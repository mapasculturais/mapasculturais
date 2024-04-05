<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Response;

class Kernel
{
    private string $url;
    private null|array $currentRoute = null;

    public function __construct()
    {
        $this->url = $this->getPathRequest();
        $routes = require_once dirname(__DIR__).'/routes/routes.php';

        $this->currentRoute = $routes[$this->url] ?? null;
    }

    public function execute(): void
    {
        if (null === $this->currentRoute) {
            return;
        }

        $this->dispatchAction();
    }

    private function getPathRequest(): string
    {
        return explode('?', $_SERVER['REQUEST_URI'])[0];
    }

    private function dispatchAction(): void
    {
        $controller = $this->currentRoute[0];
        $method = $this->currentRoute[1];

        $response = (new $controller())->$method();

        if ($response instanceof Response) {
            $response->send();
        }

        exit;
    }
}
