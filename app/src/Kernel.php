<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Kernel
{
    private string $url;
    private RouteCollection $routes;

    public function __construct()
    {
        $this->url = $this->getPathRequest();
        $this->routes = require_once dirname(__DIR__).'/routes/routes.php';
    }

    public function execute(): void
    {
        try {
            $context = new RequestContext(
                method: $_SERVER['REQUEST_METHOD']
            );

            $matcher = new UrlMatcher($this->routes, $context);

            $this->dispatchAction($matcher);
        } catch (MethodNotAllowedException $exception) {
            (new JsonResponse([
                'error' => 'Method not allowed: '.$_SERVER['REQUEST_METHOD'],
            ], status: Response::HTTP_METHOD_NOT_ALLOWED))->send();

            exit;
        } catch (ResourceNotFoundException $exception) {
            return;
        }
    }

    private function getPathRequest(): string
    {
        return explode('?', $_SERVER['REQUEST_URI'])[0];
    }

    private function dispatchAction(UrlMatcher $matcher): void
    {
        $parameters = $matcher->match($this->url);

        $controller = array_shift($parameters);
        $method = array_shift($parameters);

        unset($parameters['_route']);

        $response = (new $controller())->$method($parameters);

        if ($response instanceof Response) {
            $response->send();
        }

        exit;
    }
}
