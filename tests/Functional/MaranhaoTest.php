<?php

namespace MapasCulturais\Tests;

use MapasCulturais\App;
use MapasCulturais\Themes\Maranhao\Theme;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Connection;
use MapasCulturais\Repository;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use MapasCulturais\AuthProvider;

class FakeAuthProvider extends \MapasCulturais\AuthProvider {
    protected $_user = null;
    
    protected $filename = '';

    protected function _init() {
        $tmp_dir = sys_get_temp_dir();
        $this->filename = isset($this->_config['filename']) ? 
                $this->_config['filename'] : $tmp_dir . '/mapasculturais-tests-authenticated-user.id';
    }

    public function _cleanUserSession() {
        if(file_exists($this->filename)){
            unlink($this->filename);
        }
        $this->_user = null;
    }

    public function _requireAuthentication() {
        $app = \MapasCulturais\App::i();
        $app->halt(401, \MapasCulturais\i::__('This action requires authentication.'));
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){ }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        return '';
    }


    public function _getAuthenticatedUser() {
        
        if(file_exists($this->filename)){
            $id = file_get_contents($this->filename);
            $this->_user = \MapasCulturais\App::i()->repo('User')->find($id);
        }
        
        return $this->_user;
    }

    public function setAuthenticatedUser(\MapasCulturais\Entities\User $user){
        file_put_contents($this->filename, $user->id);
        $this->_setAuthenticatedUser($user);
    }
    
    protected function _createUser($data) {
        ;
    }
}

/**
 * @covers \MapasCulturais\Theme
 */
class MaranhaoTest extends TestCase
{
    protected static $app;
    protected $theme;

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
