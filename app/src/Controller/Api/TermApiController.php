<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\TermRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TermApiController
{
    private TermRepository $repository;

    public function __construct()
    {
        $this->repository = new TermRepository();
    }

    public function getList(): JsonResponse
    {
        $terms = $this->repository->findAll();

        return new JsonResponse($terms);
    }

    public function getOne(array $params): JsonResponse
    {
        $term = $this->repository->find((int) $params['id']);

        return new JsonResponse($term);
    }
}
