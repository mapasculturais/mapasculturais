<?php

declare(strict_types=1);

use App\Controller\Api\ProjectApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/projects' => [
        Request::METHOD_GET => [ProjectApiController::class, 'getList'],
        Request::METHOD_POST => [ProjectApiController::class, 'post'],
    ],
    '/api/v2/projects/{id}' => [
        Request::METHOD_GET => [ProjectApiController::class, 'getOne'],
        Request::METHOD_PATCH => [ProjectApiController::class, 'patch'],
        Request::METHOD_DELETE => [ProjectApiController::class, 'delete'],
    ],
];
