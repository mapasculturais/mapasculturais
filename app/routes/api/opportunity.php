<?php

declare(strict_types=1);

use App\Controller\Api\OpportunityApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/opportunities' => [
        Request::METHOD_GET => [OpportunityApiController::class, 'getList'],
        Request::METHOD_POST => [OpportunityApiController::class, 'post'],
    ],
    '/api/v2/opportunities/{id}' => [
        Request::METHOD_GET => [OpportunityApiController::class, 'getOne'],
        Request::METHOD_PATCH => [OpportunityApiController::class, 'patch'],
        Request::METHOD_DELETE => [OpportunityApiController::class, 'delete'],
    ],
];
