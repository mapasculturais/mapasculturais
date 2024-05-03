<?php

declare(strict_types=1);

namespace App\Tests;

class OpportunityApiControllerTest extends AbstractTestCase
{
    public function testGetOpportunitiesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/opportunities');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneOpportunityShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/opportunities/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }
}
