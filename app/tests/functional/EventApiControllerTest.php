<?php

declare(strict_types=1);

namespace App\Tests;

class EventApiControllerTest extends AbstractTestCase
{
    public function testGetEventTypesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/events/types');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneAgentShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/events/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testGetEventsBySpacesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/spaces/4/events');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }
}
