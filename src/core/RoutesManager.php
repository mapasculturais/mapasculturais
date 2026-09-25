<?php
namespace MapasCulturais;

use Error;
use MapasCulturais\Exceptions\NotFound;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use RuntimeException;
use Throwable;

/**
 * Gerenciador de rotas do MapasCulturais
 *
 * Esta classe adiciona uma regra à função map do Slim que captura todas as requisições.
 *
 * Se nenhuma rota for solicitada (/) o controlador e ação padrão serão usados (site/index por padrão).
 *
 * Se a rota for um atalho (shortcut), chama o controlador e ação com os parâmetros definidos no atalho.
 *
 * Se a rota for um ID de controlador ou um alias de controlador, chama a ação padrão deste controlador.
 *
 * Se a rota contiver um ID de controlador ou um alias de controlador e uma ação ou alias de ação, chama a ação deste controlador.
 *
 * @package MapasCulturais
 */
class RoutesManager{
    use Traits\MagicGetter;
    
    /**
     * Cria o gerenciador de rotas
     */
    public function __construct() {
        $this->addRoutes();
    }

    /**
     * Retorna a configuração de rotas
     * 
     * @return array
     */
    function getConfig(){
        $app = App::i();
        $config = $app->config['routes'];
        if(is_array($config['shortcuts'])){
            foreach($config['shortcuts'] as $k => $shortcuts){
                if(key_exists(2, $shortcuts))
                    ksort($config['shortcuts'][$k][2]);
            }
        }

        return $config;
    }

