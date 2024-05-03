<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\SealRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class SealApiController
{
    private SealRepository $repository;

    public function __construct()
    {
        $this->repository = new SealRepository();
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
}
