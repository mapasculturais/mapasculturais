<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\ProjectFixtures;
use App\Tests\fixtures\ProjectTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectApiControllerTest extends AbstractTestCase
{
    public function testGetProjectsShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/projects');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneProjectShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/projects/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testCreateProjectShouldCreateAProject(): void
    {
        $data = [
            'name' => 'PHP com Rapadura',
            'shortDescription' => 'php com rapadura',
            'type' => 1,
        ];

        $response = $this->client->request('POST', '/api/v2/projects', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($data),
        ]);

        $this->assertEquals(201, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($data['name'], $content['name']);
        $this->assertEquals($data['shortDescription'], $content['shortDescription']);
        $this->assertEquals($data['type'], $content['type']);
    }

    public function testDeleteProjectShouldReturnSuccess(): void
    {
        $projectId = 1;

        $response = $this->client->request('DELETE', '/api/v2/projects/'.$projectId);

        $this->assertEquals(204, $response->getStatusCode());

        $response = $this->client->request('GET', '/api/v2/projects/'.$projectId);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateProjectShouldUpdateAProject(): void
    {
        $requestBody = ProjectTestFixtures::partial();
        $url = sprintf('/api/v2/projects/%s', ProjectFixtures::PROJECT_ID_2);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($requestBody),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach ($requestBody as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateNotFoundedProjectResource(): void
    {
        $requestData = json_encode(ProjectTestFixtures::partial());
        $url = sprintf('/api/v2/projects/%s', 1024);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $requestData,
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
