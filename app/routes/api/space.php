<?php

declare(strict_types=1);

use App\Controller\Api\SpaceApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/spaces' => [
        Request::METHOD_GET => [SpaceApiController::class, 'getList'],
    ],
    '/api/v2/spaces/{id}' => [
        Request::METHOD_GET => [SpaceApiController::class, 'getOne'],
    ],
];