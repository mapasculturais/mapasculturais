<?php
return [
    'middlewares' => [
        MapasCulturais\Middlewares\ExecutionTime::class,
        PersonalAccessToken\Middleware\APIAuthMiddleware::class
    ]
];
