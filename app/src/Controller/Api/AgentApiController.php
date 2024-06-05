<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\ResourceNotFoundException;
use App\Repository\AgentRepository;
use App\Request\AgentRequest;
use App\Service\AgentService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AgentApiController
{
    public AgentService $agentService;
    private AgentRequest $agentRequest;

    private AgentRepository $repository;

    public function __construct()
    {
        $this->repository = new AgentRepository();

        $this->agentService = new AgentService();
        $this->agentRequest = new AgentRequest();
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

    public function post(): JsonResponse
    {
        try {
            $agentData = $this->agentRequest->validatePost();

            $agent = $this->agentService->create((object) $agentData);

            $responseData = [
                'id' => $agent->getId(),
                'name' => $agent->getName(),
                'shortDescription' => $agent->getShortDescription(),
                'terms' => $agent->getTerms(),
                'type' => $agent->getType(),
            ];

            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function patch(array $params): JsonResponse
    {
        try {
            $agentData = $this->agentRequest->validateUpdate();
            $agent = $this->agentService->update((int) $params['id'], (object) $agentData);

            return new JsonResponse($agent, Response::HTTP_CREATED);
        } catch (ResourceNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(array $params): JsonResponse
    {
        try {
            $this->agentService->discard((int) $params['id']);

            return new JsonResponse(status: Response::HTTP_NO_CONTENT);
        } catch (ResourceNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
