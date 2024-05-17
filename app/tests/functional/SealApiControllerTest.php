<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\fixtures\SealTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SealApiControllerTest extends AbstractTestCase
{
    public function testGetSealsShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/seals');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneSealShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/seals/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testCreateSealShouldReturnCreatedSeal(): void
    {
        $requestBody = SealTestFixtures::partial();

        $response = $this->client->request('POST', '/api/v2/seals', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($requestBody),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach (array_keys($requestBody) as $key) {
            $this->assertEquals($requestBody[$key], $content[$key]);
        }
    }

    public function testDeleteSealShouldReturnSuccess(): void
    {
        $sealId = 1;

        $response = $this->client->request(Request::METHOD_DELETE, sprintf('/api/v2/seals/%s', $sealId));
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->client->request(Request::METHOD_GET, sprintf('/api/v2/seals/%s', $sealId));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
