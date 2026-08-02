<?php

namespace Entities\Services;

use Closure;
use InvalidArgumentException;
use MapasCulturais\App;
use MapasCulturais\Cache;

class VideoThumbnailResolver
{
    public const POSITIVE_CACHE_TTL = 3600;
    public const NEGATIVE_CACHE_TTL = 300;

    private const CONNECT_TIMEOUT_MS = 3000;
    private const TOTAL_TIMEOUT_MS = 5000;
    private const MAX_RESPONSE_BYTES = 2097152;
    private const PROVIDER_HOSTS = [
        'tiktok' => [
            'tiktok.com',
            'www.tiktok.com',
            'm.tiktok.com',
            'vm.tiktok.com',
            'vt.tiktok.com',
        ],
        'instagram' => [
            'instagram.com',
            'www.instagram.com',
            'm.instagram.com',
            'instagr.am',
        ],
    ];

    private Closure $httpRequest;
    private Cache $cache;

    public function __construct(?Closure $httpRequest = null, ?Cache $cache = null)
    {
        $this->httpRequest = $httpRequest ?? fn(string $url, bool $headersOnly = false): array =>
            $this->request($url, $headersOnly);
        $this->cache = $cache ?? App::i()->cache;
    }

    public function normalize(string $url): array
    {
        $url = trim($url);
        [$parts, $host] = $this->parseHttpsUrl($url);
        $provider = $this->providerForHost($host);

        if (!$provider) {
            throw new InvalidArgumentException('Unsupported video provider');
        }

        $canonical = $this->canonicalize($provider, $parts);
        if (!$canonical) {
            if (!$this->shouldResolveRedirect($provider, $host, $parts['path'] ?? '/')) {
                throw new InvalidArgumentException('Unsupported video URL');
            }

            $redirectCacheKey = $this->redirectCacheKey($provider, $url);
            if ($this->cache->contains($redirectCacheKey)) {
                $cached = $this->cache->fetch($redirectCacheKey);
                if (is_string($cached)) {
                    return ['provider' => $provider, 'url' => $cached];
                }

                throw new \RuntimeException('Cached short video URL failure');
            }

            try {
                $url = $this->resolveRedirects($url, $provider);
                [$parts, $host] = $this->parseHttpsUrl($url);

                if ($this->providerForHost($host) !== $provider) {
                    throw new \RuntimeException('Provider changed after redirect');
                }

                $canonical = $this->canonicalize($provider, $parts);
                if (!$canonical) {
                    throw new \RuntimeException('Unsupported redirected video URL');
                }
            } catch (\Throwable $exception) {
                $this->cache->save($redirectCacheKey, null, self::NEGATIVE_CACHE_TTL);
                throw new \RuntimeException('Unable to resolve short video URL', 0, $exception);
            }

            $this->cache->save($redirectCacheKey, $canonical, self::POSITIVE_CACHE_TTL);
        }

        if (!$canonical) {
            throw new InvalidArgumentException('Unsupported video URL');
        }

        return ['provider' => $provider, 'url' => $canonical];
    }

    private function shouldResolveRedirect(string $provider, string $host, string $path): bool
    {
        if ($provider === 'tiktok') {
            return in_array($host, ['vm.tiktok.com', 'vt.tiktok.com', 'm.tiktok.com'], true);
        }

        return $host === 'instagr.am' || str_starts_with($path, '/share/');
    }

    private function parseHttpsUrl(string $url): array
    {
        $parts = parse_url($url);
        if (
            !is_array($parts) ||
            strtolower((string) ($parts['scheme'] ?? '')) !== 'https' ||
            empty($parts['host']) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            throw new InvalidArgumentException('Invalid video URL');
        }

        $host = strtolower(rtrim($parts['host'], '.'));
        return [$parts, $host];
    }

    private function providerForHost(string $host): ?string
    {
        foreach (self::PROVIDER_HOSTS as $provider => $hosts) {
            if (in_array($host, $hosts, true)) {
                return $provider;
            }
        }

        return null;
    }

    private function canonicalize(string $provider, array $parts): ?string
    {
        $path = $parts['path'] ?? '/';

        if ($provider === 'instagram') {
            if (!preg_match('#^/(p|reel|tv)/([A-Za-z0-9_-]+)(?:/|$)#', $path, $matches)) {
                return null;
            }

            return "https://www.instagram.com/{$matches[1]}/{$matches[2]}/";
        }

        if (!preg_match('#^/@([A-Za-z0-9._-]+)/video/(\d+)(?:/|$)#', $path, $matches)) {
            return null;
        }

        return "https://www.tiktok.com/@{$matches[1]}/video/{$matches[2]}";
    }

    private function resolveRedirects(string $url, string $provider): string
    {
        $current = $url;

        for ($redirects = 0; $redirects <= 3; $redirects++) {
            $response = ($this->httpRequest)($current, true);
            $status = (int) ($response['status'] ?? 0);

            if ($status === 405) {
                $response = ($this->httpRequest)($current, false);
                $status = (int) ($response['status'] ?? 0);
            }

            if ($status >= 200 && $status < 300) {
                return $current;
            }

            if ($status < 300 || $status >= 400 || empty($response['headers']['location'])) {
                throw new \RuntimeException('Unable to resolve short video URL');
            }

            if ($redirects === 3) {
                throw new \RuntimeException('Too many redirects');
            }

            $next = $this->absoluteRedirectUrl($current, $response['headers']['location']);
            [, $host] = $this->parseHttpsUrl($next);
            if ($this->providerForHost($host) !== $provider) {
                throw new \RuntimeException('Redirect outside provider allowlist');
            }

            $current = $next;
        }

        throw new \RuntimeException('Unable to resolve short video URL');
    }

