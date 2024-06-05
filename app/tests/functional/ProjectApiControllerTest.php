<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\ProjectFixtures;
use App\Tests\fixtures\ProjectTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectApiControllerTest extends AbstractTestCase
{
    private const BASE_URL = '/api/v2/projects';

    public function testGetProjectsShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL);
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneProjectShouldRetrieveAObject(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testCreateProjectShouldCreateAProject(): void
    {
        $projectTestFixtures = ProjectTestFixtures::partial();

        $response = $this->client->request(Request::METHOD_POST, self::BASE_URL, [
            'body' => $projectTestFixtures->json(),
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        foreach ($projectTestFixtures->toArray() as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testDeleteProjectShouldReturnSuccess(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_DELETE, self::BASE_URL.'/1');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/1');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateProjectShouldUpdateAProject(): void
    {
        $projectTestFixtures = ProjectTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', ProjectFixtures::PROJECT_ID_2);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $projectTestFixtures->json(),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach ($projectTestFixtures->toArray() as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateNotFoundedProjectResource(): void
    {
        $projectTestFixtures = ProjectTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', 1024);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $projectTestFixtures->json(),
        ]);

        $error = [
            'error' => 'Project not found or already deleted.',
        ];

        $content = json_decode($response->getContent(false), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals($error, $content);
    }
}
