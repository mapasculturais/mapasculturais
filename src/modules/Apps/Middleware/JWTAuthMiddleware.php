<?php
namespace Apps\Middleware;

use Apps\Entities\UserApp;
use Apps\JWTAuthProvider;
use MapasCulturais\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JWTAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = App::i();
        // Get Authorization header (case-insensitive) and strip optional Bearer prefix
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader) {
            $token = $authHeader;
            if (stripos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            // Switch the auth provider just for this request
            $app->auth = new JWTAuthProvider(['token' => $token]);
        }
        
        return $handler->handle($request);
    }
}
