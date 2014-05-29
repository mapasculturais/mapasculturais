<?php
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

        if(key_exists($user_id, $this->app->config['userIds'])){
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
        $this->app->em->beginTransaction();
    }

    // Abstract way to make a request to SlimPHP, this allows us to mock the
    // slim environment
    public function request($method, $path, $options = array())
    {
        // Capture STDOUT
        ob_start();

        // Prepare a mock environment
        \Slim\Environment::mock(array_merge(array(
            'REQUEST_METHOD' => $method,
            'PATH_INFO'      => $path,
            'SERVER_NAME'    => 'local.dev',
        ), $options));


        // Establish some useful references to the slim app properties
        $this->request  = $this->app->request();
        $this->response = $this->app->response();

        // Execute our app
        $this->app->run();

        // Return the application output. Also available in `response->body()`
        return ob_get_clean();
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
