<?php
use Curl\Curl;

//
// Unit Test Bootstrap and Slim PHP Testing Framework
// =============================================================================
//
// SlimpPHP is a little hard to test - but with this harness we can load our
// routes into our own `$app` container for unit testing, and then `run()` and
// hand a reference to the `$app` to our tests so that they have access to the
// dependency injection container and such.
//
// * Author: [Craig Davis](craig@there4development.com)
// * Since: 10/2/2013
//
// -----------------------------------------------------------------------------

date_default_timezone_set('America/Sao_Paulo');



require_once __DIR__."/../src/protected/vendor/autoload.php";

define('BASE_PATH', realpath(__DIR__.'/../src') . '/');
define('PROTECTED_PATH', BASE_PATH . 'protected/');
define('APPLICATION_PATH', PROTECTED_PATH . 'application/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH',  THEMES_PATH . 'active/');
define('PLUGINS_PATH', APPLICATION_PATH.'/plugins/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');

 // Prepare a mock environment
\Slim\Environment::mock(array_merge(array(
    'REQUEST_METHOD' => 'get',
    'PATH_INFO'      => '/',
    'SERVER_NAME'    => 'local.dev',
)));


$config = include __DIR__ . '/../src/protected/application/conf/conf-test.php';


// create the App instance
$app = MapasCulturais\App::i()->init($config);
$app->register();

abstract class MapasCulturais_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \MapasCulturais\App
     */
    protected $app;

    protected $entities = [
        'Agent' => 'agents',
        'Space' => 'spaces',
        'Event' => 'events',
        'Project' => 'projects'
    ];

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        $this->app = MapasCulturais\App::i();
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;

        parent::__construct($name, $data, $dataName);
    }

    public function __set($name, $value) {
        if($name === 'user'){
            if(is_object($value) && $value instanceof \MapasCulturais\Entities\User)
                $this->app->auth->authenticatedUser = $value;
            else
                $this->setUserId ($value);
        }
    }

    /**
     * 
     * @param string $class
     * @param mixed $user
     * @return \MapasCulturais\Entity
     */
    function getNewEntity($class, $user = null){
        if(!is_null($user)){
            $_user = $this->app->user->is('guest') ? null : $this->app->user;
            $this->user = $user;
        }

        $app = MapasCulturais\App::i();
        $classname = 'MapasCulturais\Entities\\' . $class;

        $_types = $app->getRegisteredEntityTypes($classname);
        $type = array_shift($_types);

        $entity = new $classname;
        $entity->name = "Test $class "  . uniqid();
        $entity->type = $type;
        $entity->shortDescription = 'A litle short description';

        if(!is_null($user)){
            $this->user = $_user;
        }
        return $entity;
    }

    function assertPermissionDenied($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
            $callable();
        } catch (\Exception $ex) {
            $exception = $ex;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $exception, $msg);
    }


    function assertPermissionGranted($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
            $callable();
        } catch (\Exception $ex) {
            $exception = $ex;
            $msg .= '(message: "' . $ex->getMessage() . '")';
        }

        $this->assertEmpty($exception, $msg);
    }

    function assertAuthorizationRequestCreated($callable, $msg = ''){
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
            $callable();
        }catch (\MapasCulturais\Exceptions\WorkflowRequest $ex) {
            $exception = $ex;
        }

        if(is_object($exception) && substr(get_class($exception),0,9) === 'Doctrine\\'){
            throw $exception;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\WorkflowRequest', $exception, $msg);
    }

    public function setUserId($user_id = null){
        if(!is_null($user_id))
            $this->app->auth->authenticatedUser = $this->getUser($user_id);
        else
            $this->app->auth->logout();
    }

    /**
     *
     * @param type $user_id
     * @param type $index
     * @return MapasCulturais\Entities\User
     */
    public function getUser($user_id = null, $index = 0){
        if($user_id instanceof \MapasCulturais\Entities\User){
            return $user_id;
        }else if(key_exists($user_id, $this->app->config['userIds'])){
            return $this->app->repo('User')->find($this->app->config['userIds'][$user_id][$index]);
        }else{
            return $this->app->repo('User')->find($user_id);
        }
    }

    // Initialize our own copy of the slim application
    public function setup()
    {
        $app = MapasCulturais\App::i();
        $app->em->beginTransaction();
    }

    protected function tearDown(){
        $app = MapasCulturais\App::i();
        $app->em->rollback();

        parent::tearDown();

    }
    function resetTransactions(){
        $this->app->em->rollback();
        $this->app->em->clear();
        $this->app->em->beginTransaction();
    }

    // Abstract way to make a request to SlimPHP, this allows us to mock the
    // slim environment
    public function request($method, $path, $options = array()) {
        $baseUrl = 'http://localhost:8888';
        if(strpos($path, $baseUrl) !== 0){
            $url = $baseUrl . $path;
        }else{
            $url = $path;
        }

        $c = new Curl;
        $c->$method($url, $options);

        return $c;
    }

    public function get($path, $options = array())
    {
        return $this->request('GET', $path, $options);
    }

    public function post($path, $options = array(), $postVars = array())
    {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('POST', $path, $options);
    }

    public function patch($path, $options = array(), $postVars = array())
    {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('PATCH', $path, $options);
    }

    public function put($path, $options = array(), $postVars = array())
    {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('PUT', $path, $options);
    }

    public function delete($path, $options = array())
    {
        return $this->request('DELETE', $path, $options);
    }

    public function head($path, $options = array())
    {
        return $this->request('HEAD', $path, $options);
    }

}

/* End of file bootstrap.php */
