<?php

declare(strict_types=1);

use App\Controller\Api\AgentApiController;
use App\Controller\Api\ProjectApiController;
use App\Controller\Api\SealApiController;
use App\Controller\Api\SpaceApiController;
use App\Controller\Api\WelcomeApiController;

return [
    '/api' => [WelcomeApiController::class, 'index'],
    '/api/v2' => [WelcomeApiController::class, 'index'],
    '/api/v2/agents' => [AgentApiController::class, 'getList'],
    '/api/v2/agents/{id}' => [AgentApiController::class, 'getOne'],
    '/api/v2/projects' => [ProjectApiController::class, 'getList'],
    '/api/v2/seals' => [SealApiController::class, 'getList'],
    '/api/v2/seals/{id}' => [SealApiController::class, 'getOne'],
    '/api/v2/spaces' => [SpaceApiController::class, 'getList'],
    '/api/v2/spaces/{id}' => [SpaceApiController::class, 'getOne'],
];
