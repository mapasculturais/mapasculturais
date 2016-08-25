<?php
namespace MapasCulturais;

use \MapasCulturais\App;

/**
 * The base class for all controllers.
 *
 * To create a controller you must extends this class and register the new controller class as a controller in the application
 * with the method \MapasCulturais\App::registerController(). Inside this class you can create actions.
 *
 * The controllers actions are methods with name starting with the request type (GET_, POST_, PUT_ or DELETE_) or the word ALL_
 * followed by action name. So if you want an action that responds to all requests methods you use the word ALL, otherwise you put
 * the request type in the begin of the method name.
 *
 * Inside the action method you can access the data passed to action through
 * $this->data, $this->urlData, $this->requestData, $this->getData, $this->postData, $this->putData and $this->deleteData
 *
 * If you want that only logged in users can access an action, call the method $this->requireAuthentication(); at the first line of the action.
 * This method redirects the user, if he is not logged in, to the login page and redirects back to the action after the user successful log in
 *
 * To render a json call $this->json($data_to_encode_to_json).
 *
 * To render a template with the layout call $this->render('template-name', $array_of_data_to_pass_to_template).
 *
 * To render a template without the layout call $this->partial('template-name', $array_of_data_to_pass_to_template).
 *
 * The template files for this controller is located in the folder themes/active/views/{$controller_id}/
 *
 *
 * @property-read string $action
 *
 * @property-read array $data URL + GET + POST + PUT + DELETE data
 * @property-read array $urlData URL data
 * @property-read array $requestData GET + POST + PUT + DELETE data
 * @property-read array $getData GET data
 * @property-read array $postData POST data
 * @property-read array $putData PUT data
 * @property-read array $deleteData DELETE data
 *
 *
 * @see \MapasCulturais\App::registerController()
 *
 * @hook **{$method}({$controller_id}.{$action_name})** *($arguments)* - executed if the methods {$method}_{$action_name} and ALL_{$action_name} not exists.
 * @hook **ALL({$controller_id}.{$action_name})** *($arguments)* - executed if the methods {$method}_{$action_name} and ALL_{$action_name} and the previous hook not exists.
 *
 * @hook **{$method}:before** *($arguments)* - executed before the execution of all actions of all controllers.
 * @hook **{$method}({$controller_id}):before** *($arguments)* - executed before the execution of all actions of the controller.
 * @hook **{$method}({$controller_id}.{$action_name}):before** *($arguments)* - executed before the execution of the action.
 * @hook **ALL:before** *($arguments)* - executed before the execution of all actions of all controllers.
 * @hook **ALL({$controller_id}):before** *($arguments)* - executed before the execution ofall actions of the controller.
 * @hook **ALL({$controller_id}.{$action_name}):before** *($arguments)* - executed before the execution of the action.
 *
 * @hook **{$method}:after** *($arguments)* - executed after the execution of all actions of all controllers
 * @hook **{$method}({$controller_id}):after** *($arguments)* - executed after the execution of all actions of the controller.
 * @hook **{$method}({$controller_id}.{$action_name}):after** *($arguments)* - executed after the execution of the action.
 * @hook **ALL:after** *($arguments)* - executed after the execution of all actions of all controllers.
 * @hook **ALL({$controller_id}):after** *($arguments)* - executed after the execution of all actions of the controller.
 * @hook **ALL({$controller_id}.{$action_name}):after** *($arguments)* - executed after the execution of the action.
 */

abstract class Controller{
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers,
        Traits\Singleton;


    /**
     * URL based vars passed in URL after the action name (not by GET).
     *
     * @example for de url **http://mapasculturais/controller/action/id:11/name:Fulanano** this property will be ['id' => 11, 'name' => 'Fulano']
     *
     * @var array The URL data
     */
    protected $_urlData = [];

    /**
     * Array with the request data.
     *
     * This array is the merge of the URL based vars with $_REQUEST
     *
     * @example for the URL .../actionname/id:1/a-data/name:Fulano?age=33 the resultant array will be [id=>1, 0=>a-data, name=>Fulano, age=>33]
     * @var array
     */
    protected $data = [];


