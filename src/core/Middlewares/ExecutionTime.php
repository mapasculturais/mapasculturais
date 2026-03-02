<?php
namespace MapasCulturais\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use MapasCulturais\App;

/**
 * Middleware para medição do tempo de execução de requisições
 * 
 * Este middleware mede e registra o tempo de execução e uso de memória
 * de cada requisição processada pelo sistema, útil para monitoramento
 * de performance e depuração.
 * 
 * @package MapasCulturais\Middlewares
 */
class ExecutionTime {
    
    /**
     * Processa a requisição medindo o tempo de execução
     * 
     * @param Request $request Requisição HTTP
     * @param RequestHandler $handler Handler da requisição
     * @return \Psr\Http\Message\ResponseInterface Resposta HTTP
     */
    public function __invoke(Request $request, RequestHandler $handler) {
        $app = App::i();

        $app->log->info('=========================================================================');
        $app->log->debug($_SERVER['REQUEST_METHOD'] . ' ' . urldecode($_SERVER['REQUEST_URI']));

        $response = $handler->handle($request);

        $endTime = microtime(true);
        $execution_time = number_format($endTime - $app->startTime, 3);
        $mem = memory_get_usage(true) / 1024 / 1024;

        $route = $app->request->route;

        $log = "{$route} - executed in {$execution_time} seconds. (MEM: {$mem}MB)";

        $app->log->info($log);
        $app->log->info('=========================================================================');
        
        return $response;
    }

    /**
     * Calcula o tempo de uso de recursos do sistema
     * 
     * @param array $ru Dados de uso de recursos atuais
     * @param array $rus Dados de uso de recursos iniciais
     * @param string $index Índice do recurso a ser calculado
     * @return float Tempo em milissegundos
     */
    function rutime($ru, $rus, $index) {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
         -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }
}