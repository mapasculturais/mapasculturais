<?php

declare(strict_types=1);

require_once 'bootstrap.php';

$routes = require_once __DIR__.'/../src/app/routes/routes.php';

$url = $_SERVER['REQUEST_URI'];

if (true === isset($routes[$url])) {
    $controller = $routes[$url][0];
    $method = $routes[$url][1];

    (new $controller())->$method();
    exit;
}

$app->run();
