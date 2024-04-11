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

    public function getTypes(): JsonResponse
    {
        $types = $this->eventService->getTypes();

        return new JsonResponse($types);
    }
}
