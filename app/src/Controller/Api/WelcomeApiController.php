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
}
