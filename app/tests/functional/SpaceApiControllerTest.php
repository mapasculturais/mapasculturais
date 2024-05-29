<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\SpaceFixtures;
use App\Tests\fixtures\SpaceTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpaceApiControllerTest extends AbstractTestCase
{
    private const BASE_URL = '/api/v2/spaces';

    public function testGetSpacesShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL);
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneSpaceShouldRetrieveAObject(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testCreateSpaceShouldReturnCreatedSpace(): void
    {
        $this->markTestSkipped();
        $requestData = json_encode([
            'name' => 'Novo Espaço',
            'location' => [
                'latitude' => '45.4215',
                'longitude' => '-75.6981',
            ],
            'public' => true,
            'shortDescription' => 'Uma breve descrição do espaço.',
            'longDescription' => 'Descrição longa e detalhada do espaço.',
        ]);

        $response = $this->client->request(Request::METHOD_POST, self::BASE_URL, [
            'body' => $requestData,
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals('Novo Espaço', $content['name']);
    }

    public function testDeleteSpaceShouldReturnSuccess(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_DELETE, self::BASE_URL.'/1');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdate(): void
    {
        $this->markTestSkipped();
        $requestBody = SpaceTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', SpaceFixtures::SPACE_ID_3);

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
        $requestData = json_encode(SpaceTestFixtures::partial());
        $url = sprintf(self::BASE_URL.'/%s', 1024);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $requestData,
        ]);

        $error = [
            'error' => 'Space not found',
        ];

        $content = json_decode($response->getContent(false), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals($error, $content);
    }
}
