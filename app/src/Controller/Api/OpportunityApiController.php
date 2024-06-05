<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\OpportunityRepository;
use App\Request\OpportunityRequest;
use App\Service\OpportunityService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OpportunityApiController
{
    private OpportunityRequest $opportunityRequest;
    private OpportunityService $opportunityService;
    private OpportunityRepository $repository;

    public function __construct()
    {
        $this->repository = new OpportunityRepository();
        $this->opportunityRequest = new OpportunityRequest();
        $this->opportunityService = new OpportunityService();
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

    public function post(): JsonResponse
    {
        try {
            $opportunityData = $this->opportunityRequest->validatePost();
            $opportunity = $this->opportunityService->create((object) $opportunityData);

            $responseData = [
                'id' => $opportunity->getId(),
                'name' => $opportunity->getName(),
                'terms' => $opportunity->getTerms(),
                '_type' => $opportunity->getType(),
            ];

            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getOpportunitiesByAgent(array $params): JsonResponse
    {
        $agentId = (int) $params['id'];
        $opportunities = $this->repository->findOpportunitiesByAgentId($agentId);

        return new JsonResponse($opportunities);
    }

    public function patch(array $params): JsonResponse
    {
        try {
            $opportunityData = $this->opportunityRequest->validateUpdate();
            $opportunity = $this->opportunityService->update((int) $params['id'], (object) $opportunityData);

            return new JsonResponse($opportunity, Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(array $params): JsonResponse
    {
        try {
            $opportunity = $this->opportunityRequest->validateDelete($params);
            $this->repository->softDelete($opportunity);

            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
