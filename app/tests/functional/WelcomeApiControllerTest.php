<?php

declare(strict_types=1);

namespace Tests;

class WelcomeApiControllerTest extends AbstractTestCase
{
    public function testOneIsOne(): void
    {
        $response = $this->client->request('GET', '/api');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('MapaCultural', $content->API);
    }
}
