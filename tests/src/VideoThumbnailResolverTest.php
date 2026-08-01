<?php

namespace Tests;

use Entities\Services\VideoThumbnailResolver;
use InvalidArgumentException;
use Tests\Abstract\TestCase;

class VideoThumbnailResolverTest extends TestCase
{
    private array $requests = [];

    private function resolver(array $responses = []): VideoThumbnailResolver
    {
        $transport = function (string $url, bool $headersOnly = false) use (&$responses): array {
            $key = ($headersOnly ? 'HEAD ' : 'GET ') . $url;
            $this->requests[] = $key;

            if (!isset($responses[$key]) || !$responses[$key]) {
                throw new \RuntimeException("Unexpected HTTP request: {$key}");
            }

            return array_shift($responses[$key]);
        };

        return new VideoThumbnailResolver($transport, $this->app->cache);
    }

    private function response(int $status, string $body = '', ?string $location = null): array
    {
        return [
            'status' => $status,
            'headers' => $location ? ['location' => $location] : [],
            'body' => $body,
        ];
    }

    public function testNormalizesCanonicalInstagramUrls(): void
    {
        $resolver = $this->resolver();

        $this->assertSame(
            ['provider' => 'instagram', 'url' => 'https://www.instagram.com/reel/AbC_123-/'],
            $resolver->normalize('https://www.instagram.com/reel/AbC_123-/?igsh=tracking')
        );
        $this->assertSame(
            ['provider' => 'instagram', 'url' => 'https://www.instagram.com/p/Post123/'],
            $resolver->normalize('https://instagram.com/p/Post123/')
        );
        $this->assertSame(
            ['provider' => 'instagram', 'url' => 'https://www.instagram.com/tv/Tv123/'],
            $resolver->normalize('https://www.instagram.com/tv/Tv123/')
        );
    }

    public function testNormalizesCanonicalTikTokUrl(): void
    {
        $resolver = $this->resolver();

        $this->assertSame(
            [
                'provider' => 'tiktok',
                'url' => 'https://www.tiktok.com/@creator.name/video/7123456789012345678',
            ],
            $resolver->normalize('https://www.tiktok.com/@creator.name/video/7123456789012345678?is_from_webapp=1')
        );
    }

    public function testResolvesTikTokOembedThumbnail(): void
    {
        $canonical = 'https://www.tiktok.com/@creator/video/7123456789012345678';
        $oembed = 'https://www.tiktok.com/oembed?url=' . rawurlencode($canonical);
        $resolver = $this->resolver([
            "GET {$oembed}" => [
                $this->response(200, json_encode([
                    'thumbnail_url' => 'https://p16-sign.tiktokcdn.com/cover.jpeg',
                ])),
            ],
        ]);

        $this->assertSame(
            'https://p16-sign.tiktokcdn.com/cover.jpeg',
            $resolver->resolve($canonical)
        );
    }

    public function testResolvesTikTokShortLinkWithinAllowlist(): void
    {
        $short = 'https://vm.tiktok.com/ZMshort/';
        $canonical = 'https://www.tiktok.com/@creator/video/7123456789012345678';
        $oembed = 'https://www.tiktok.com/oembed?url=' . rawurlencode($canonical);
        $resolver = $this->resolver([
            "HEAD {$short}" => [$this->response(302, location: $canonical)],
            "HEAD {$canonical}" => [$this->response(200)],
            "GET {$oembed}" => [
                $this->response(200, '{"thumbnail_url":"https://p16.tiktokcdn.com/cover.jpg"}'),
            ],
        ]);

        $this->assertSame('https://p16.tiktokcdn.com/cover.jpg', $resolver->resolve($short));
    }

    public function testResolvesInstagramShareLinkWithinAllowlist(): void
    {
        $short = 'https://www.instagram.com/share/reel/Share123/';
        $canonical = 'https://www.instagram.com/reel/Reel123/';
        $embed = 'https://www.instagram.com/reel/Reel123/embed/captioned/';
        $resolver = $this->resolver([
            "HEAD {$short}" => [$this->response(302, location: $canonical)],
            "HEAD {$canonical}" => [$this->response(200)],
            "GET {$embed}" => [
                $this->response(200, '<img class="EmbeddedMediaImage" src="https://scontent.cdninstagram.com/reel.jpg">'),
            ],
        ]);

        $this->assertSame(
            'https://scontent.cdninstagram.com/reel.jpg',
            $resolver->resolve($short)
        );
    }

