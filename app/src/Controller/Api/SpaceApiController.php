<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Enum\EntityStatusEnum;
use App\Exception\ResourceNotFoundException;
use App\Repository\SpaceRepository;
use App\Request\SpaceRequest;
use App\Service\SpaceService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SpaceApiController
{
    private SpaceRepository $repository;
    private SpaceRequest $spaceRequest;
    private SpaceService $spaceService;

    public function __construct()
    {
        $this->repository = new SpaceRepository();
        $this->spaceRequest = new SpaceRequest();
        $this->spaceService = new SpaceService();
    }

    public function getList(): JsonResponse
    {
        $spaces = $this->repository->findAll();

        return new JsonResponse($spaces);
    }

    public function getOne(array $params): JsonResponse
    {
        $space = $this->repository->find((int) $params['id']);

        return new JsonResponse($space);
    }

    public function post(): JsonResponse
    {
        try {
            $spaceData = $this->spaceRequest->validatePost();
            $space = $this->spaceService->create((object) $spaceData);

            $responseData = [
                'id' => $space->getId(),
                'name' => $space->getName(),
                'shortDescription' => $space->getShortDescription(),
                'terms' => $space->getTerms(),
                'type' => $space->getType(),
            ];

            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function patch(array $params): JsonResponse
    {
        try {
            $spaceData = $this->spaceRequest->validateUpdate();
            $space = $this->spaceService->update((int) $params['id'], (object) $spaceData);

            return new JsonResponse($space, Response::HTTP_CREATED);
        } catch (ResourceNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(array $params): JsonResponse
    {
        $space = $this->repository->find((int) $params['id']);

        if (EntityStatusEnum::TRASH->getValue() === $space->status) {
            return new JsonResponse(['error' => 'Espaço não encontrado'], Response::HTTP_NOT_FOUND);
        }

        $this->repository->softDelete($space);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
