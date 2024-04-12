<?php

declare(strict_types=1);

namespace App\Tests;

class WelcomeApiControllerTest extends AbstractTestCase
{
    public function testHomepageFromAPI(): void
    {
        $response = $this->client->request('GET', '/api');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MapaCultural', $content->API);
    }

    public function testErrorResponseWhenMethodIsNotAllowed(): void
    {
        $response = $this->client->request('POST', '/api');

        $this->assertEquals(405, $response->getStatusCode());
    }
}
