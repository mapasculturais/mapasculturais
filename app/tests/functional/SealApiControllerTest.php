<?php

declare(strict_types=1);

namespace App\Tests;

class SealApiControllerTest extends AbstractTestCase
{
    public function testGetSealsShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/seals');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }
}
