<?php

declare(strict_types=1);

use App\Controller\Api\EventApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/events' => [
        Request::METHOD_GET => [EventApiController::class, 'getList'],
        Request::METHOD_POST => [EventApiController::class, 'post'],
    ],
    '/api/v2/events/types' => [
        Request::METHOD_GET => [EventApiController::class, 'getTypes'],
    ],
    '/api/v2/events/{id}' => [
        Request::METHOD_GET => [EventApiController::class, 'getOne'],
        Request::METHOD_PATCH => [EventApiController::class, 'patch'],
        Request::METHOD_DELETE => [EventApiController::class, 'delete'],
    ],
];
