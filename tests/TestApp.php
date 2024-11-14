<?php
namespace MapasCulturais\Tests;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TestApp extends \MapasCulturais\App {
    private $mockEm;
    private static $instance = null;
    private static $instanceConfig = [];

    // protected function __construct(string $id) {
    //     // Load test config before parent constructor
    //     // Load test config
    //     $test_config = require __DIR__ . '/config.php';
    //     $config = array_merge($test_config, []);
    //     $this->config = $config;
    //     $this->_config = $config;
        
    //     parent::__construct($id);
    // }

    public static function i(string $id = 'web'): \MapasCulturais\App {
        if (!isset(self::$instance)) {
            self::$instance = new self($id);
            if (isset(self::$instanceConfig[$id])) {
                self::$instance->init(self::$instanceConfig[$id]);
            }
        }
        return self::$instance;
    }

    public static function setInstanceConfig(array $config, string $id = 'web') {
        self::$instanceConfig[$id] = $config;
    }

    public function setEntityManager($em) {
        $this->mockEm = $em;
    }

    protected function _initDoctrine() {
        $this->em = $this->mockEm;
    }

    protected function _initCache() {
        $this->cache = new \MapasCulturais\Cache(new ArrayAdapter());
        $this->mscache = new \MapasCulturais\Cache(new ArrayAdapter());
        $this->mscache->setNamespace('MS');
        $rcache_adapter = new ArrayAdapter();
        $this->rcache = new \MapasCulturais\Cache($rcache_adapter);
    }

    protected function _initStorage() {
        $this->storage = \MapasCulturais\Storage\FileSystem::i(['baseUrl' => '/files']);
    }

    protected function _initRouteManager() {
        $routes = $this->config['routes'] ?? [];
        // $this->routeManager = new \MapasCulturais\RoutesManager($routes);
    }

    protected function register() {
        if (!isset($this->_controllers)) {
            $this->_controllers = [];
        }

        // Register minimal components needed for tests
        if (!isset($this->_controllers['site'])) {
            $this->registerController('site', 'MapasCulturais\Controllers\Site');
        }
        if (!isset($this->_controllers['auth'])) {
            $this->registerController('auth', 'MapasCulturais\Controllers\Auth');
        }
    }

    protected function _initModules() {
        // Initialize only required modules for tests
        $this->modules = [];
    }

    public function init(array $config = []) {
        // Reset controllers before init
        $this->_controllers = [];
        $this->_register['controllers'] = [];
        $this->_register['controllers-by-class'] = [];
        $this->_register['controllers_default_actions'] = [];
        $this->_register['controllers_view_dirs'] = [];

        // Load test config
        $test_config = require __DIR__ . '/config.php';
        $config = array_merge($test_config, $config);
        
        parent::init($config);
    }

    public function __destruct() {
        self::$instance = null;
    }
}
