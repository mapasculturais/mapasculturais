<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\ResourceNotFoundException;
use App\Exception\ValidatorException;
use App\Repository\SealRepository;
use App\Request\SealRequest;
use App\Service\SealService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SealApiController
{
    private SealRepository $repository;
    private SealService $sealService;
    private SealRequest $sealRequest;

    public function __construct()
    {
        $this->repository = new SealRepository();
        $this->sealService = new SealService();
        $this->sealRequest = new SealRequest();
    }

    public function getList(): JsonResponse
    {
        $seals = $this->repository->findAll();

        return new JsonResponse($seals);
    }

    public function getOne(array $params): JsonResponse
    {
        $seal = $this->repository->find((int) $params['id']);

        return new JsonResponse($seal);
    }

    public function post(): JsonResponse
    {
        try {
            $sealData = $this->sealRequest->validatePost();
            $seal = $this->sealService->create($sealData);

            return new JsonResponse($seal, Response::HTTP_CREATED);
        } catch (ValidatorException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'fields' => $exception->getFields(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function patch(array $params): JsonResponse
    {
        try {
            $sealData = $this->sealRequest->validatePatch();
            $seal = $this->sealService->update((int) $params['id'], (object) $sealData);

            return new JsonResponse($seal, Response::HTTP_CREATED);
        } catch (ResourceNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ValidatorException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'fields' => $exception->getFields(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(array $params): JsonResponse
    {
        try {
            $this->sealService->delete((int) $params['id']);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (ResourceNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
