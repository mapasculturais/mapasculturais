<?php

declare(strict_types=1);

use App\Controller\Api\WelcomeApiController;

return [
    '/api' => [WelcomeApiController::class, 'index'],
];
