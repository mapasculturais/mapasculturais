<?php
namespace MapasCulturais;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class ErrorRender implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $app = App::i();
        $stream = fopen('php://output', 'rw');
        $app->response = $app->response->withBody(new \Slim\Psr7\Stream($stream));
        $app->view->importedComponents = [];

        if($app->request->isAjax()) {
            $app->response->withHeader('Content-Type', 'application/json');
            return json_encode(['error' => true, 'message' => $exception->getMessage()]);
        } else {

            $app->controller('site')->render('error-500', [
                'code' => 500, 
                'exception' => $exception,
                'display_details' => \env('DISPLAY_ERROR_DETAIL', false)
            ]);
            return $app->response->getBody()->getContents();
        }
    }
}   