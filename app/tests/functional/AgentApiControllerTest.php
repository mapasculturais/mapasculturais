<?php

declare(strict_types=1);

namespace App\Tests;

class AgentApiControllerTest extends AbstractTestCase
{
    public function testGetAgentsShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/agents');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneAgentShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/agents/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testGetAgentTypesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/agents/types');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetAgentOpportunitiesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/agents/1/opportunities');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testCreateAgentShouldCreateAnAgent(): void
    {
        $data = [
            'name' => 'Fulano',
            'shortDescription' => 'o brabro do 085',
            'terms' => [
                'area' => ['Arqueologia'],
            ],
            'type' => 1,
        ];

        $response = $this->client->request('POST', '/api/v2/agents', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($data),
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($data['name'], $content['name']);
        $this->assertEquals($data['shortDescription'], $content['shortDescription']);
        $this->assertEquals($data['terms'], $content['terms']);
        $this->assertEquals($data['type'], $content['type']);
    }

    public function testDeleteAgentShouldReturnSuccess(): void
    {
        $agentId = 2;

        $response = $this->client->request('DELETE', '/api/v2/agents/'.$agentId);

        $this->assertEquals(204, $response->getStatusCode());

        $response = $this->client->request('GET', '/api/v2/agents/'.$agentId);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
