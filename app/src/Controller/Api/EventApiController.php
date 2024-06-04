<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\EventRepository;
use App\Request\EventRequest;
use App\Service\EventService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EventApiController
{
    private EventService $eventService;
    private EventRepository $repository;
    private EventRequest $eventRequest;

    public function __construct()
    {
        $this->eventService = new EventService();

        $this->repository = new EventRepository();

        $this->eventRequest = new EventRequest();
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

    public function post(): JsonResponse
    {
        try {
            $eventData = $this->eventRequest->validatePost();

            $event = $this->eventService->create((object) $eventData);

            $responseData = [
                'id' => $event->getId(),
                'name' => $event->getName(),
                'shortDescription' => $event->getShortDescription(),
                'classificacaoEtaria' => $event->getMetadata('classificacaoEtaria'),
                'terms' => $event->getTerms(),
            ];

            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function patch(array $params): JsonResponse
    {
        try {
            $eventData = $this->eventRequest->validateUpdate();
            $event = $this->eventService->update((int) $params['id'], (object) $eventData);

            return new JsonResponse($event, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete($params): JsonResponse
    {
        try {
            $event = $this->eventRequest->validateEventExistent($params);
            $this->repository->softDelete($event);

            return new JsonResponse([], Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
