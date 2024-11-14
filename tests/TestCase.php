<?php
namespace MapasCulturais\Tests;

use Curl\Curl;
use MapasCulturais\App;
use MapasCulturais\Entities;

use MapasCulturais\Themes\Maranhao\Theme;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Connection;
use MapasCulturais\Repository;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use MapasCulturais\AuthProvider;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \App
     */
    protected $app;
    
    private $theme;

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
        parent::__construct($name, $data, $dataName);
        $this->app = \MapasCulturais\App::i();
        $this->factory = new TestFactory($this->app);
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
    }

    public function __set($name, $value) {
        if($name === 'user'){
            if(is_object($value) && $value instanceof Entities\User)
                $this->app->auth->authenticatedUser = $value;
            else
                $this->setUserId($value);
        }
    }

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

    public function setUserId($user_id = null){
        if(!is_null($user_id)) {
            if(is_array($this->app->config) && isset($this->app->config['userIds']) && is_array($this->app->config['userIds'])) {
                if(array_key_exists($user_id, $this->app->config['userIds'])) {
                    $this->app->auth->authenticatedUser = $this->getUser($user_id);
                } else {
                    $this->app->auth->authenticatedUser = $this->app->repo('User')->find($user_id);
                }
            } else {
                $this->app->auth->authenticatedUser = $this->app->repo('User')->find($user_id);
            }
        } else {
            $this->app->auth->logout();
        }
    }

    public function getUser($user_id = null, $index = 0){
        if($user_id instanceof Entities\User){
            return $user_id;
        } else if(is_array($this->app->config) && isset($this->app->config['userIds']) && is_array($this->app->config['userIds']) && array_key_exists($user_id, $this->app->config['userIds'])) {
            return $this->app->repo('User')->find($this->app->config['userIds'][$user_id][$index]);
        } else {
            return $this->app->repo('User')->find($user_id);
        }
    }

    protected function setUp(): void {
        parent::setUp();

        // Create mock repository
        $repository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create mock connection
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create mock mapping driver
        $mappingDriver = $this->getMockBuilder(MappingDriver::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create mock configuration
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup configuration to return mapping driver
        $configuration->method('getMetadataDriverImpl')
            ->willReturn($mappingDriver);

        // Create mock EntityManager
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup connection mock to return platform
        $platform = new \Doctrine\DBAL\Platforms\PostgreSQLPlatform();
        $connection->method('getDatabasePlatform')
            ->willReturn($platform);

        // Setup EntityManager mock to return connection, repository and configuration
        $em->method('getConnection')
            ->willReturn($connection);
        $em->method('getRepository')
            ->willReturn($repository);
        $em->method('getConfiguration')
            ->willReturn($configuration);

        // Create app instance with proper configuration
        $this->app = \MapasCulturais\App::i();
        // $this->app->setEntityManager($em);
        $config = require __DIR__ . '/config.php';
        $this->app->init($config);


        $assetManager = new \MapasCulturais\AssetManagers\FileSystem(['baseUrl' => 'baseUrl']);
        $this->theme = new Theme($assetManager);
        $this->app->em->beginTransaction();
    }

    protected function tearDown(): void {
        if ($this->app && $this->app->em) {
            $this->app->em->rollback();
            $this->app->em->clear();
        }
        parent::tearDown();
    }

    function resetTransactions(){
        if ($this->app && $this->app->em) {
            $this->app->em->rollback();
            $this->app->em->clear();
            $this->app->em->beginTransaction();
        }
    }

    public function request($method, $path, $options = array()) {
        // Ensure base URL is properly set
        $baseUrl = $this->app->config['base.url'] ?? 'http://localhost:8888';
        if(strpos($path, $baseUrl) !== 0){
            $url = $baseUrl . $path;
        } else {
            $url = $path;
        }

        $c = new Curl;
        $c->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $c->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $c->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $c->$method($url, $options);

        return $c;
    }

    public function get($path, $options = array()) {
        return $this->request('GET', $path, $options);
    }

    public function post($path, $options = array(), $postVars = array()) {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('POST', $path, $options);
    }

    public function patch($path, $options = array(), $postVars = array()) {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('PATCH', $path, $options);
    }

    public function put($path, $options = array(), $postVars = array()) {
        $options['slim.input'] = http_build_query($postVars);
        return $this->request('PUT', $path, $options);
    }

    public function delete($path, $options = array()) {
        return $this->request('DELETE', $path, $options);
    }

    public function head($path, $options = array()) {
        return $this->request('HEAD', $path, $options);
    }
}
