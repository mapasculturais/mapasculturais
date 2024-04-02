<?php

declare(strict_types=1);

use App\Controller\DocumentationController;

return [
    '/mapas/docs' => [DocumentationController::class, 'index'],
];
