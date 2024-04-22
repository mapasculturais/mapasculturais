<?php

declare(strict_types=1);

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$web = require_once 'web.php';
$api = require_once 'api.php';

$routesCollection = new RouteCollection();

$context = new RequestContext();

$routes = [
    ...$api,
    ...$web,
];

/** @var string $key */
foreach ($routes as $key => $routeData) {
    foreach ($routeData as $method => $params) {
        $controller = $params[0];
        $action = $params[1];

        $route = new Route(
            path: $key,
            defaults: [
                '_controller' => $controller,
                '_action' => $action,
            ],
            methods: [$method]
        );

        $routesCollection->add("$key-$method", $route);
    }
}

return $routesCollection;
