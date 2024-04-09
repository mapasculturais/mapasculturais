<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\OpportunityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class OpportunityApiController
{
    private OpportunityRepository $repository;

    public function __construct()
    {
        $this->repository = new OpportunityRepository();
    }

    public function getList(): JsonResponse
    {
        $opportunities = $this->repository->findAll();

        return new JsonResponse($opportunities);
    }

    public function getOne(array $params): JsonResponse
    {
        $opportunity = $this->repository->find((int) $params['id']);

        return new JsonResponse($opportunity);
    }
}
