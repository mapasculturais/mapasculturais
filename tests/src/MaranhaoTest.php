<?php

namespace MapasCulturais\Tests\Theme;

require_once __DIR__.'/../../src/bootstrap.php';

$config_filename =  __DIR__.'/../../src/conf/config.php';

$config = require_once $config_filename;

require_once __DIR__.'/../../src/load-translation.php';

use MapasCulturais\App;
use MapasCulturais\Themes\Maranhao\Theme;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Connection;
use MapasCulturais\Repository;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class TestApp extends App {
    private $mockEm;

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
        // No storage initialization needed for theme tests
    }

    protected function _initRouteManager() {
        // No route manager initialization needed for theme tests
    }

    protected function register() {
        // No registration needed for theme tests
    }

    protected function _initModules() {
        // No modules initialization needed for theme tests
    }
}

class MaranhaoTest extends TestCase
{
    private $theme;
    private $app;

    protected function setUp(): void
    {
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

        // Setup basic configuration
        $config = [
            // Logger configuration
            'app.log.processors' => [],
            'app.log.handlers' => [],
            'app.log.channel' => 'mapas',
            'monolog.processors' => [],
            'monolog.handlers' => [],
            'monolog.defaultLevel' => 'DEBUG',
            'monolog.logsDir' => '/tmp/',
            
            // Other required configurations
            'themes.active' => 'MapasCulturais\Themes\BaseV2',
            'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(['baseUrl' => 'baseUrl']),
            
            // Routes configuration
            'routes' => [
                'default_controller_id' => 'site',
                'default_action' => 'index',
                'shortcuts' => [],
                'controllers' => []
            ],

            // Auth configuration
            'auth.provider' => '\MapasCulturais\AuthProviders\Fake',
            'auth.config' => [],

            // Base URL configuration
            'base.url' => 'http://localhost/',
            'base.assetUrl' => 'http://localhost/assets/'
        ];

        // Create app instance
        $this->app = TestApp::i();
        $this->app->setEntityManager($em);
        $this->app->init($config);
        
        $assetManager = new \MapasCulturais\AssetManagers\FileSystem(['baseUrl' => 'baseUrl']);
        $this->theme = new Theme($assetManager);
    }

    public function testThemeInitialization()
    {
        // Test if theme is properly initialized
        $this->assertInstanceOf(Theme::class, $this->theme);
        
        // Test if theme inherits from BaseV2
        $this->assertInstanceOf(\MapasCulturais\Themes\BaseV2\Theme::class, $this->theme);
    }

    public function testBodyClasses()
    {
        $this->theme->_init();
        
        // Test if the theme's body class is added
        $this->assertContains('maranhao-theme', $this->theme->bodyClasses);
        
        // Test if the parent theme's body class is maintained
        $this->assertContains('base-v2', $this->theme->bodyClasses);
    }
}
