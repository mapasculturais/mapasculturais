<?php

declare(strict_types=1);

use App\Controller\DocumentationController;

return [
    '/mapas/docs/v1' => [DocumentationController::class, 'v1'],
    '/mapas/docs/v2' => [DocumentationController::class, 'v2'],
];