    private function absoluteRedirectUrl(string $current, string $location): string
    {
        if (str_starts_with($location, 'https://')) {
            return $location;
        }

        $parts = parse_url($current);
        if (str_starts_with($location, '//')) {
            return 'https:' . $location;
        }

        if (str_starts_with($location, '/')) {
            return 'https://' . $parts['host'] . $location;
        }

        $basePath = rtrim(dirname($parts['path'] ?? '/'), '/');
        return 'https://' . $parts['host'] . $basePath . '/' . $location;
    }

    public function resolve(string $url): ?string
    {
        try {
            $normalized = $this->normalize($url);
            $cacheKey = $this->cacheKey($normalized['provider'], $normalized['url']);

            if ($this->cache->contains($cacheKey)) {
                $cached = $this->cache->fetch($cacheKey);
                return is_string($cached) ? $cached : null;
            }

            try {
                $thumbnail = $normalized['provider'] === 'tiktok'
                    ? $this->resolveTikTok($normalized['url'])
                    : $this->resolveInstagram($normalized['url']);
            } catch (\Throwable $exception) {
                $this->cache->save($cacheKey, null, self::NEGATIVE_CACHE_TTL);
                return null;
            }

            $this->cache->save(
                $cacheKey,
                $thumbnail,
                $thumbnail ? self::POSITIVE_CACHE_TTL : self::NEGATIVE_CACHE_TTL
            );

            return $thumbnail;
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function cacheKey(string $provider, string $url): string
    {
        return "video-thumbnail:{$provider}:" . hash('sha256', $url);
    }

    private function redirectCacheKey(string $provider, string $url): string
    {
        return "video-thumbnail:redirect:{$provider}:" . hash('sha256', $url);
    }

    private function resolveTikTok(string $url): ?string
    {
        $endpoint = 'https://www.tiktok.com/oembed?url=' . rawurlencode($url);
        $response = ($this->httpRequest)($endpoint, false);

        if (($response['status'] ?? 0) < 200 || ($response['status'] ?? 0) >= 300) {
            return null;
        }

        $data = json_decode((string) ($response['body'] ?? ''), true);
        return $this->validateThumbnailUrl($data['thumbnail_url'] ?? null);
    }

    private function resolveInstagram(string $url): ?string
    {
        $embedUrl = rtrim($url, '/') . '/embed/captioned/';
        $response = ($this->httpRequest)($embedUrl, false);

        if (($response['status'] ?? 0) < 200 || ($response['status'] ?? 0) >= 300) {
            return null;
        }

        return $this->extractInstagramThumbnail((string) ($response['body'] ?? ''));
    }

    private function extractInstagramThumbnail(string $html): ?string
    {
        if (preg_match('/"thumbnail_src"\s*:\s*("(?:\\\\.|[^"\\\\])*")/u', $html, $matches)) {
            $thumbnail = json_decode($matches[1], true);
            if (is_string($thumbnail) && $validated = $this->validateThumbnailUrl($thumbnail)) {
                return $validated;
            }
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return null;
        }

        foreach ($document->getElementsByTagName('img') as $image) {
            $classes = preg_split('/\s+/', trim($image->getAttribute('class')));
            if (in_array('EmbeddedMediaImage', $classes, true)) {
                return $this->validateThumbnailUrl(
                    html_entity_decode($image->getAttribute('src'), ENT_QUOTES | ENT_HTML5, 'UTF-8')
                );
            }
        }

        return null;
    }

    private function validateThumbnailUrl(mixed $url): ?string
    {
        if (!is_string($url) || $url === '') {
            return null;
        }

        try {
            $this->parseHttpsUrl($url);
            return $url;
        } catch (InvalidArgumentException $exception) {
            return null;
        }
    }

    private function request(string $url, bool $headersOnly): array
    {
        $headers = [];
        $body = '';
        $tooLarge = false;
        $handle = curl_init($url);

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => self::CONNECT_TIMEOUT_MS,
            CURLOPT_TIMEOUT_MS => self::TOTAL_TIMEOUT_MS,
            CURLOPT_USERAGENT => 'MapasCulturais/VideoThumbnailResolver',
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER => ['Accept: application/json, text/html;q=0.9, */*;q=0.1'],
            CURLOPT_HEADERFUNCTION => function ($handle, string $line) use (&$headers): int {
                $length = strlen($line);
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[strtolower(trim($name))] = trim($value);
                }
                return $length;
            },
        ]);

        if ($headersOnly) {
            curl_setopt($handle, CURLOPT_NOBODY, true);
        } else {
            curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($handle, string $chunk) use (&$body, &$tooLarge): int {
                if (strlen($body) + strlen($chunk) > self::MAX_RESPONSE_BYTES) {
                    $tooLarge = true;
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            });
        }

        $result = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($tooLarge) {
            throw new \RuntimeException('Provider response exceeded size limit');
        }

        if ($result === false) {
            throw new \RuntimeException($error ?: 'Provider request failed');
        }

        return ['status' => $status, 'headers' => $headers, 'body' => $body];
    }
}
