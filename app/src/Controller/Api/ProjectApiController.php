<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectApiController
{
    private ProjectRepository $repository;

    public function __construct()
    {
        $this->repository = new ProjectRepository();
    }

    public function getList(): JsonResponse
    {
        $projects = $this->repository->findAll();

        return new JsonResponse($projects);
    }

    public function getOne(array $params): JsonResponse
    {
        $project = $this->repository->find((int) $params['id']);

        return new JsonResponse($project);
    }
}
