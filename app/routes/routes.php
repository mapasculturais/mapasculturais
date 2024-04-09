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
    $controller = $routeData[0];
    $method = $routeData[1];

    $route = new Route(path: $key, defaults: [
        '_controller' => $controller,
        '_method' => $method,
    ]);

    $routesCollection->add($key, $route);
}

return $routesCollection;
