<?php

namespace PersonalAccessToken\Middleware;

use Apps\JWTAuthProvider;
use MapasCulturais\App;
use PersonalAccessToken\AuthProviders\PATAuthProvider;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class APIAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = App::i();
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $handler->handle($request);
        }

        $token = $authHeader;
        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (empty($token)) {
            return $handler->handle($request);
        }

        if (str_starts_with($token, PersonalAccessToken::TOKEN_PREFIX)) {
            $auth = new PATAuthProvider(['token' => $token]);

            if (!$auth->isUserAuthenticated()) {
                $response = new \Slim\Psr7\Response(401);
                $response->getBody()->write(json_encode([
                    'error' => 'Token inválido, expirado ou revogado',
                ]));
                return $response
                    ->withHeader('Content-Type', 'application/json');
            }

            $app->auth = $auth;
            $app->config['app.usePermissionsCache'] = false;
        } else {
            $app->auth = new JWTAuthProvider(['token' => $token]);
        }

        return $handler->handle($request);
    }
}
