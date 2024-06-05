<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\EventFixtures;
use App\Tests\fixtures\EventTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventApiControllerTest extends AbstractTestCase
{
    private const BASE_URL = '/api/v2/events';

    public function testGetEventTypesShouldRetrieveAList(): void
    {
        $response = $this->client->request(Request::METHOD_GET, self::BASE_URL.'/types');
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

    public function testGetEventsBySpacesShouldRetrieveAList(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_GET, '/api/v2/spaces/4/events');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testPostEventShouldCreateANewEvent(): void
    {
        $this->markTestSkipped();
        $eventTestFixtures = EventTestFixtures::partial();

        $response = $this->client->request(Request::METHOD_POST, self::BASE_URL, [
            'body' => $eventTestFixtures->json(),
        ]);
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsObject($content);
    }

    public function testDeleteEventShouldRemoveAnEvent(): void
    {
        $this->markTestSkipped();
        $response = $this->client->request(Request::METHOD_DELETE, self::BASE_URL.'/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testUpdateEventShouldUpdateAnEvent(): void
    {
        $eventTestFixtures = EventTestFixtures::partial();

        $url = sprintf(self::BASE_URL.'/%s', EventFixtures::EVENT_ID_2);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $eventTestFixtures->json(),
        ]);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertIsArray($content);
        foreach ($eventTestFixtures->toArray() as $key => $value) {
            $this->assertEquals($value, $content[$key]);
        }
    }

    public function testUpdateNotFoundedEventResource(): void
    {
        $eventTestFixtures = EventTestFixtures::partial();
        $url = sprintf(self::BASE_URL.'/%s', 1024);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'body' => $eventTestFixtures->json(),
        ]);

        $error = [
            'error' => 'Event not found or already deleted.',
        ];

        $content = json_decode($response->getContent(false), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEquals($error, $content);
    }
}
