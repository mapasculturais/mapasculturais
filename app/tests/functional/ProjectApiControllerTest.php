<?php

declare(strict_types=1);

namespace App\Tests;

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

    public function testDeleteProjectShouldReturnSuccess(): void
    {
        $projectId = 1;

        $response = $this->client->request('DELETE', '/api/v2/projects/'.$projectId);

        $this->assertEquals(204, $response->getStatusCode());

        $response = $this->client->request('GET', '/api/v2/projects/'.$projectId);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
