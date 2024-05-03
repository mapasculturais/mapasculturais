<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ProjectRepository;
use App\Request\ProjectRequest;
use App\Service\ProjectService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectApiController
{
    public ProjectService $projectService;
    private ProjectRequest $projectRequest;

    private ProjectRepository $repository;

    public function __construct()
    {
        $this->repository = new ProjectRepository();

        $this->projectService = new ProjectService();
        $this->projectRequest = new ProjectRequest();
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

    public function post(): JsonResponse
    {
        try {
            $projectData = $this->projectRequest->validatePost();

            $project = $this->projectService->create((object) $projectData);

            $responseData = [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'shortDescription' => $project->getShortDescription(),
                'type' => $project->getType(),
            ];

            return new JsonResponse($responseData, status: Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], status: Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(array $params): JsonResponse
    {
        $project = $this->repository->find((int) $params['id']);

        if (!$project || -10 === $project->status) {
            return new JsonResponse(status: Response::HTTP_NOT_FOUND);
        }

        $this->repository->softDelete($project);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
