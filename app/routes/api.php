<?php

declare(strict_types=1);

use App\Controller\Api\SealApiController;
use App\Controller\Api\WelcomeApiController;

return [
    '/api' => [WelcomeApiController::class, 'index'],
    '/api/v2' => [WelcomeApiController::class, 'index'],
    '/api/v2/seals' => [SealApiController::class, 'getList'],
    '/api/v2/seals/{id}' => [SealApiController::class, 'getOne'],
];
