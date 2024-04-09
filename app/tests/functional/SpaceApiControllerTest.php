<?php

declare(strict_types=1);

namespace App\Tests;

class SpaceApiControllerTest extends AbstractTestCase
{
    public function testGetSpacesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/spaces');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneSealShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/spaces/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }
}
