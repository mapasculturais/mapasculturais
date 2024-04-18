<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\EventRepository;
use App\Service\EventService;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventApiController
{
    private EventService $eventService;

    private EventRepository $repository;

    public function __construct()
    {
        $this->eventService = new EventService();

        $this->repository = new EventRepository();
    }

    public function getList(): JsonResponse
    {
        $events = $this->repository->findAll();

        return new JsonResponse($events);
    }

    public function getOne(array $params): JsonResponse
    {
        $event = $this->repository->find((int) $params['id']);

        return new JsonResponse($event);
    }

    public function getTypes(): JsonResponse
    {
        $types = $this->eventService->getTypes();

        return new JsonResponse($types);
    }

    public function getEventsBySpace(array $params): JsonResponse
    {
        $events = $this->repository->findEventsBySpaceId((int) $params['id']);

        return new JsonResponse($events);
    }
}
