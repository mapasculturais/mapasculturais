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
}