    public function testExtractsInstagramJsonThumbnail(): void
    {
        $url = 'https://www.instagram.com/reel/Reel123/';
        $embed = 'https://www.instagram.com/reel/Reel123/embed/captioned/';
        $html = '<script>window.__data={"thumbnail_src":"https:\/\/scontent.cdninstagram.com\/cover.jpg?x=1\\u0026y=2"};</script>';
        $resolver = $this->resolver([
            "GET {$embed}" => [$this->response(200, $html)],
        ]);

        $this->assertSame(
            'https://scontent.cdninstagram.com/cover.jpg?x=1&y=2',
            $resolver->resolve($url)
        );
    }

    public function testFallsBackToInstagramImageElement(): void
    {
        $url = 'https://www.instagram.com/p/Post123/';
        $embed = 'https://www.instagram.com/p/Post123/embed/captioned/';
        $html = '<img alt="post" src="https://scontent.cdninstagram.com/post.jpg?a=1&amp;b=2" class="EmbeddedMediaImage other">';
        $resolver = $this->resolver([
            "GET {$embed}" => [$this->response(200, $html)],
        ]);

        $this->assertSame(
            'https://scontent.cdninstagram.com/post.jpg?a=1&b=2',
            $resolver->resolve($url)
        );
    }

    public function testRejectsRedirectOutsideProviderAllowlist(): void
    {
        $resolver = $this->resolver([
            'HEAD https://vm.tiktok.com/ZMshort/' => [
                $this->response(302, location: 'https://example.org/video/123'),
            ],
        ]);

        $this->assertNull($resolver->resolve('https://vm.tiktok.com/ZMshort/'));
        $this->assertSame(['HEAD https://vm.tiktok.com/ZMshort/'], $this->requests);
    }

    public function testRejectsMoreThanThreeRedirects(): void
    {
        $resolver = $this->resolver([
            'HEAD https://vm.tiktok.com/one/' => [
                $this->response(302, location: 'https://vm.tiktok.com/two/'),
            ],
            'HEAD https://vm.tiktok.com/two/' => [
                $this->response(302, location: 'https://vm.tiktok.com/three/'),
            ],
            'HEAD https://vm.tiktok.com/three/' => [
                $this->response(302, location: 'https://vm.tiktok.com/four/'),
            ],
            'HEAD https://vm.tiktok.com/four/' => [
                $this->response(302, location: 'https://vm.tiktok.com/five/'),
            ],
        ]);

        $this->assertNull($resolver->resolve('https://vm.tiktok.com/one/'));
        $this->assertCount(4, $this->requests);
    }

    public function testReturnsNullForMissingOrMalformedProviderData(): void
    {
        $tiktok = 'https://www.tiktok.com/@creator/video/7123456789012345678';
        $oembed = 'https://www.tiktok.com/oembed?url=' . rawurlencode($tiktok);
        $resolver = $this->resolver([
            "GET {$oembed}" => [$this->response(200, '{"title":"without thumbnail"}')],
        ]);

        $this->assertNull($resolver->resolve($tiktok));
    }

    /** @dataProvider invalidUrlProvider */
    public function testRejectsInvalidOrUnsupportedUrls(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolver()->normalize($url);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            'http' => ['http://www.instagram.com/reel/Code/'],
            'credentials' => ['https://user:pass@www.instagram.com/reel/Code/'],
            'custom port' => ['https://www.instagram.com:8443/reel/Code/'],
            'deceptive Instagram host' => ['https://instagram.com.example.org/reel/Code/'],
            'deceptive TikTok host' => ['https://tiktok.com.example.org/@user/video/123'],
            'Instagram profile' => ['https://www.instagram.com/mapasculturais/'],
            'TikTok profile' => ['https://www.tiktok.com/@mapasculturais'],
            'unsupported provider' => ['https://example.org/video/123'],
            'malformed' => ['not a URL'],
        ];
    }
}
