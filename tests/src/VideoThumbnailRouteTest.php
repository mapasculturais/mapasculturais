<?php

namespace Tests;

use MapasCulturais\Cache;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Abstract\TestCase;
use Tests\Traits\RequestFactory;

class VideoThumbnailRouteTest extends TestCase
{
    use RequestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->cache = new Cache(new ArrayAdapter());
    }

    private function runRequest(string $url): array
    {
        $request = $this->requestFactory->GET(
            'site',
            'videoThumbnail',
            query_params: ['url' => $url]
        );

        $this->app->run($request, false);

        return [
            'status' => $this->app->response->getStatusCode(),
            'json' => json_decode((string) $this->app->response->getBody(), true),
        ];
    }

    public function testReturnsCachedThumbnailUrl(): void
    {
        $url = 'https://www.tiktok.com/@creator/video/7123456789012345678';
        $key = 'video-thumbnail:tiktok:' . hash('sha256', $url);
        $this->app->cache->save($key, 'https://cdn.tiktok.com/cover.jpg', 3600);

        $result = $this->runRequest($url);

        $this->assertSame(200, $result['status']);
        $this->assertSame(
            ['thumbnailUrl' => 'https://cdn.tiktok.com/cover.jpg'],
            $result['json']
        );
    }

    public function testReturnsNullForCachedNegativeResult(): void
    {
        $url = 'https://www.instagram.com/reel/Missing123/';
        $key = 'video-thumbnail:instagram:' . hash('sha256', $url);
        $this->app->cache->save($key, null, 300);

        $result = $this->runRequest($url);

        $this->assertSame(200, $result['status']);
        $this->assertSame(['thumbnailUrl' => null], $result['json']);
    }

    public function testRejectsInvalidInput(): void
    {
        $result = $this->runRequest('https://instagram.com.example.org/reel/Code/');

        $this->assertSame(400, $result['status']);
        $this->assertSame(['thumbnailUrl' => null], $result['json']);
    }

    public function testRejectsEmptyInput(): void
    {
        $result = $this->runRequest('');

        $this->assertSame(400, $result['status']);
        $this->assertSame(['thumbnailUrl' => null], $result['json']);
    }
}
