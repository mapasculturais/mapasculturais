<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\SealFixtures;
use App\Tests\fixtures\SealTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SealApiControllerTest extends AbstractTestCase
{
    private const BASE_URL = '/api/v2/seals';

    public function testGetSealsShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL);
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneSealShouldRetrieveAObject(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testCreateSealShouldReturnCreatedSeal(): void
    {
        $this->markTestSkipped();
        $sealTestFixtures = SealTestFixtures::partial();

        $response = $this->client->request(Request::METHOD_POST, self::BASE_URL, [
            'body' => $sealTestFixtures->json(),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach ($sealTestFixtures->toArray() as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testDeleteSealShouldReturnSuccess(): void
    {
        $this->markTestSkipped();
        $sealId = SealFixtures::SEAL_ID_6;

        $response = $this->client->request(Request::METHOD_DELETE, sprintf(self::BASE_URL.'/%s', $sealId));
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->client->request(Request::METHOD_GET, sprintf(self::BASE_URL.'/%s', $sealId));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdate(): void
    {
        $this->markTestSkipped();
        $sealTestFixtures = SealTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', SealFixtures::SEAL_ID_3);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $sealTestFixtures->json(),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach ($sealTestFixtures->toArray() as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateNotFoundedResource(): void
    {
        $sealTestFixtures = SealTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', 80);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $sealTestFixtures->json(),
        ]);

        $error = [
            'error' => 'Seal not found',
        ];

        $content = json_decode($response->getContent(false), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals($error, $content);
    }
}
