<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class WelcomeApiController
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'API' => 'MapaCultural',
        ]);
    }

    public function create(): JsonResponse
    {
        return new JsonResponse([
            'API' => 'MapaCultural - Test POST',
        ]);
    }

    public function delete(): JsonResponse
    {
        return new JsonResponse([
            'API' => 'MapaCultural - Test DELETE',
        ]);
    }
}
