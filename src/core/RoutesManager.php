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
 * The MapasCulturais default route manager.
 *
 * This class adds a rule to the Slim map function that captures all requests.
 *
 * If no route is requested (/) the default controller and action will be used (site/index by default).
 *
 * If the route is a shortcut, calls the controller and action with the params defined in the shortcut.
 *
 * If the route is a controller id or a controller alias, calls the default action of this controller
 *
 * If the route contains a controller id or a controller alias and an action or action alias, calls the action of this controller.
 *
 *
 *
 */
class RoutesManager{
    protected $config = [];

    /**
     * Creates the Routes Menager
     *
     * @param array $config
     */
    public function __construct(array $config) {
        if(is_array($config['shortcuts'])){
            foreach($config['shortcuts'] as $k => $shortcuts){
                if(key_exists(2, $shortcuts))
                    ksort($config['shortcuts'][$k][2]);
            }
        }
        $this->config = $config;

        $this->addRoutes();
    }

    /**
     * Faz o roteamento de uma rota
     * @param RequestInterface $request 
     * @param ResponseInterface $response 
     * @param mixed $controller_id 
     * @param mixed $action 
     * @param array $params 
     * @param bool $api 
     * @return void 
     * @throws NotFound 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
    protected function route(RequestInterface $request, ResponseInterface $response, $controller_id, $action, $params = [], $api = false) {
        $app = App::i();
        $app->request = new Request($request, $controller_id, $action, $params);
        $app->response = $response;
        
        if ($controller = $app->controller($controller_id)) {
            $app->view->controller = $controller;
            $controller->setRequestData($params);
            
            try{
                $controller->callAction($api ? 'API' : $request->getMethod(), $action, $params);
            } catch (Exceptions\Halt $e){
                // não precisa fazer nada.
            } catch (Exceptions\NotFound $e){
                $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => $e], false);

            } catch (Exceptions\PermissionDenied $e){
                $app->response = $app->response->withHeader('Error-Code', $e->code);
                $this->callAction($app->controller('site'), 'error', ['code' => 403, 'e' => $e], false);

            }  catch (Exceptions\WorkflowRequest $e){
                $requests = array_map(function($e){ return $e->getRequestType(); }, $e->requests);

                $app->response = $app->response->withStatus(202);
                $app->response->getBody()->write(json_encode($requests));
            } 
        } else {
            $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => new Exceptions\NotFound], false);
        }
    }

    /**
     * Adiciona as rotas no slim
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
            
            $this->route($request, $response, $controller_id, $action_name, $args, $api_call);

            return $app->response;
        });
    }


    /**
     * Substitui os shortcuts e alias pelos slugs utilizados no código
     * 
     * @param array $parts partes da path
     * @return array 
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
     * Extrai os argumentos do caminho da uri, deixando no $path somente o controller e a action 
     * e retornando um array com os url args, como nos exemplos abaixo
     * 
     * ['agent', 11] => retorna ['id' => 11] e o path fica ['agent']
     * ['agent', 'edit', '11'] => retorna um array ['id' => 11] e o $path fica ['agent', 'edit']
     * ['agent', 'test', 'name:Fulano', [idade:33] => retorna ['name' => 'Fulano', 'idade' => 33]
     * ['agent', 'test', 11, 'name:Fulano', [idade:33] => retorna ['id' => 11, 'name' => 'Fulano', 'idade' => 33]
     * 
     * @param array $path 
     * @return array 
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

    final function callAction(Controller $controller, $action_name, array $args, $api_call){
        $controller->setRequestData( $args );
        if($api_call && !$controller->usesAPI()){
            App::i()->pass();
        }else{
            App::i()->view->setController($controller);
            try{
                $controller->callAction( $api_call ? 'API' : App::i()->request->getMethod(), $action_name, $args );
            } catch (Exceptions\Halt $e){
                // não precisa fazer nada
            }
        }
    }

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
