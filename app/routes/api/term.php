<?php

declare(strict_types=1);

use App\Controller\Api\TermApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/terms' => [
        Request::METHOD_GET => [TermApiController::class, 'getList'],
    ],
    '/api/v2/terms/{id}' => [
        Request::METHOD_GET => [TermApiController::class, 'getOne'],
    ],
];
