<?php

declare(strict_types=1);

use App\Controller\Api\ProjectApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/projects' => [
        Request::METHOD_GET => [ProjectApiController::class, 'getList'],
    ],
    '/api/v2/projects/{id}' => [
        Request::METHOD_GET => [ProjectApiController::class, 'getOne'],
    ],
];