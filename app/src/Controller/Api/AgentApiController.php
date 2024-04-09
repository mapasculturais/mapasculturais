<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\AgentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgentApiController
{
    private AgentRepository $repository;

    public function __construct()
    {
        $this->repository = new AgentRepository();
    }

    public function getList(): JsonResponse
    {
        $agents = $this->repository->findAll();

        return new JsonResponse($agents);
    }

    public function getOne(int $id): JsonResponse
    {
        $agent = $this->repository->find($id);

        return new JsonResponse($agent);
    }
}