<?php
namespace MapasCulturais;

use Error;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
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

    public function route(RequestInterface $request, ResponseInterface $response, $controller_id, $action, $params = [], $api = false) {
        $app = App::i();
        $app->request = new Request($request);
        $app->response = $response;
        
        if ($controller = $app->controller($controller_id)) {
            $app->view->controller = $controller;
            $controller->setRequestData($params);
            
            try{
                $controller->callAction($api ? 'API' : $request->getMethod(), $action, $params);
            } catch (Exceptions\Halt $e){
                // não precisa fazer nada.
            } catch (Exceptions\TemplateNotFound $e){
                eval(\psy\sh());
                $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => $e], false);

            } catch (Exceptions\PermissionDenied $e){
                $this->callAction($app->controller('site'), 'error', ['code' => 403, 'e' => $e], false);

            }  catch (Exceptions\WorkflowRequest $e){
                $requests = array_map(function($e){ return $e->getRequestType(); }, $e->requests);

                $app->response = $app->response->withStatus(202);
                $app->response->getBody()->write(json_encode($requests));
            } 
        } else {
            $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => new Exceptions\TemplateNotFound], false);
        }
    }

    protected function addRoutes(){
        $app = App::i();
        $slim = $app->slim;

        $slim->any("[/{args:.*}]", function (RequestInterface $request, ResponseInterface $response, array $path) use($app) {
            $parts = array_filter(explode('/', $path['args']));

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

        // $slim->get('/api/{controller}/{action}[/{args:.*}]', function(RequestInterface $request, ResponseInterface $response, array $args) use($self, $app) {
        //     $params = $self->extractArgs(explode('/', $args['args'] ?? ''));
        //     $self->route($request, $response, $args['controller'], $args['action'], $params, api: true);

        //     return $app->response;
        // });

        // foreach($controllers as $controller_id => $controller_class) {
        //     $slim->any("/{$controller_id}/{action}[/{args:.*}]", function(RequestInterface $request, ResponseInterface $response, array $args) use($app, $controller_id, $self) {
        //         $params = $self->extractArgs(explode('/', $args['args'] ?? ''));
        //         $self->route($request, $response, $controller_id, $args['action'], $params);

        //         return $app->response;
        //     });

        //     // rota padrão do controlador
        //     $slim->any("/{$controller_id}[/]", function(RequestInterface $request, ResponseInterface $response, array $args) use($app, $controller_id, $self) {

        //     });
        // }

        // $shortcuts = $app->config['routes']['shortcuts'] ?? [];

        // foreach($shortcuts as $shortcut => $target) {
        //     $slim->any("/{$shortcut}[/{args:.*}]", function (RequestInterface $request, ResponseInterface $response, array $args) use($target, $self, $app) {
        //         $controller_id = $target[0];
        //         $action = $target[1];
        //         $shortcut_params = $target[2] ?? [];
        //         $params = $self->extractArgs(explode('/', $args['args'] ?? ''));

        //         $params = array_merge($shortcut_params, $params);
        //         $self->route($request, $response, $controller_id, $action, $params);

        //         return $app->response;
        //     });
        // }

        // $slim->get('/', function(RequestInterface $request, ResponseInterface $response) use($self, $app) {
        //     $controller_id = $app->config['routes']['default_controller_id'];
        //     $action = $app->config['routes']['default_action_name'];
            
        //     $self->route($request, $response, $controller_id, $action);

        //     return $app->response;;
        // });

        return ;
        $app->map('/(:args+)', function ($url_args = []) use ($app){
            $api_call = false;
            if(key_exists(0, $url_args) && $url_args[0] === 'api'){
                array_shift($url_args);
                $api_call = true;
            }

            // regex with all shortcuts
            $shortcuts_preg = '#^/(' . implode('|', array_keys($this->config['shortcuts'])) . ')(/|$)#';

            // if is the root url
            if(!$url_args){
                // get the default controller id
                $controller_id = $this->config['default_controller_id'];

                // and the default action name
                $action_name = $this->config['default_action_name'];
                $args = [];

            // if url starts with one of the shortcuts
            }elseif(preg_match($shortcuts_preg, $app->request->getPathInfo(), $matches)){
                // get the matched shortcut
                $shortcut = $this->config['shortcuts'][$matches[1]];

                // get the controller id and action name from shortcut
                $controller_id = $shortcut[0];
                $action_name = $shortcut[1];

                // shortcut arguments
                $args = key_exists(2, $shortcut) ? $shortcut[2] : [];

                $url_args = explode('/', preg_replace('#^/' . $matches[1] . '/?#', '', $app->request->getPathInfo()));

                // extract the arguments from url
                $args = $args + $this->extractArgs($url_args);

            // controller/action urls
            }else{
                // the first url argument is the controller id
                $controller_id = array_shift($url_args);

                // if the controller id is an controller alias, get the real controller id
                if(key_exists($controller_id, $this->config['controllers']))
                    $controller_id = $this->config['controllers'][$controller_id];

                // if the next argument is an action
                if($url_args && $url_args[0] && strpos($url_args[0], ':') === false && !is_numeric($url_args[0])){
                    $action_name = array_shift($url_args);

                    // if the action name is an action alias, get the real action name
                    if(key_exists($action_name, $this->config['actions']))
                        $action_name = $this->config['actions'][$action_name];

                // else if there is no action in url, get the default action name
                }else{
                    $action_name = $this->config['default_action_name'];
                }

                // extract the arguments from url to pass to action
                $args = $this->extractArgs($url_args);
            }

            $app->applyHook('routes.filter', [&$controller_id, &$action_name, &$args, &$api_call]);

            if($controller = $app->controller($controller_id)){
                try{
                    $this->callAction($controller, $action_name, $args, $api_call);
                }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
                    
                    $this->callAction($app->controller('site'), 'error', ['code' => 403, 'e' => $e], false);

                }  catch (\MapasCulturais\Exceptions\WorkflowRequest $e){
                    $requests = array_map(function($e){ return $e->getRequestType(); }, $e->requests);
                    if($app->request->isAjax()){
                        $app->halt(202, json_encode($requests) );
                    }else{
                        $app->halt(202, \MapasCulturais\i::__('Created requests: ') . implode(', ',$requests) );
                    }
                } 
            }else{
                $app->pass();
            }

        })->via('GET', 'POST', 'PUT', 'DELETE', 'PATCH');
        
        $app->notFound(function() use ($app) {
            $this->callAction($app->controller('site'), 'error', ['code' => 404, 'e' => new Exceptions\TemplateNotFound], false);
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
            if ($i > 2 || is_numeric($path[$i]) || strpos($path[$i], ':')){
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

    protected final function callAction(Controller $controller, $action_name, array $args, $api_call){
        $controller->setRequestData( $args );
        if($api_call && !$controller->usesAPI()){
            App::i()->pass();
        }else{
            App::i()->view->setController($controller);
            $controller->callAction( $api_call ? 'API' : App::i()->request->getMethod(), $action_name, $args );
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
