<?php

declare(strict_types=1);

use App\Controller\Api\EventApiController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/api/v2/events/types' => [
        Request::METHOD_GET => [EventApiController::class, 'getTypes'],
    ],
];