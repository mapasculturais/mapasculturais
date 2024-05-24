<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\EventFixtures;
use App\Tests\fixtures\EventTestFixtures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventApiControllerTest extends AbstractTestCase
{
    public function testGetEventTypesShouldRetrieveAList(): void
    {
        $response = $this->client->request('GET', '/api/v2/events/types');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($content);
    }

    public function testGetOneAgentShouldRetrieveAObject(): void
    {
        $response = $this->client->request('GET', '/api/v2/events/1');
        $content = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsObject($content);
    }

//    public function testGetEventsBySpacesShouldRetrieveAList(): void
//    {
//        $response = $this->client->request('GET', '/api/v2/spaces/4/events');
//        $content = json_decode($response->getContent());
//
//        $this->assertEquals(200, $response->getStatusCode());
//        $this->assertIsArray($content);
//    }

//    public function testPostEventShouldCreateANewEvent(): void
//    {
//        $response = $this->client->request('POST', '/api/v2/events', [
//            'json' => [
//                'name' => 'Event Test',
//                'shortDescription' => 'Event Test Description',
//                'classificacaoEtaria' => 'livre',
//                'terms' => [
//                    'linguagem' => 'Artes Circenses',
//                ],
//            ],
//        ]);
//        $content = json_decode($response->getContent());
//
//        $this->assertEquals(201, $response->getStatusCode());
//        $this->assertIsObject($content);
//    }

//    public function testDeleteEventShouldRemoveAnEvent(): void
//    {
//        $response = $this->client->request('DELETE', '/api/v2/events/1');
//        $content = json_decode($response->getContent());
//
//        $this->assertEquals(200, $response->getStatusCode());
//        $this->assertIsArray($content);
//    }

//    public function testUpdateEventShouldUpdateAnEvent(): void
//    {
//        $requestBody = EventTestFixtures::partial();
//        $url = sprintf('/api/v2/events/%s', EventFixtures::EVENT_ID_2);
//
//        $response = $this->client->request(Request::METHOD_PATCH, $url, [
//            'headers' => ['Content-Type' => 'application/json'],
//            'body' => json_encode($requestBody),
//        ]);
//
//        $content = json_decode($response->getContent(), true);
//        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
//        $this->assertIsArray($content);
//        foreach (array_keys($requestBody) as $key) {
//            $this->assertEquals($requestBody[$key], $content[$key]);
//        }
//    }

    public function testUpdateNotFoundedEventResource(): void
    {
        $requestData = json_encode(EventTestFixtures::partial());
        $url = sprintf('/api/v2/events/%s', 1024);

        $response = $this->client->request(Request::METHOD_PATCH, $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $requestData,
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
