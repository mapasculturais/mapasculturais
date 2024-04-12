<?php

declare(strict_types=1);

use App\Controller\Api\SealApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/seals' => [
        Request::METHOD_GET => [SealApiController::class, 'getList'],
    ],
    '/api/v2/seals/{id}' => [
        Request::METHOD_GET => [SealApiController::class, 'getOne'],
    ],
];