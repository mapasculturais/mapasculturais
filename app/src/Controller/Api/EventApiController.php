<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\EventService;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventApiController
{
    private EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

    public function getList(): JsonResponse
    {
        $types = $this->eventService->getAll();

        return new JsonResponse($types);
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
}
