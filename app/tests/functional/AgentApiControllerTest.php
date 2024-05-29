<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\AgentFixtures;
use App\Tests\fixtures\AgentTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentApiControllerTest extends AbstractTestCase
{
    private const BASE_URL = '/api/v2/agents';

    public function testGetAgentsShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL);
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneAgentShouldRetrieveAObject(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testGetAgentTypesShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/types');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetAgentOpportunitiesShouldRetrieveAList(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1/opportunities');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testCreateAgentShouldCreateAnAgent(): void
    {
        $this->markTestSkipped();
        $data = [
            'name' => 'Fulano',
            'shortDescription' => 'o brabro do 085',
            'terms' => [
                'area' => ['Arqueologia'],
            ],
            'type' => 1,
        ];

        $response = $this->client->request(Request::METHOD_POST, self::BASE_URL, [
            'body' => json_encode($data),
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($data['name'], $content['name']);
        $this->assertEquals($data['shortDescription'], $content['shortDescription']);
        $this->assertEquals($data['terms'], $content['terms']);
        $this->assertEquals($data['type'], $content['type']);
    }

    public function testDeleteAgentShouldReturnSuccess(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_DELETE, self::BASE_URL.'/2');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/2');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdate(): void
    {
        $this->markTestSkipped();
        $requestBody = AgentTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', AgentFixtures::AGENT_ID_4);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => json_encode($requestBody),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach (array_keys($requestBody) as $key) {
            $this->assertEquals($requestBody[$key], $content[$key]);
        }
    }

    public function testUpdateNotFoundedResource(): void
    {
        $requestData = json_encode(AgentTestFixtures::partial());
        $url = sprintf(self::BASE_URL.'/%s', 1969);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $requestData,
        ]);

        $error = [
            'error' => 'Agent not found',
        ];

        $content = json_decode($response->getContent(false), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals($error, $content);
    }
}
