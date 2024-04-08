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
}