    protected $action = null;
    
    protected $method = null;
    
    // =================== GETTERS ================== //

    /**
     * Returns the controller id.
     *
     * @see \MapasCulturais\App::getControllerId()
     *
     * @return string The controller id.
     */
    public function getId(){
        return App::i()->controllerId($this);
    }

    /**
     * Returns the URL based vars passed in URL after the action name (not by GET).
     *
     * @return array URL data
     */
    public function getUrlData(){
        return $this->_urlData;
    }

    /**
     * Returns the GET + POST + PUT + DELETE data
     *
     * @return array GET + POST + PUT + DELETE data
     */
    public function getRequestData(){
        return App::i()->request()->params();
    }

    /**
     * Returns the GET data
     *
     * @return array GET data
     */
    public function getGetData(){
        return App::i()->request()->get();
    }

    /**
     * Returns the POST data
     *
     * @return array POST data
     */
    public function getPostData(){
        return App::i()->request()->post();
    }

    /**
     * Returns the PUT data
     *
     * @return array PUT data
     */
    public function getPutData(){
        return App::i()->request()->put();
    }

    /**
     * Returns the DELETE data
     *
     * @return array DELETE data
     */
    public function getDeleteData(){
        return App::i()->request()->delete();
    }



    // =================== SETTERS ===================== //

    /**
     * Set the layout to use to render the template.
     *
     * This method sets the layout in the view object.
     *
     * @see \MapasCulturais\View::setLayout()
     *
     * @param string $layout
     */
    public function setLayout($layout){
        App::i()->view()->layout = $layout;
    }

    /**
     * Defines the request data to be used in actions.
     *
     * @param array $args
     */
    public function setRequestData(array $args){
        $this->_urlData = $args;
        $this->data = $args + App::i()->request()->params();
    }




