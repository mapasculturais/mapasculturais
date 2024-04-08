<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\SpaceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class SpaceApiController
{
    private SpaceRepository $repository;

    public function __construct()
    {
        $this->repository = new SpaceRepository();
    }

    public function getList(): JsonResponse
    {
        $spaces = $this->repository->findAll();

        return new JsonResponse($spaces);
    }
}
