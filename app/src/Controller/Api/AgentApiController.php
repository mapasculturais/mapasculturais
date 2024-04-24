<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\AgentRepository;
use App\Service\AgentService;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgentApiController
{
    public AgentService $agentService;

    private AgentRepository $repository;

    public function __construct()
    {
        $this->repository = new AgentRepository();

        $this->agentService = new AgentService();
    }

    public function getList(): JsonResponse
    {
        $agents = $this->repository->findAll();

        return new JsonResponse($agents);
    }

    public function getOne(array $params): JsonResponse
    {
        $agent = $this->repository->find((int) $params['id']);

        return new JsonResponse($agent);
    }

    public function getTypes(): JsonResponse
    {
        $types = $this->agentService->getTypes();

        return new JsonResponse($types);
    }

    public function delete(array $params): JsonResponse
    {
        $agent = $this->repository->find((int) $params['id']);

        if (!$agent || -10 === $agent->status) {
            return new JsonResponse(status: Response::HTTP_NOT_FOUND);
        }

        $this->repository->softDelete($agent);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