    /**
     * Call an action of this controller.
     *
     * The action is a method named {$method}_actionName (ex: GET_list) or a hook like GET(controllerId.actionName).
     *
     * This method first try to call a method starting with the request type (like GET_), then try to call a method starting with the word ALL_, then try to call the hooks.
     * If none of these methods exists, the request is passed by calling the App::i()->pass().
     *
     * For the API actions the name of the action method must starts with API_ (ex: API_actionName)
     *
     * @param string $method (GET, PUT, POST, DELETE or ALL)
     * @param string $action_name the action name
     * @param array $arguments arguments to pass to action
     *
     * @example For a POST request to the ..../controller_id/actionName, first try Controller::POST_actionName, then Controller::ALL_actionName,
     *          then the hook with name POST(controller_id.actionName), then the hook ALL(controller_id.actionName)
     *
     * @hook **{$method}({$controller_id}.{$action_name})** *($arguments)* - executed if the methods {$method}_{$action_name} and ALL_{$action_name} not exists.
     * @hook **ALL({$controller_id}.{$action_name})** *($arguments)* - executed if the methods {$method}_{$action_name} and ALL_{$action_name} and the previous hook not exists.
     *
     * @hook **{$method}:before** *($arguments)* - executed before the execution of all actions of all controllers.
     * @hook **{$method}({$controller_id}):before** *($arguments)* - executed before the execution of all actions of the controller.
     * @hook **{$method}({$controller_id}.{$action_name}):before** *($arguments)* - executed before the execution of the action.
     * @hook **ALL:before** *($arguments)* - executed before the execution of all actions of all controllers.
     * @hook **ALL({$controller_id}):before** *($arguments)* - executed before the execution ofall actions of the controller.
     * @hook **ALL({$controller_id}.{$action_name}):before** *($arguments)* - executed before the execution of the action.
     *
     * @hook **{$method}:after** *($arguments)* - executed after the execution of all actions of all controllers
     * @hook **{$method}({$controller_id}):after** *($arguments)* - executed after the execution of all actions of the controller.
     * @hook **{$method}({$controller_id}.{$action_name}):after** *($arguments)* - executed after the execution of the action.
     * @hook **ALL:after** *($arguments)* - executed after the execution of all actions of all controllers.
     * @hook **ALL({$controller_id}):after** *($arguments)* - executed after the execution of all actions of the controller.
     * @hook **ALL({$controller_id}.{$action_name}):after** *($arguments)* - executed after the execution of the action.
     *
     */
    public function callAction($method, $action_name, $arguments) {
        $app = App::i();

        if(@$app->config['app.log.requestData']){
            $app->log->debug('===== POST DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->postData,true));

            $app->log->debug('===== GET DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->getData,true));

            $app->log->debug('===== URL DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->urlData,true));
        }

        $this->action = $action_name;

        $method = strtoupper($method);
        
        $this->method = $method;

        // hook like GET(user.teste)
        $hook = $method . "({$this->id}.{$action_name})";
        
        // hook like ALL(user.teste)
        $ALL_hook =  $method !== 'API' ? "ALL({$this->id}.{$action_name})" : null;

        $call_method = null;
        $call_hook = null;

        // first try to call an action defined inside the controller
        if(method_exists($this, $method . '_' . $action_name)){
            $call_method = [$this, $method . '_' . $action_name];

        }elseif($method !== 'API' && method_exists($this, 'ALL_' . $action_name)){
            $call_method = [$this, 'ALL_' . $action_name];

        // then try to call an action defined outside the controller
        }elseif($app->getHooks($hook)){
            $call_hook = $hook;

        }elseif($method !== 'API' && $app->getHooks($ALL_hook)){
            $call_hook = $ALL_hook;

        }

        if($call_method || $call_hook){
            $app->applyHookBoundTo($this, $hook . ':before', $arguments);

            if($call_method)
                call_user_func_array($call_method, $arguments);
            else
                $app->applyHookBoundTo($this, $call_hook, $arguments);

            $app->applyHookBoundTo($this, $hook . ':after', $arguments);
        // else pass to 404?
        }else{
            $app->pass();
        }

    }

    /**
     * Render a template in the folder with the name of the controller id
     *
     * @param string $template the template name
     * @param type $data array with data to pass to the template
     */
    public function render($template, $data = []){
        $app = App::i();
        $app->applyHookBoundTo($this, 'controller(' . $this->id . ').render(' . $template . ')', ['template' => &$template, 'data' => &$data]);

        $template = $this->id . '/' . $template;
        $app->render($template, $data);
    }

    /**
     * Render a template without the layout, in the folder with the name of the controller id
     *
     * @param string $template the template name
     * @param type $data array with data to pass to the template
     */
    public function partial($template, $data = []){
        $app = App::i();
        $app->applyHookBoundTo($this, 'controller(' . $this->id . ').partial(' . $template . ')', ['template' => &$template, 'data' => &$data]);

        $template = $this->id . '/' . $template;
        $app->view->partial = true;
        $app->render($template, $data);
    }

    /**
     * Sets the response content type to application/json and prints the $data encoded to json.
     *
     * @param mixed $data
     */
    public function json($data, $status = 200){
        $app = App::i();
        $app->contentType('application/json');
        $app->halt($status, json_encode($data));
    }

    /**
     * Sets the response content type to application/json and prints a json of ['error' => true, 'data' => $data]
     *
     * @param mixed $data
     *
     * @TODO Alterar o status padrão para 400. será necessário alterar os js para esperar este retorno.
     */
    public function errorJson($data, $status = 200){
        $app = App::i();

        $app->contentType('application/json');

        $app->halt($status, json_encode(['error' => true, 'data' => $data]));
    }

    /**
     * Creates a URL to the given action name and data
     * @param string $actionName
     * @param array $data
     * @return string the generated URL
     */
    public function createUrl($actionName, array $data = []){
        return App::i()->routesManager->createUrl($this->id, $actionName, $data);
    }

    /**
     * This method redirects the request to authentication page if the user is not logged in.
     *
     * Call this method at the beginning of actions that require authentication.
     */
    public function requireAuthentication(){
        $app = App::i();

        if($app->user->is('guest')){
            $app->applyHookBoundTo($this, "controller({$this->id}).requireAuthentication");

            $app->auth->requireAuthentication();
        }
    }
}