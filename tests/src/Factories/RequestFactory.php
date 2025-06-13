<?php

namespace Tests\Factories;

use Exception;
use Laminas\Diactoros\ServerRequest;
use MapasCulturais\App;
use MapasCulturais\Entity;
use Psr\Http\Message\ServerRequestInterface;

class RequestFactory
{
    function createServerRequest(string $method, string $controller_id, string $action, array $url_params = [], array $query_params = [], array $headers = [], array $cookie_params = [], string|array|null $parsed_body = null, array $uploaded_files = []): ServerRequestInterface
    {
        if(!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'])) {
            throw new Exception('Invalid HTTP Request Method: ' . $method);
        }

        $app = App::i();

        $url = $app->createUrl($controller_id, $action, $url_params);
        $uri = str_replace($app->baseUrl, '/', $url);
        $request = new ServerRequest(method: $method, uri: $uri, queryParams: $query_params, cookieParams: $cookie_params, headers: $headers, parsedBody: $parsed_body, uploadedFiles: $uploaded_files);

        return $request;
    }

    function GET(string $controller_id, string $action, array $url_params = [], array $query_params = [], array $headers = [], array $cookie_params = []): ServerRequestInterface {
        return $this->createServerRequest(
                        method: 'GET',
                        controller_id: $controller_id,
                        action: $action,
                        url_params: $url_params,
                        query_params: $query_params,
                        headers: $headers,
                        cookie_params: $cookie_params);
    }

    function POST(string $controller_id, string $action, array $url_params = [], array $payload = [], array $query_params = [], array $headers = [], array $cookie_params = []): ServerRequestInterface
    {
        return $this->createServerRequest(
                        method: 'POST',
                        controller_id: $controller_id,
                        action: $action,
                        url_params: $url_params,
                        query_params: $query_params,
                        parsed_body: $payload,
                        headers: $headers,
                        cookie_params: $cookie_params);
    }

    function PATCH(string $controller_id, string $action, array $url_params = [], array $payload = [], array $query_params = [], array $headers = [], array $cookie_params = []): ServerRequestInterface
    {
        return $this->createServerRequest(
                        method: 'PATCH',
                        controller_id: $controller_id,
                        action: $action,
                        url_params: $url_params,
                        query_params: $query_params,
                        parsed_body: $payload,
                        headers: $headers,
                        cookie_params: $cookie_params);
    }

    function DELETE(string $controller_id, string $action, array $url_params = [], array $payload = [], array $query_params = [], array $headers = [], array $cookie_params = []): ServerRequestInterface
    {
        return $this->createServerRequest(
                        method: 'DELETE',
                        controller_id: $controller_id,
                        action: $action,
                        url_params: $url_params,
                        query_params: $query_params,
                        parsed_body: $payload,
                        headers: $headers,
                        cookie_params: $cookie_params);
    }

    function PATCH_entity(Entity $entity, $changes): ServerRequestInterface
    {
        return $this->PATCH($entity->controllerId, 'single', [$entity->id], payload: $changes);
    }
}
