<?php
namespace MapasCulturais;
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

    protected function addRoutes(){
        App::i()->map('/(:args+)', function ($url_args = []){
            $app = App::i();

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

                // else if there is no action in url, get the default actiona name
                }else{
                    $action_name = $this->config['default_action_name'];
                }

                // extract the arguments from url to pass to action
                $args = $this->extractArgs($url_args);
            }


            if($controller = $app->controller($controller_id)){
                try{
                    $this->callAction($controller, $action_name, $args, $api_call);
                }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){

                    if($app->config['slim.debug']){
                        if($app->request()->isAjax())
                            $app->halt(403, \MapasCulturais\i::__('Permission Denied:') . $e);
                        else
                            $app->halt(403, \MapasCulturais\i::__('Permission Denied:') . '<br><pre>' . $e . '</pre>');
                    }else{
                        $app->halt(403, \MapasCulturais\i::__('Permission Denied'));
                    }
                }  catch (\MapasCulturais\Exceptions\WorkflowRequest $e){
                    $requests = array_map(function($e){ return $e->getRequestType(); }, $e->requests);
                    if($app->request()->isAjax())
                        $app->halt(202, json_encode($requests) );
                    else
                        $app->halt(202, \MapasCulturais\i::__('Created requests:') . implode(', ',$requests) );
                }
            }else{
                $app->pass();
            }

        })->via('GET', 'POST', 'PUT', 'DELETE', 'PATCH');
    }

    /**
     * Extract data from URL
     *
     * for the URL:<br/>
     * <b>http://mapasculturais/controller/action/some%20value/name:Fulano/lastName:de%20Tal/age:31/</b><br/>
     * this method will return the array:<br/>
     * <b>[0 => 'a value', 'name' => 'Fulano', 'lastName' => 'de Tal', 'age' => '31']</b>
     *
     * @param array $args
     *
     * @return array Extracted data
     */
    protected function extractArgs($url_args){
        $data = [];
        $i = 0;

        foreach($url_args as $arg){
            if(!$arg) continue;

            if(strpos($arg, ':') !== false){
                list($key, $val) = explode(':', $arg, 2);
                $data[$key] = $val;
            }else{
                if($i === 0 && is_numeric($arg) && !key_exists('id', $data))
                    $data['id'] = $arg;
                else
                    $data[$i++] = $arg;
            }
        }
        return $data;
    }

    protected final function callAction(Controller $controller, $action_name, array $args, $api_call){
        $controller->setRequestData( $args );
        if($api_call && !$controller->usesAPI()){
            App::i()->pass();
        }else{
            App::i()->view->setController($controller);
            $controller->callAction( $api_call ? 'API' : App::i()->request()->getMethod(), $action_name, $args );
        }
    }

    public function createUrl($controller_id, $action_name = '', array $args = []){

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

        return App::i()->baseUrl . $route;
    }
}