    /**
     * Executa o roteamento de uma rota
     * 
     * @param RequestInterface $request Requisição PSR-7
     * @param ResponseInterface $response Resposta PSR-7
     * @param string $controller_id ID do controlador
     * @param string $action Nome da ação
     * @param array $params Parâmetros da rota
     * @param bool $api Indica se é uma chamada à API
     * @return ResponseInterface Resposta processada
     * @throws NotFound Quando o controlador não é encontrado
     * @throws InvalidArgumentException Quando os argumentos são inválidos
     * @throws RuntimeException Quando ocorre um erro de execução
     */
    protected function route(RequestInterface $request, ResponseInterface $response, $controller_id, $action, $params = [], $api = false): ResponseInterface {
        $app = App::i();
        $app->request = new Request($request, $controller_id, $action, $params);
        $app->response = $response;

        $controller = $app->controller($controller_id);

        if(!$controller) {
            $this->callAction($app->controller('site'), 'error', ['code' => 404, 'exception' => new Exceptions\NotFound], false);
            $app->response = $app->response->withHeader('Error-Code', 404);
            $app->response = $app->response->withStatus(404);
            return $app->response;
        }

        $app->view->controller = $controller;
        
        try{
            $this->callAction($controller, $action, $params, $api);
        } catch (Exceptions\Halt $e){
            // não precisa fazer nada.
        } catch (Exceptions\NotFound $e){
            $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => $e], false);
            $app->response = $app->response->withHeader('Error-Code', 404);
            $app->response = $app->response->withStatus(404);

        } catch (Exceptions\PermissionDenied $e){
            $this->callAction($app->controller('site'), 'error', ['code' => 403, 'exception' => $e], false);
            $app->response = $app->response->withHeader('Error-Code', 403);
            $app->response = $app->response->withStatus(403);

        }  catch (Exceptions\WorkflowRequest $e){
            $requests = array_map(function($e){ return $e->getRequestType(); }, $e->requests);

            $app->response = $app->response->withStatus(202);
            $app->response->getBody()->write(json_encode($requests));
        } 
        
        return $app->response;
    }

    /**
     * Adiciona as rotas no Slim
     * 
     * @return void 
     */
    protected function addRoutes(){
        $app = App::i();
        $slim = $app->slim;

        $slim->any("[/{args:.*}]", function (RequestInterface $request, ResponseInterface $response, array $path) use($app) {
            $parts = array_values(array_filter(explode('/', $path['args'])));
            
            if (($parts[0] ?? null) == 'api') {
                $api_call = true;
                array_shift($parts);
            } else {
                $api_call = false;
            }

            $parts = $this->replaceShortcuts($parts);
            $args = $this->extractArgs($parts);

            $controller_id = $parts[0] ?? $this->config['default_controller_id'];
            $action_name = $parts[1] ?? $this->config['default_action_name'];
            
            $response = $this->route($request, $response, $controller_id, $action_name, $args, $api_call);

            return $response;
        });
    }


    /**
     * Substitui os atalhos (shortcuts) e aliases pelos slugs utilizados no código
     * 
     * @param array $parts Partes do caminho da URL
     * @return array Partes do caminho com atalhos substituídos
     */
    protected function replaceShortcuts(array $parts): array {
        $app = App::i();
        
        $shortcuts = $app->config['routes']['shortcuts'] ?? [];

        // substitui os shortcuts
        foreach ($shortcuts as $shortcut => $target) {
            $shortcut_parts = explode('/', $shortcut);
            $match = true;
            foreach ($shortcut_parts as $i => $part) {
                if ($part !== ($parts[$i] ?? null)) {
                    $match = false;
                }
            }

            if ($match) {
                $args = array_slice($parts, count($shortcut_parts));
                $parts = [$target[0], $target[1], ...($target[2] ?? []), ...$args];
            }
        }

        // substitui os alias dos controladores
        if ($parts[0] ?? false) {
            $parts[0] = $app->config['routes']['controllers'][$parts[0]] ?? $parts[0];
        }

        // substitui os alias das ações
        if ($parts[1] ?? false) {
            $parts[1] = $app->config['routes']['actions'][$parts[1]] ?? $parts[1];
        }

        return $parts;
    }


    /**
     * Extrai os argumentos do caminho da URI, deixando no $path somente o controller e a action
     * e retornando um array com os argumentos da URL, como nos exemplos abaixo:
     * 
     * ['agent', 11] => retorna ['id' => 11] e o path fica ['agent']
     * ['agent', 'edit', '11'] => retorna um array ['id' => 11] e o $path fica ['agent', 'edit']
     * ['agent', 'test', 'name:Fulano', 'idade:33'] => retorna ['name' => 'Fulano', 'idade' => 33]
     * ['agent', 'test', 11, 'name:Fulano', 'idade:33'] => retorna ['id' => 11, 'name' => 'Fulano', 'idade' => 33]
     * 
     * @param array &$path Referência ao array do caminho (será modificado)
     * @return array Argumentos extraídos
     */
    protected function extractArgs(array &$path){
        $args = [];
        for ($i = count($path) -1; $i >= 0; $i--) {
            if ($i >= 2 || is_numeric($path[$i]) || strpos($path[$i], ':')){
                $arg = array_pop($path);
                if (is_numeric($arg)) {
                    if ((int) $arg == $arg) {
                        $args['id'] = (int) $arg;
                    } else {
                        $args[] = (float) $arg;
                    }
                } else if (strpos($arg, ':')) {
                    list($key, $val) = explode(':', $arg, 2);
                    $args[$key] = $val;
                } else {
                    $args[] = $arg;
                }
            }
        }
        return $args;
    }

    /**
     * Chama uma ação em um controlador
     * 
     * @param Controller $controller Controlador
     * @param string $action_name Nome da ação
     * @param array $args Argumentos para a ação
     * @param bool $api_call Indica se é uma chamada à API
     * @return void
     */
    final function callAction(Controller $controller, $action_name, array $args, $api_call) {
        $app = App::i();
        $controller->setRequestData( $args );
        if($api_call && !$controller->usesAPI()){
            $app->pass();
        }else{
            $app->view->controller = $controller;
            try{
                $controller->callAction( $api_call ? 'API' : $app->request->getMethod(), $action_name, $args );
            } catch (Exceptions\Halt $e){
                // não precisa fazer nada
            }
        }
    }

    /**
     * Cria uma URL para um controlador e ação específicos
     * 
     * @param string $controller_id ID do controlador
     * @param string $action_name Nome da ação (opcional, padrão: ação padrão)
     * @param array $args Argumentos para a URL
     * @return string URL gerada
     */
    public function createUrl($controller_id, $action_name = '', array $args = []){
        $app = App::i();

        if ($controller_id == $app->config['routes']['default_controller_id'] && $action_name == $app->config['routes']['default_action_name']) {
            return $app->baseUrl;
        }

        if($action_name == '')
            $action_name = $this->config['default_action_name'];

        ksort($args);

        $route = '';

        if($args && in_array([$controller_id, $action_name, $args], $this->config['shortcuts'])){
            $route = array_search([$controller_id, $action_name, $args], $this->config['shortcuts']) . '/';
            $args = [];
        }elseif(in_array([$controller_id, $action_name], $this->config['shortcuts'])){
            $route = array_search([$controller_id, $action_name], $this->config['shortcuts']) . '/';
        }else{
            if(in_array($controller_id, $this->config['controllers'])){
                $route = array_search($controller_id, $this->config['controllers']) . '/';
            }else{
                $route = $controller_id . '/';
            }

            if($action_name != $this->config['default_action_name']){
                if(in_array($action_name, $this->config['actions'])){
                    $route .= array_search($action_name, $this->config['actions']) . '/';
                }else{
                    $route .= $action_name . '/';
                }
            }
        }

        foreach($args as $key => $val)
            if(is_numeric($key))
                $route .= $val . '/';
            else
                $route .= $key . ':' . $val . '/';

        return $app->baseUrl . $route;
    }
}
