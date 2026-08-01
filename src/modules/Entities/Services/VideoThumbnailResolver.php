<?php

namespace Entities\Services;

use Closure;
use InvalidArgumentException;
use MapasCulturais\App;
use MapasCulturais\Cache;

class VideoThumbnailResolver
{
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

            $url = $this->resolveRedirects($url, $provider);
            [$parts, $host] = $this->parseHttpsUrl($url);

            if ($this->providerForHost($host) !== $provider) {
                throw new InvalidArgumentException('Provider changed after redirect');
            }

            $canonical = $this->canonicalize($provider, $parts);
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
        if ($this->hostMatches($host, 'tiktok.com')) {
            return 'tiktok';
        }

        if ($this->hostMatches($host, 'instagram.com') || $host === 'instagr.am') {
            return 'instagram';
        }

        return null;
    }

    private function hostMatches(string $host, string $domain): bool
    {
        return $host === $domain || str_ends_with($host, ".{$domain}");
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
            return $normalized['provider'] === 'tiktok'
                ? $this->resolveTikTok($normalized['url'])
                : $this->resolveInstagram($normalized['url']);
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            return null;
        }
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
        throw new \RuntimeException('HTTP transport not implemented');
    }
}
