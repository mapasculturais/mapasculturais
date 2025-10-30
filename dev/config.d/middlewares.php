<?php
return [
    'middlewares' => [
        MapasCulturais\Middlewares\ExecutionTime::class,
        Apps\Middleware\JWTAuthMiddleware::class
    ]
];
