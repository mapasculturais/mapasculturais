<?php

declare(strict_types=1);

use App\Controller\DocumentationController;
use Symfony\Component\HttpFoundation\Request;

return [
    '/mapas/docs/v1' => [
        Request::METHOD_GET => [DocumentationController::class, 'v1'],
    ],
    '/mapas/docs/v2' => [
        Request::METHOD_GET => [DocumentationController::class, 'v2'],
    ],
];
