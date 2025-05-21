<?php
use Curl\Curl;
use MapasCulturais\App;
use MapasCulturais\Entities;


abstract class MapasCulturais_TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     *
     * @var \App
     */
    protected $app;

    protected $entities = [
        'Agent' => 'agents',
        'Space' => 'spaces',
        'Event' => 'events',
        'Project' => 'projects'
    ];

    /**
     * Test Factory
     *
     * @var MapasCulturais_TestFactory
     */
    protected $factory;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        $this->app = App::i();
        $this->factory = new MapasCulturais_TestFactory($this->app);

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;

        parent::__construct($name, $data, $dataName);
    }

    public function __set($name, $value) {
        if($name === 'user'){
            if(is_object($value) && $value instanceof Entities\User)
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
    function getNewEntity($class, $user = null, $owner = null){
        $app = $this->app;

        if(!is_null($user)){
            $_user = $app->user->is('guest') ? null : $app->user;
            $this->user = $user;
        }

        $classname = 'MapasCulturais\Entities\\' . $class;

        $_types = $app->getRegisteredEntityTypes($classname);
        $type = array_shift($_types);

        $entity = new $classname;
        $entity->name = "Test $class "  . uniqid();
        $entity->type = $type;
        $entity->shortDescription = 'A litle short description';

        if($owner){
            $entity->owner = $owner;
        } else if($app->user->is('guest') && $user && $classname::usesOwnerAgent()){
            $entity->owner = $user->profile;
        }

        if(!is_null($user)){
            $this->user = $_user;
        }
        return $entity;
    }

    
    function assertStatus($method, $status, $url, $message){
        $c = $this->$method($url);
        $this->assertEquals($status, $c->http_status_code, $message);
        return $c;
    }

    function assertGet200($url, $message){
        return $this->assertStatus('get', 200, $url, $message);
    }

    function assertGet401($url, $message){
        return $this->assertStatus('get', 401, $url, $message);
    }

    function assertGet403($url, $message){
        return $this->assertStatus('get', 403, $url, $message);
    }

    function assertGet404($url, $message){
        return $this->assertStatus('get', 404, $url, $message);
    }

    function assertGet503($url, $message){
        return $this->assertStatus('get', 503, $url, $message);
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
        }catch(\Exception $ex){
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
        if($user_id instanceof Entities\User){
            return $user_id;
        }else if(key_exists($user_id, $this->app->config['userIds'])){
            return $this->app->repo('User')->find($this->app->config['userIds'][$user_id][$index]);
        }else{
            return $this->app->repo('User')->find($user_id);
        }
    }

    // Initialize our own copy of the slim application
    protected function setUp(): void
    {
        parent::setUp();
        $app = App::i();
        $app->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        $app = App::i();
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