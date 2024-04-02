<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Response;

class Kernel
{
    public static function execute(): void
    {
        include dirname(__DIR__ ) . '/vendor/autoload.php';

        $routes = require_once dirname(__DIR__).'/routes/routes.php';

        $url = $_SERVER['REQUEST_URI'];

        if (false === isset($routes[$url])) {
            return;
        }

        $controller = $routes[$url][0];
        $method = $routes[$url][1];

        $response = (new $controller())->$method();

        if ($response instanceof Response) {
            $response->send();
        }

        exit;
    }
}
