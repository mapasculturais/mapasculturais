<?php
namespace MapasCulturais;

use Slim\Interfaces\ErrorHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;


class ErrorHandler implements ErrorHandlerInterface {
    
    static \Slim\Handlers\ErrorHandler $defaultErrorHandler;

    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface {
        $app = App::i();
        
        if($logErrors) {

                $sanitize = function($value) {
                    return str_replace(['*','_','`'], '-', $value);
                };

                $log = "{$app->siteName}";
                $log .= "\n\n*MESSAGE*: `{$exception->getMessage()}`";

                $log .= "\n\nUserID: {$app->user->id}";

                if($app->request) {
                    
                    $log .= "\n\n*ROUTE*:  {$app->request->route}";
                    $log .= "\n*URI*: " . ($_SERVER['REQUEST_URI'] ?? '');

                    if($referer = $sanitize($app->request->getReferer())) {
                        $referer = $referer[0] ?? '';
                        $log .= "\n\n*REFERER*: {$referer}";
                    }

                    if($user_aget = $sanitize($app->request->getUserAgent())) {
                        $log .= "\n\n*USER-AGENT*: `{$user_aget}`";
                    }
                    
                    if ($logErrorDetails) {
                        if($_POST ?? false) {
                            $log .= "\n\n*POST*: " . $sanitize(print_r($_POST, true)) . "\n";
                        }
        
                        if($_GET ?? false) {
                            $log .= "\n\n*GET*: " . $sanitize(print_r($_GET, true)) . "\n";
                        }
                    }
                }

                if ($logErrorDetails) {
                    $trace = $sanitize($exception->getTraceAsString());
                    $log .= "\n*TRACE:*\n{$trace}\n";
                }

                $app->log->critical($log);
            }

            return self::$defaultErrorHandler->__invoke($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
    }
}   