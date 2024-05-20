<?php

declare(strict_types=1);

use App\Controller\Api\SealApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/seals' => [
        Request::METHOD_GET => [SealApiController::class, 'getList'],
        Request::METHOD_POST => [SealApiController::class, 'post'],
    ],
    '/api/v2/seals/{id}' => [
        Request::METHOD_GET => [SealApiController::class, 'getOne'],
        Request::METHOD_PATCH => [SealApiController::class, 'patch'],
        Request::METHOD_DELETE => [SealApiController::class, 'delete'],
    ],
];
