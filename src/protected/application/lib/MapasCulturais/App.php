<?php
namespace MapasCulturais;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use Acelaya\Doctrine\Type\PhpEnumType;
use DateTime;
use Exception;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\PermissionCachePending;
use MapasCulturais\Entities\User;

/**
 * MapasCulturais Application class.
 *
 *
 * @property-read \Doctrine\ORM\EntityManager $em The Doctrine Entity Manager
 * @property-read \Slim\Log $log Slim Logger
 * @property-read \Doctrine\Common\Cache\CacheProvider $cache Cache Provider
 * @property-read \Doctrine\Common\Cache\CacheProvider $mscache Multisite Cache Provider
 * @property-read \Doctrine\Common\Cache\ArrayCache $rcache Runtime Cache Provider
 * @property-read \MapasCulturais\AuthProvider $auth The Authentication Manager Component.
 * @property-read \MapasCulturais\Theme $view The MapasCulturais View object
 * @property-read \MapasCulturais\Storage\FileSystem $storage File Storage Component.
 * @property-read \MapasCulturais\Entities\User $user The Logged in user.
 * @property-read String $opportunityRegistrationAgentRelationGroupName Opportunity Registration Agent Relation Group Name
 *
 * From Slim Class Definition
 * @property-read array[\Slim] $apps = []
 * @property-read string $name The Slim Application name
 * @property-read array $environment
 * @property-read \Slim\Http\Request $request
 * @property-read \Slim\Http\Response $response
 * @property-read \Slim\Router $router
 * @property-read array $settings
 * @property-read string $mode
 * @property-read array $middleware
 * @property-read mixed $error Callable to be invoked if application error
 * @property-read mixed $notFound Callable to be invoked if no matching routes are found
 *
 * @property-read string $siteName
 * @property-read string $siteDescription
 *
 * @property-read array $config
 *
 * @property-read \MapasCulturais\Module[] $modules active modules
 * @property-read \MapasCulturais\Plugin[] $plugins active plugins
 *
 * @method \MapasCulturais\App i() Returns the application object
 */
class App extends \Slim\Slim{
    use \MapasCulturais\Traits\MagicGetter,
        \MapasCulturais\Traits\MagicSetter,
        \MapasCulturais\Traits\Singleton;

    /**
     * Is the App initiated?
     * @var boolean
     */
    protected $_initiated = false;

    /**
     * Doctrine Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em = null;

    /**
     * Cache Component
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $_cache = null;

    /**
     * Cache Component
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $_mscache = null;

    /**
     * Runtime Cache
     * @var \Doctrine\Common\Cache\ArrayCache
     */
    protected $_rcache = null;

    /**
     * The MapasCulturais Auth Manager.
     * @var \MapasCulturais\Auth
     */
    protected $_auth = null;

    /**
     * The Route Manager.
     * @var \MapasCulturais\RoutesManager
     */
    protected $_routesManager = null;

    /**
     * File Storage Component
     * @var \MapasCulturais\Storage
     */
    protected $_storage = null;


    protected $_debugbar = null;

    /**
     * App Configuration.
     * @var array
     */
    public $_config = [];

    /**
     * The Application Registry.
     *
     * Here is stored the registered controllers, entity types, entity type groups, entity metadata definitions, file groups definitions and taxonomy definitions.
     *
     * @var type
     */
    protected $_register = [
            'controllers' => [],
            'auth_providers' => [],
            'controllers-by-class' => [],
            'controllers_default_actions' => [],
            'controllers_view_dirs' => [],
            'entity_type_groups' => [],
            'entity_types' => [],
            'entity_metadata_definitions' => [],
            'file_groups' => [],
            'metalist_groups' => [],
            'taxonomies' => [
                'by-id' => [],
                'by-slug' => [],
                'by-entity' => [],
            ],
            'api_outputs' => [],
            'image_transformations' => [],
            'registration_agent_relations' => [],
            'registration_fields' => [],
            'evaluation_method' => [],
            'roles' => [],
            'chat_thread_types' => [],
            'job_types' => [],
        ];

    protected $_registerLocked = true;

    protected $_hooks = [];
    protected $_excludeHooks = [];


    protected $_disableAccessControlCount = 0;
    protected $_workflowEnabled = true;

    protected $_plugins = [];

    protected $_modules = [];

    protected $_subsite = null;

    /**
     * Initializes the application instance.
     *
     * This method
     * starts the session,
     * call Slim constructor,
     * set the custom log writer (if is defined in config),
     * bootstraps the Doctrine,
     * bootstraps the Auth Manager,
     * creates the cache and rcache components,
     * sets the file storage,
     * adds midlewares,
     * instantiates the Route Manager and
     * includes the theme.php file of the active theme if the file exists.
     *
     *
     * If the application was previously initiated, this method returns the application in the first line.
     *
     * @return \MapasCulturais\App
     */
    public function init($config = []){
        if($this->_initiated)
            return $this;

        $this->_initiated = true;

        if(empty($config['base.url'])){
            $config['base.url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . 
                                  (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/';
        }

        if(empty($config['base.assetUrl'])){
            $config['base.assetUrl'] = $config['base.url'] . 'assets/';
        }

        if($config['slim.debug'])
            error_reporting(E_ALL ^ E_STRICT);

        if ($config['app.mode'] == APPMODE_PRODUCTION)
            session_save_path(SESSIONS_SAVE_PATH);
        
        session_start();

        session_regenerate_id();


        if($config['app.offline']){
            $bypass_callable = $config['app.offlineBypassFunction'];
            
            if (php_sapi_name()!=="cli" && (!is_callable($bypass_callable) || !$bypass_callable())) {
                http_response_code(307);
                header('Location: ' . $config['app.offlineUrl']);
                die;
            }
        }

        // =============== CACHE =============== //
        if(key_exists('app.cache', $config) && is_object($config['app.cache'])  && is_subclass_of($config['app.cache'], '\Doctrine\Common\Cache\CacheProvider')){
            $this->_cache = $config['app.cache'];
            $this->_mscache = clone $this->_cache;

        }else{
            $this->_cache = new \Doctrine\Common\Cache\ArrayCache ();
            $this->_mscache = new \Doctrine\Common\Cache\ArrayCache ();
        }

        $this->_rcache = new \Doctrine\Common\Cache\ArrayCache ();


        $this->_mscache->setNamespace(__DIR__);

        // list of modules
        $available_modules = [];
        if($handle = opendir(MODULES_PATH)){
            while (false !== ($file = readdir($handle))) {
                $dir = MODULES_PATH . $file . '/';
                if ($file != "." && $file != ".." && is_dir($dir) && file_exists($dir."/Module.php")) {
                    $available_modules[] = $file;
                    $config['namespaces'][$file] = $dir;
                }
            }
            closedir($handle);
        }

        // list of themes
        foreach (scandir(THEMES_PATH) as $ff) {
            if ($ff != '.' && $ff != '..' ) {
                $theme_folder = THEMES_PATH . $ff;
                if (is_dir($theme_folder) && file_exists($theme_folder . '/Theme.php')) {
                    $content = file_get_contents($theme_folder . '/Theme.php');
                    if(preg_match('#namespace +([a-z0-9\\\]+) *;#i', $content, $matches)) {
                        $namespace = $matches[1];
                        if ( !array_key_exists($namespace, $config['namespaces']) )
                            $config['namespaces'][$namespace] = $theme_folder;
                    }
                }
            }
        }

        spl_autoload_register(function($class) use ($config){
            $cache_id = "AUTOLOAD_CLASS:$class";
            if($config['app.useRegisteredAutoloadCache'] && $this->_mscache->contains($cache_id)){
                $path = $this->_mscache->fetch($cache_id);
                require_once $path;
                return true;
            }

            $namespaces = $config['namespaces'];

            $namespaces['MapasCulturais\\DoctrineProxies'] = DOCTRINE_PROXIES_PATH;

            $subfolders = [
                'Controllers',
                'Entities',
                'Repositories'
            ];

            foreach($config['plugins'] as $plugin){
                $namespace = $plugin['namespace'];
                $dir = isset($plugin['path']) ? $plugin['path'] : PLUGINS_PATH . $namespace;
                if(!isset($namespaces[$namespace])){
                    $namespaces[$namespace] = $dir;
                }

                foreach($subfolders as $subfolder) {
                    if(!isset($namespaces[$namespace . '\\' . $subfolder])){
                        $namespaces[$namespace . '\\' . $subfolder] = $dir . '/' . $subfolder;
                    }   
                }
            }

            foreach($namespaces as $namespace => $base_dir){
                if(strpos($class, $namespace) === 0){
                    $path = str_replace('\\', '/', str_replace($namespace, $base_dir, $class) . '.php' );

                    if(\file_exists($path)){
                        require_once $path;
                        if($config['app.useRegisteredAutoloadCache'])
                            $this->_mscache->save($cache_id, $path, $config['app.registeredAutoloadCache.lifetime']);
                        return true;
                    }
                }
            }
        });

        // extende a config with theme config files
        
        $theme_class = "\\" . $config['themes.active'] . '\Theme';
        $theme_path = $theme_class::getThemeFolder() . '/';

        if (file_exists($theme_path . 'conf-base.php')) {
            $theme_config = require $theme_path . 'conf-base.php';
            $config = array_merge($config, $theme_config);
        }

        if (file_exists($theme_path . 'config.php')) {
            $theme_config = require $theme_path . 'config.php';
            $config = array_merge($config, $theme_config);
        }


        $config['app.mode'] = key_exists('app.mode', $config) ? $config['app.mode'] : 'production';

        $this->_config = $config;

        $this->_config['path.layouts'] = APPLICATION_PATH.'themes/active/layouts/';
        $this->_config['path.templates'] = APPLICATION_PATH.'themes/active/views/';
        $this->_config['path.metadata_inputs'] = APPLICATION_PATH.'themes/active/metadata-inputs/';

        if(!key_exists('app.sanitize_filename_function', $this->_config))
                $this->_config['app.sanitize_filename_function'] = null;

        // ========== BOOTSTRAPING DOCTRINE ========== //
        // annotation driver
        $doctrine_config = Setup::createConfiguration($config['doctrine.isDev']);

        $driver = new AnnotationDriver(new AnnotationReader());

        $driver->addPaths([__DIR__ . '/Entities/']);

        // tells the doctrine to ignore hook annotation.
        AnnotationReader::addGlobalIgnoredName('hook');

        // driver must be pdo_pgsql
        $config['doctrine.database']['driver'] = 'pdo_pgsql';

        // registering noop annotation autoloader - allow all annotations by default
        AnnotationRegistry::registerLoader('class_exists');
        $doctrine_config->setMetadataDriverImpl($driver);

        $doctrine_config->setProxyDir(DOCTRINE_PROXIES_PATH);
        $doctrine_config->setProxyNamespace('MapasCulturais\DoctrineProxies');

        /** DOCTRINE2 SPATIAL */

        $doctrine_config->addCustomStringFunction('st_asbinary', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STAsBinary');
        $doctrine_config->addCustomStringFunction('st_astext', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STAsText');
        $doctrine_config->addCustomNumericFunction('st_area', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STArea');
        $doctrine_config->addCustomStringFunction('st_centroid', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STCentroid');
        $doctrine_config->addCustomStringFunction('st_closestpoint', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STClosestPoint');
        $doctrine_config->addCustomNumericFunction('st_contains', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STContains');
        $doctrine_config->addCustomNumericFunction('st_containsproperly', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STContainsProperly');
        $doctrine_config->addCustomNumericFunction('st_covers', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STCovers');
        $doctrine_config->addCustomNumericFunction('st_coveredby', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STCoveredBy');
        $doctrine_config->addCustomNumericFunction('st_crosses', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STCrosses');
        $doctrine_config->addCustomNumericFunction('st_disjoint', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STDisjoint');
        $doctrine_config->addCustomNumericFunction('st_distance', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STDistance');
        $doctrine_config->addCustomStringFunction('st_envelope', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STEnvelope');
        $doctrine_config->addCustomStringFunction('st_geomfromtext', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STGeomFromText');
        $doctrine_config->addCustomNumericFunction('st_length', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STLength');
        $doctrine_config->addCustomNumericFunction('st_linecrossingdirection', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STLineCrossingDirection');
        $doctrine_config->addCustomStringFunction('st_startpoint', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STStartPoint');
        $doctrine_config->addCustomStringFunction('st_summary', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STSummary');


        $doctrine_config->addCustomStringFunction('string_agg', 'MapasCulturais\DoctrineMappings\Functions\StringAgg');
        $doctrine_config->addCustomStringFunction('unaccent', 'MapasCulturais\DoctrineMappings\Functions\Unaccent');
        $doctrine_config->addCustomStringFunction('recurring_event_occurrence_for', 'MapasCulturais\DoctrineMappings\Functions\RecurringEventOcurrenceFor');

        $doctrine_config->addCustomNumericFunction('st_dwithin', 'MapasCulturais\DoctrineMappings\Functions\STDWithin');
        $doctrine_config->addCustomNumericFunction('st_makepoint', 'MapasCulturais\DoctrineMappings\Functions\STMakePoint');

        $doctrine_config->setMetadataCacheImpl($this->_mscache);
        $doctrine_config->setQueryCacheImpl($this->_mscache);
        $doctrine_config->setResultCacheImpl($this->_mscache);


        $doctrine_config->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
        
        // obtaining the entity manager
        $this->_em = EntityManager::create($config['doctrine.database'], $doctrine_config);

        \MapasCulturais\DoctrineMappings\Types\Frequency::register();

        \MapasCulturais\DoctrineMappings\Types\Point::register();
        \MapasCulturais\DoctrineMappings\Types\Geography::register();
        \MapasCulturais\DoctrineMappings\Types\Geometry::register();


        PhpEnumType::registerEnumTypes([
            DoctrineEnumTypes\ObjectType::getTypeName() => DoctrineEnumTypes\ObjectType::class,
            DoctrineEnumTypes\PermissionAction::getTypeName() => DoctrineEnumTypes\PermissionAction::class
        ]);

        $platform = $this->_em->getConnection()->getDatabasePlatform();

        $platform->registerDoctrineTypeMapping('_text', 'text');
        $platform->registerDoctrineTypeMapping('point', 'point');
        $platform->registerDoctrineTypeMapping('geography', 'geography');
        $platform->registerDoctrineTypeMapping('geometry', 'geometry');
        $platform->registerDoctrineTypeMapping('object_type', 'object_type');
        $platform->registerDoctrineTypeMapping('permission_action', 'permission_action');
        

        // QUERY LOGGER
        if(@$config['app.log.query']){
            if (isset($config['app.queryLogger']) && is_object($config['app.queryLogger'])) {
                $query_logger = $config['app.queryLogger'];
            } elseif (isset($config['app.queryLogger']) && is_string($config['app.queryLogger']) && class_exists($config['app.queryLogger'])) {
                $query_logger_class = $config['app.queryLogger'];
                $query_logger = new $query_logger_class;
            } else {
                $query_logger = new Loggers\DoctrineSQL\SlimLog();
            }
            $doctrine_config->setSQLLogger($query_logger);
        }

        // ===================================== //



        $domain = @$_SERVER['HTTP_HOST'];

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        // para permitir o db update rodar para criar a tabela do subsite
        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }
        try{
            $this->_subsite = $this->repo('Subsite')->findOneBy(['url' => $domain, 'status' => 1]);

            if(!$this->_subsite){
                $this->_subsite = $this->repo('Subsite')->findOneBy(['aliasUrl' => $domain, 'status' => 1]);
            }
        } catch ( \Exception $e) { }


        if($this->_subsite){
            $this->_cache->setNamespace($config['app.cache.namespace'] . ':' . $this->_subsite->id);

            $theme_class = $this->_subsite->namespace . "\Theme";
            $theme_instance = new $theme_class($config['themes.assetManager'], $this->_subsite);
        } else {
            $this->_cache->setNamespace($config['app.cache.namespace']);

            $theme_class = $config['themes.active'] . '\Theme';
            $theme_instance = new $theme_class($config['themes.assetManager']);
        }


        parent::__construct([
            'log.level' => $config['slim.log.level'],
            'log.enabled' => $config['slim.log.enabled'],
            'debug' => $config['slim.debug'],
            'templates.path' => $this->_config['path.templates'],
            'view' => $theme_instance,
            'mode' => $this->_config['app.mode']
        ]);

        foreach($config['plugins'] as $slug => $plugin){
            $_namespace = $plugin['namespace'];
            $_class = isset($plugin['class']) ? $plugin['class'] : 'Plugin';
            $plugin_class_name = "$_namespace\\$_class";

            if(class_exists($plugin_class_name)){
                $plugin_config = isset($plugin['config']) && is_array($plugin['config']) ? $plugin['config'] : [];

                $slug = is_numeric($slug) ? $_namespace : $slug;

                $this->_plugins[$slug] = new $plugin_class_name($plugin_config);
            }
        }

        $this->applyHookBoundTo($this, 'app.init:before');

        $config = $this->_config;

        $this->applyHookBoundTo($this, 'app.modules.init:before', [&$available_modules]);
        foreach ($available_modules as $module){
            $module_class_name = "$module\Module";
            $module_config = isset($config["module.$module"]) ? 
            $config["module.$module"] : [];
            
            $this->applyHookBoundTo($this, "app.module({$module}).init:before", [&$module_config]);
            $this->_modules[$module] = new $module_class_name($module_config);
            $this->applyHookBoundTo($this, "app.module({$module}).init:after");
        }
        $this->applyHookBoundTo($this, 'app.modules.init:after');


        // ===================================== //

        // custom log writer
        if(isset($config['slim.log.writer']) && is_object($config['slim.log.writer']) && method_exists($config['slim.log.writer'], 'write')){
            $log = $this->getLog();
            $log->setWriter($config['slim.log.writer']);
        }


        // creates runtime cache component
        $this->_rcache = new \Doctrine\Common\Cache\ArrayCache ();

        // ===================================== //




        // ============= STORAGE =============== //
        if(key_exists('storage.driver', $config) && class_exists($config['storage.driver']) && is_subclass_of($config['storage.driver'], '\MapasCulturais\Storage')){
            $storage_class = $config['storage.driver'];
            $this->_storage = key_exists('storage.config', $config) ? $storage_class::i($config['storage.config']) : $storage_class::i();
        }else{
            $this->_storage = \MapasCulturais\Storage\FileSystem::i();
        }
        // ===================================== //



        // add middlewares
        if(is_array($config['slim.middlewares']))
            foreach($config['slim.middlewares'] as $middleware)
                $this->add($middleware);

        // instantiate the route manager
        $this->_routesManager = new RoutesManager(key_exists('routes', $config) ? $config['routes'] : []);

        $this->applyHookBoundTo($this, 'mapasculturais.init');

        $this->register();


        // =============== AUTH ============== //

        if($token = $this->request()->headers->get('authorization')){
            $this->_auth = new AuthProviders\JWT(['token' => $token]);
        }else{
            $auth_class_name = strpos($config['auth.provider'], '\\') !== false ? $config['auth.provider'] : 'MapasCulturais\AuthProviders\\' . $config['auth.provider'];
            $this->_auth = new $auth_class_name($config['auth.config']);
            $this->_auth->setCookies();
        }

        // initialize theme
        $this->view->init();

        // ===================================== //
        
        // run plugins
        if(isset($config['plugins.enabled']) && is_array($config['plugins.enabled'])){
            foreach($config['plugins.enabled'] as $plugin){
                if(file_exists(PLUGINS_PATH.$plugin.'.php')){
                    include PLUGINS_PATH.$plugin.'.php';
                }
            }
        }
        // ===================================== //


        if($this->_subsite){
            // apply subsite filters
            $this->_subsite->applyApiFilters();

            $this->_subsite->applyConfigurations($this->_config);
        }

        if(defined('DB_UPDATES_FILE') && file_exists(DB_UPDATES_FILE))
            $this->_dbUpdates();

        $this->applyHookBoundTo($this, 'app.init:after');
        return $this;
    }

    public function run() {
        $this->applyHookBoundTo($this, 'mapasculturais.run:before');
        parent::run();
        $this->persistPCachePendingQueue();
        $this->applyHookBoundTo($this, 'mapasculturais.run:after');
    }

    public function getVersion(){
        $version = trim($this->getVersionFile());
        return sprintf('v%s', $version);
    }

    private function getVersionFile() {
        $version = \MapasCulturais\i::__("versão indefinida");
        $path = getcwd() . "/../version.txt";
        if (file_exists($path) && $versionFile = fopen($path, "r")) {
            $version = fgets($versionFile);
            fclose($versionFile);
        }
        return $version;
    }

    /**
     * http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string/13733588#13733588
     */
    protected static function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1)
            return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    static function getToken($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[self::crypto_rand_secure(0, $max)];
        }
        
        return $token;
    }

    function isEnabled($entity){
        return $this->_config['app.enabled.' . $entity];
    }

    function enableAccessControl(){
        if ($this->_disableAccessControlCount > 0) {
            $this->_disableAccessControlCount--;
        }
    }

    function disableAccessControl(){
        $this->_disableAccessControlCount++;
    }

    function isAccessControlEnabled(){
        return $this->_disableAccessControlCount == 0;
    }

    function enableWorkflow(){
        $this->_workflowEnabled = true;
    }

    function disableWorkflow(){
        $this->_workflowEnabled = false;
    }

    function isWorkflowEnabled(){
        return $this->_workflowEnabled;
    }

    function _dbUpdates(){
        $this->disableAccessControl();

        $executed_updates = [];

        foreach($this->repo('DbUpdate')->findAll() as $up)
            $executed_updates[] = $up->name;

        $updates = include DB_UPDATES_FILE;

        foreach($this->view->path as $path){
            $db_update_file = $path . 'db-updates.php';
            if(file_exists($db_update_file)){
                $updates += include $db_update_file;
            }
        }

        $new_updates = false;

        foreach($updates as $name => $function){
            if(!in_array($name, $executed_updates)){
                $new_updates = true;
                echo "\nApplying db update \"$name\":";
                echo "\n-------------------------------------------------------------------------------------------------------\n";
                try{
                    if($function() !== false){
                        $up = new Entities\DbUpdate();
                        $up->name = $name;
                        $up->save();
                    }
                }catch(\Exception $e){
                    echo "\nERROR " . $e . "\n";
                }
                echo "\n-------------------------------------------------------------------------------------------------------\n\n";
            }
        }

        if($new_updates){
            $this->_em->flush();
            $this->cache->deleteAll();
        }

        $this->enableAccessControl();
    }

    private $_registered = false;

    public function register(){
        //        phpdbg_break_next();

        if($this->_registered)
            return;

        $this->_registered = true;

        $this->applyHookBoundTo($this, 'app.register:before');

        // get types and metadata configurations
        if ($theme_space_types = $this->view->resolveFilename('','space-types.php')) {
            $space_types = include $theme_space_types;
        } else {
            $space_types = include APPLICATION_PATH.'/conf/space-types.php';
        }
        $space_meta = key_exists('metadata', $space_types) && is_array($space_types['metadata']) ? $space_types['metadata'] : [];

        if ($theme_agent_types = $this->view->resolveFilename('','agent-types.php')) {
            $agent_types = include $theme_agent_types;
        } else {
            $agent_types = include APPLICATION_PATH.'/conf/agent-types.php';
        }
        $agents_meta = key_exists('metadata', $agent_types) && is_array($agent_types['metadata']) ? $agent_types['metadata'] : [];

        if ($theme_event_types = $this->view->resolveFilename('','event-types.php')) {
            $event_types = include $theme_event_types;
        } else {
            $event_types = include APPLICATION_PATH.'/conf/event-types.php';
        }
        $event_meta = key_exists('metadata', $event_types) && is_array($event_types['metadata']) ? $event_types['metadata'] : [];

        if ($theme_project_types = $this->view->resolveFilename('','project-types.php')) {
            $project_types = include $theme_project_types;
        } else {
            $project_types = include APPLICATION_PATH.'/conf/project-types.php';
        }
        $projects_meta = key_exists('metadata', $project_types) && is_array($project_types['metadata']) ? $project_types['metadata'] : [];

        if ($theme_opportunity_types = $this->view->resolveFilename('','opportunity-types.php')) {
            $opportunity_types = include $theme_opportunity_types;
        } else {
            $opportunity_types = include APPLICATION_PATH.'/conf/opportunity-types.php';
        }
        $opportunities_meta = key_exists('metadata', $opportunity_types) && is_array($opportunity_types['metadata']) ? $opportunity_types['metadata'] : [];

        // get types and metadata configurations
        if ($theme_subsite_types = $this->view->resolveFilename('','subsite-types.php')) {
            $subsite_types = include $theme_subsite_types;
        } else {
            $subsite_types = include APPLICATION_PATH.'/conf/subsite-types.php';
        }
        $subsite_meta = key_exists('metadata', $subsite_types) && is_array($subsite_types['metadata']) ? $subsite_types['metadata'] : [];

        if ($theme_seal_types = $this->view->resolveFilename('','seal-types.php')) {
            $seal_types = include $theme_seal_types;
        } else {
            $seal_types = include APPLICATION_PATH.'/conf/seal-types.php';
        }
        $seals_meta = key_exists('metadata', $seal_types) && is_array($seal_types['metadata']) ? $seal_types['metadata'] : [];

        if ($theme_notification_types = $this->view->resolveFilename('','notification-types.php')) {
            $notification_types = include $theme_notification_types;
        } else {
            $notification_types = include APPLICATION_PATH.'/conf/notification-types.php';
        }
        $notification_meta = key_exists('metadata', $notification_types) && is_array($notification_types['metadata']) ? $notification_types['metadata'] : [];

        // register auth providers
        // @TODO veridicar se isto está sendo usado, se não remover
        $this->registerAuthProvider('OpenID');
        $this->registerAuthProvider('logincidadao');


        // register controllers

        $this->registerController('site',    'MapasCulturais\Controllers\Site');
        $this->registerController('auth',    'MapasCulturais\Controllers\Auth');
        $this->registerController('panel',   'MapasCulturais\Controllers\Panel');
        $this->registerController('geoDivision',    'MapasCulturais\Controllers\GeoDivision');

        $this->registerController('user',   'MapasCulturais\Controllers\User');

        $this->registerController('event',          'MapasCulturais\Controllers\Event');
        $this->registerController('agent',          'MapasCulturais\Controllers\Agent');
        $this->registerController('seal',           'MapasCulturais\Controllers\Seal');
        $this->registerController('space',          'MapasCulturais\Controllers\Space');
        $this->registerController('project',        'MapasCulturais\Controllers\Project');

        $this->registerController('opportunity',    'MapasCulturais\Controllers\Opportunity');
        $this->registerController('evaluationMethodConfiguration', 'MapasCulturais\Controllers\EvaluationMethodConfiguration');

        $this->registerController('subsite',        'MapasCulturais\Controllers\Subsite');


        $this->registerController('app',   'MapasCulturais\Controllers\UserApp');

        $this->registerController('registration',                   'MapasCulturais\Controllers\Registration');
        $this->registerController('registrationFileConfiguration',  'MapasCulturais\Controllers\RegistrationFileConfiguration');
        $this->registerController('registrationFieldConfiguration', 'MapasCulturais\Controllers\RegistrationFieldConfiguration');

        $this->registerController('term',           'MapasCulturais\Controllers\Term');
        $this->registerController('file',           'MapasCulturais\Controllers\File');
        $this->registerController('metalist',       'MapasCulturais\Controllers\MetaList');
        $this->registerController('eventOccurrence','MapasCulturais\Controllers\EventOccurrence');

        $this->registerController('eventAttendance','MapasCulturais\Controllers\EventAttendance');

        //workflow controllers
        $this->registerController('notification', 'MapasCulturais\Controllers\Notification');

        // history controller
        $this->registerController('entityRevision',    'MapasCulturais\Controllers\EntityRevision');
        $this->registerController('permissionCache',   'MapasCulturais\Controllers\PermissionCache');

        // chat controllers
        $this->registerController('chatThread', 'MapasCulturais\Controllers\ChatThread');
        $this->registerController('chatMessage', 'MapasCulturais\Controllers\ChatMessage');

        $this->registerApiOutput('MapasCulturais\ApiOutputs\Json');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\Html');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\Excel');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\Dump');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\TextTable');

        $roles = [
            'saasSuperAdmin' => (object) [
                'name' => i::__('Super Administrador do SaaS'),
                'plural' => i::__('Super Administradores do SaaS'),
                'another_roles' => ['saasAdmin', 'superAdmin', 'admin'],
                'subsite' => false,
                'can_user_manage_role' => function(UserInterface $user, $subsite_id) {
                    return $user->is('saasSuperAdmin');
                }
            ],
            'saasAdmin' => (object) [
                'name' => i::__('Administrador do SaaS'),
                'plural' => i::__('Administradores do SaaS'),
                'another_roles' => ['superAdmin', 'admin'],
                'subsite' => false,
                'can_user_manage_role' => function(UserInterface $user, $subsite_id) {
                    return $user->is('saasSuperAdmin');
                }
            ],
            'superAdmin' => (object) [
                'name' => i::__('Super Administrador'),
                'plural' => i::__('Super Administradores'),
                'another_roles' => ['admin'],
                'subsite' => true,
                'can_user_manage_role' => function(UserInterface $user, $subsite_id) {
                    return $user->is('superAdmin', $subsite_id);
                }
            ],
            'admin' => (object) [
                'name' => i::__('Administrador'),
                'plural' => i::__('Administradores'),
                'another_roles' => [],
                'subsite' => true,
                'can_user_manage_role' => function(UserInterface $user, $subsite_id) {
                    return $user->is('superAdmin', $subsite_id);
                }
            ],
        ];

        foreach ($roles as $role => $cfg) {
            $role_definition = new Definitions\Role($role, $cfg->name, $cfg->plural, $cfg->subsite, $cfg->can_user_manage_role, $cfg->another_roles);

            $this->registerRole($role_definition);
        }

        /**
         * @todo melhores mensagens de erro
         */

        // all file groups
        $file_groups = [
            'downloads' => new Definitions\FileGroup('downloads'),
            'avatar' => new Definitions\FileGroup('avatar', ['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), true),
            'header' => new Definitions\FileGroup('header', ['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), true),
            'gallery' => new Definitions\FileGroup('gallery', ['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), false),
            'registrationFileConfiguration' => new Definitions\FileGroup('registrationFileTemplate', ['^application/.*'], \MapasCulturais\i::__('O arquivo enviado não é um documento válido.'), true),
            'rules' => new Definitions\FileGroup('rules', ['^application/.*'], \MapasCulturais\i::__('O arquivo enviado não é um documento válido.'), true),
            'logo'  => new Definitions\FileGroup('logo',['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), true),
            'background' => new Definitions\FileGroup('background',['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'),true),
            'share' => new Definitions\FileGroup('share',['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'),true),
            'institute'  => new Definitions\FileGroup('institute',['^image/(jpeg|png)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), true),
            'favicon'  => new Definitions\FileGroup('favicon',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'), true),
            'zipArchive'  => new Definitions\FileGroup('zipArchive',['^application/zip$'], \MapasCulturais\i::__('O arquivo não é um ZIP.'), true, null, true),
        ];

        // register file groups
        $this->registerFileGroup('agent', $file_groups['downloads']);
        $this->registerFileGroup('agent', $file_groups['header']);
        $this->registerFileGroup('agent', $file_groups['avatar']);
        $this->registerFileGroup('agent', $file_groups['gallery']);

        $this->registerFileGroup('space', $file_groups['downloads']);
        $this->registerFileGroup('space', $file_groups['header']);
        $this->registerFileGroup('space', $file_groups['avatar']);
        $this->registerFileGroup('space', $file_groups['gallery']);

        $this->registerFileGroup('event', $file_groups['header']);
        $this->registerFileGroup('event', $file_groups['avatar']);
        $this->registerFileGroup('event', $file_groups['downloads']);
        $this->registerFileGroup('event', $file_groups['gallery']);

        $this->registerFileGroup('project', $file_groups['header']);
        $this->registerFileGroup('project', $file_groups['avatar']);
        $this->registerFileGroup('project', $file_groups['downloads']);
        $this->registerFileGroup('project', $file_groups['gallery']);

        $this->registerFileGroup('opportunity', $file_groups['header']);
        $this->registerFileGroup('opportunity', $file_groups['avatar']);
        $this->registerFileGroup('opportunity', $file_groups['downloads']);
        $this->registerFileGroup('opportunity', $file_groups['gallery']);
        $this->registerFileGroup('opportunity', $file_groups['rules']);

        $this->registerFileGroup('seal', $file_groups['downloads']);
        $this->registerFileGroup('seal', $file_groups['header']);
        $this->registerFileGroup('seal', $file_groups['avatar']);
        $this->registerFileGroup('seal', $file_groups['gallery']);

        $this->registerFileGroup('registrationFileConfiguration', $file_groups['registrationFileConfiguration']);
        $this->registerFileGroup('registration', $file_groups['zipArchive']);

        $this->registerFileGroup('subsite',$file_groups['header']);
        $this->registerFileGroup('subsite',$file_groups['avatar']);
        $this->registerFileGroup('subsite',$file_groups['logo']);
        $this->registerFileGroup('subsite',$file_groups['background']);
        $this->registerFileGroup('subsite',$file_groups['share']);
        $this->registerFileGroup('subsite',$file_groups['institute']);
        $this->registerFileGroup('subsite',$file_groups['favicon']);
        $this->registerFileGroup('subsite',$file_groups['downloads']);

        if ($theme_image_transformations = $this->view->resolveFilename('','image-transformations.php')) {
            $image_transformations = include $theme_image_transformations;
        } else {
            $image_transformations = include APPLICATION_PATH.'/conf/image-transformations.php';
        }

        foreach($image_transformations as $name => $transformation)
            $this->registerImageTransformation($name, $transformation);


        // registration agent relations

        foreach($this->_config['registration.agentRelations'] as $config){
            $def = new Definitions\RegistrationAgentRelation($config);
            $opportunities_meta[$def->metadataName] = $def->getMetadataConfiguration();

            $this->registerRegistrationAgentRelation($def);
        }


        // all metalist groups
        $metalist_groups = [
            'links' => new Definitions\MetaListGroup('links',
                [
                    'title' => [
                        'label' => 'Nome'
                    ],
                    'value' => [
                        'label' => 'Link',
                        'validations' => [
                            'required' => 'O link do vídeo é obrigatório',
                            "v::url('vimeo.com')" => "Insira um link de um vídeo do Vimeo ou Youtube"
                        ]
                    ],
                ],
                \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'),
                true
            ),
            'videos' => new Definitions\MetaListGroup('videos',
                [
                    'title' => [
                        'label' => 'Nome'
                    ],
                    'value' => [
                        'label' => 'Link',
                        'validations' => [
                            'required' => \MapasCulturais\i::__('O link do vídeo é obrigatório'),
                            "v::url('vimeo.com')" => "Insira um link de um vídeo do Vimeo ou Youtube"
                        ]
                    ],
                ],
                \MapasCulturais\i::__('O arquivo enviado não é uma imagem válida.'),
                true
            ),
        ];

        // register metalist groups
        $this->registerMetaListGroup('agent', $metalist_groups['links']);
        $this->registerMetaListGroup('agent', $metalist_groups['videos']);

        $this->registerMetaListGroup('space', $metalist_groups['links']);
        $this->registerMetaListGroup('space', $metalist_groups['videos']);

        $this->registerMetaListGroup('event', $metalist_groups['links']);
        $this->registerMetaListGroup('event', $metalist_groups['videos']);

        $this->registerMetaListGroup('project', $metalist_groups['links']);
        $this->registerMetaListGroup('project', $metalist_groups['videos']);

        $this->registerMetaListGroup('opportunity', $metalist_groups['links']);
        $this->registerMetaListGroup('opportunity', $metalist_groups['videos']);

        $this->registerMetaListGroup('seal', $metalist_groups['links']);
        $this->registerMetaListGroup('seal', $metalist_groups['videos']);

        // register job types
        $this->registerJobType(new JobTypes\ReopenEvaluations(JobTypes\ReopenEvaluations::SLUG));

        // register space types and spaces metadata
        foreach($space_types['items'] as $group_name => $group_config){
            $entity_class = 'MapasCulturais\Entities\Space';
            $group = new Definitions\EntityTypeGroup($entity_class, $group_name, $group_config['range'][0], $group_config['range'][1]);
            $this->registerEntityTypeGroup($group);

            $group_meta = key_exists('metadata', $group_config) ? $group_config['metadata'] : [];

            foreach ($group_config['items'] as $type_id => $type_config){
                $type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);
                $group->registerType($type);
                $this->registerEntityType($type);

                $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
                $type_config['metadata'] = $type_meta;

                // add group metadata to space type
                if(key_exists('metadata', $group_config))
                    foreach($group_meta as $meta_key => $meta_config)
                        if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                                $type_config['metadata'][$meta_key] = $meta_config;

                // add space metadata to space type
                foreach($space_meta as $meta_key => $meta_config)
                    if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                            $type_config['metadata'][$meta_key] = $meta_config;

                foreach($type_config['metadata'] as $meta_key => $meta_config){
                   $metadata = new Definitions\Metadata($meta_key, $meta_config);
                   $this->registerMetadata($metadata, $entity_class, $type_id);
                }
            }
        }

        // register agent types and agent metadata
        $entity_class = 'MapasCulturais\Entities\Agent';

        foreach($agent_types['items'] as $type_id => $type_config){
            $type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);

            $this->registerEntityType($type);

            $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
            $type_config['metadata'] = $type_meta;

            // add agents metadata definition to agent type
            foreach($agents_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){

                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register event types and event metadata
        $entity_class = 'MapasCulturais\Entities\Event';

        foreach($event_types['items'] as $type_id => $type_config){
            $type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);

            $this->registerEntityType($type);

            $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
            $type_config['metadata'] = $type_meta;
            
            // add events metadata definition to event type
            foreach($event_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register project types and project metadata
        $entity_class = 'MapasCulturais\Entities\Project';

        foreach($project_types['items'] as $type_id => $type_config){
            $type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);

            $this->registerEntityType($type);
            $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
            $type_config['metadata'] = $type_meta;

            // add projects metadata definition to project type
            foreach($projects_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register opportunity types and opportunity metadata
        $entity_class = 'MapasCulturais\Entities\Opportunity';

        foreach($opportunity_types['items'] as $type_id => $type_config){
            $type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);

            $this->registerEntityType($type);
            $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
            $type_config['metadata'] = $type_meta;

            // add opportunities metadata definition to opportunity type
            foreach($opportunities_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register Subsite types and Subsite metadata
        $entity_class = 'MapasCulturais\Entities\Subsite';

        // add subsite metadata definition to event type
        foreach($subsite_meta as $meta_key => $meta_config){
            $metadata = new Definitions\Metadata($meta_key, $meta_config);
            $this->registerMetadata($metadata, $entity_class);
        }

        // register seal time unit types
		$entity_class = 'MapasCulturais\Entities\Seal';

        foreach($seal_types['items'] as $type_id => $type_config){
        	$type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);
        	$this->registerEntityType($type);

            $type_meta = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];
            $type_config['metadata'] = $type_meta;
            
        	// add projects metadata definition to project type
            foreach($seals_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register notification metadata
        $entity_class = 'MapasCulturais\Entities\Notification';

        // add notification metadata definition
        foreach($notification_meta as $meta_key => $meta_config){
            $metadata = new Definitions\Metadata($meta_key, $meta_config);
            $this->registerMetadata($metadata, $entity_class);
        }

        // register taxonomies
        if ($theme_taxonomies = $this->view->resolveFilename('','taxonomies.php')) {
            $taxonomies = include $theme_taxonomies;
        } else {
            $taxonomies = include APPLICATION_PATH . '/conf/taxonomies.php';
        }

        foreach($taxonomies as $taxonomy_id => $taxonomy_definition){
            $taxonomy_slug = $taxonomy_definition['slug'];
            $taxonomy_required = key_exists('required', $taxonomy_definition) ? $taxonomy_definition['required'] : false;
            $taxonomy_description = key_exists('description', $taxonomy_definition) ? $taxonomy_definition['description'] : '';
            $restricted_terms = key_exists('restricted_terms', $taxonomy_definition) ? $taxonomy_definition['restricted_terms'] : false;

            $definition = new Definitions\Taxonomy($taxonomy_id, $taxonomy_slug, $taxonomy_description, $restricted_terms, $taxonomy_required);

            $entity_classes = $taxonomy_definition['entities'];

            foreach($entity_classes as $entity_class){
                $this->registerTaxonomy($entity_class, $definition);
            }
        }

        $this->view->register();

        foreach($this->_modules as $module){
            $module->register();
        }

        foreach($this->_plugins as $plugin){
            $plugin->register();
        }

        $this->applyHookBoundTo($this, 'app.register',[&$this->_register]);
        $this->applyHookBoundTo($this, 'app.register:after');
    }



    function getRegisteredGeoDivisions(){
        $result = [];
        foreach($this->_config['app.geoDivisionsHierarchy'] as $key => $division) {

            $display = true;
            if (substr($key, 0, 1) == '_') {
                $display = false;
                $key = substr($key, 1);
            }
            
            if (!is_array($division)) { // for backward compability version < 4.0, $division is string not a array.
                $d = new \stdClass();
                $d->key = $key;
                $d->name = $division;
                $d->metakey = 'geo' . ucfirst($key);
                $d->display = $display;
                $result[] = $d;
            } else {
                $d = new \stdClass();
                $d->key = $key;
                $d->name = $division['name'];
                $d->metakey = 'geo' . ucfirst($key);
                $d->display = $display;
                $result[] = $d;
            }
        }

        return $result;
    }


    /**
     * Returns the configuration array or the specified configuration
     *
     * @param string $key configuration key
     *
     * @return mixed
     */
    public function getConfig($key = null){
        if(is_null($key))
            return $this->_config;
        else
            return key_exists ($key, $this->_config) ? $this->_config[$key] : null;

    }

    public function getPlugins(){
        return $this->_plugins;
    }

    public function getModules(){
        return $this->_modules;
    }

    /**
     * Creates a URL to an controller action action
     *
     * @param string $controller_id the controller id
     * @param string $action_name the action name
     * @param array $data the data to pass to action
     *
     * @see \MapasCulturais\RoutesManager::createUrl()
     *
     * @return string the URL to action
     */
    public function createUrl($controller_id, $action_name = '', $data = []){
        return $this->_routesManager->createUrl($controller_id, $action_name, $data);
    }


    /**********************************************
     * Handle Uploads
     **********************************************/

    /**
     * Handle file uploads.
     *
     * This method handle file uploads and returns an instance, or an array of instances of File Entity. The uploaded file name is sanitized by the method App::sanitizeFilename
     *
     * If the key not exists in $_FILES array, this method returns null.
     *
     * @param string $key the key of the $_FILE array to handle
     *
     * @see \MapasCulturais\App::sanitizeFilename()
     *
     * @return \MapasCulturais\Entities\File|\MapasCulturais\Entities\File[]
     */
    public function handleUpload($key, $file_class_name){
        if(is_array($_FILES) && key_exists($key, $_FILES)){

            if(is_array($_FILES[$key]['name'])){
                $result = [];
                foreach(array_keys($_FILES[$key]['name']) as $i){
                    $tmp_file = [];
                    foreach(array_keys($_FILES[$key]) as $k){
                        $tmp_file[$k] = $k == 'name' ? $this->sanitizeFilename($_FILES[$key][$k][$i]) : $_FILES[$key][$k][$i];
                    }

                    $result[] = new $file_class_name($tmp_file);
                }
            }else{

                if($_FILES[$key]['error']){
                    throw new \MapasCulturais\Exceptions\FileUploadError($key, $_FILES[$key]['error']);
                }

                $mime = mime_content_type($_FILES[$key]['tmp_name']);
                $_FILES[$key]['name'] = $this->sanitizeFilename($_FILES[$key]['name'], $mime);
                $result = new $file_class_name($_FILES[$key]);

            }
            return $result;
        }else{
            return null;
        }
    }

    /**
     * Sanitizes the uploaded files names replaceing spaces with underscores and setting the name to lower case.
     *
     * If the 'app.sanitize_filename_function' configuration key is callable, this method call it after sanitizes the filename.
     *
     * @param type $filename
     *
     * @return string The sanitized filename.
     */
    function sanitizeFilename($filename, $mimetype = false){
        $filename = str_replace(' ','_', strtolower($filename));
        if(is_callable($this->_config['app.sanitize_filename_function'])){
            $cb = $this->_config['app.sanitize_filename_function'];
            $filename = $cb($filename);
        }

        // If the file does not have an extension and is a image, lets put it
        // Wide Image relies on it and we know that cropped images come without extension (blob upload)
        if (empty(pathinfo($filename, PATHINFO_EXTENSION)) && $mimetype) {

            $imagetypes = array(
                'image/jpeg' => 'jpeg',
                'image/bmp' => 'bmp',
                'image/gif' => 'gif',
                'image/tiff' => 'tif',
                'image/png' => 'png',
                'image/x-png' => 'png',
            );

            if (array_key_exists($mimetype, $imagetypes))
                $filename .= '.' . $imagetypes[$mimetype];

        }

        return $filename;
    }

    /*     * ********************************************
     * Hooks System
     * ******************************************** */

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string   $name   A hook name (Optional)
     */
    public function clearHooks($name = null) {
        if (is_null($name)) {
            $this->_hooks = [];
            $this->_excludeHooks = [];
        } else {
            $hooks = $this->_getHookCallables($name);
            foreach ($this->_excludeHooks as $hook => $cb) {
                if (in_array($cb, $hooks))
                    unset($this->_excludeHooks[$hook]);
            }

            foreach ($this->_hooks as $hook => $cbs) {
                foreach ($cbs as $i => $cb)
                    unset($this->_hooks[$hook][$i]);
            }
        }
    }

    protected $_hookCache = [];

    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param  string     $name     A hook name (Optional)
     * @return array|null
     */
    public function getHooks($name = null) {
        return $this->_getHookCallables($name);
    }

    /**
     * Assign hook
     * @param  string   $name       The hook name
     * @param  mixed    $callable   A callable object
     * @param  int      $priority   The hook priority; 0 = high, 10 = low
     */


    protected $hook_count = 0;

    function hook($name, $callable, $priority = 10) {
        $this->hook_count++;
        $priority += ($this->hook_count / 100000);

        $this->_hookCache = [];
        $_hooks = explode(',', $name);
        foreach ($_hooks as $hook) {
            if (trim($hook)[0] === '-') {
                $hook = $this->_compileHook($hook);
                if (!key_exists($hook, $this->_excludeHooks))
                    $this->_excludeHooks[$hook] = [];

                $this->_excludeHooks[$hook][] = $callable;
            }else {
                $priority_key = "$priority";
                $hook = $this->_compileHook($hook);

                if (!key_exists($hook, $this->_hooks))
                    $this->_hooks[$hook] = [];

                if (!key_exists($priority_key, $this->_hooks[$hook]))
                    $this->_hooks[$hook][$priority_key] = [];

                $this->_hooks[$hook][$priority_key][] = $callable;

                ksort($this->_hooks[$hook]);
            }
        }
    }

    function _logHook($name){
        $n = 1;

        if(strpos($name, 'template(') === 0){
            $n = 2;
        }

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $filename = $bt[$n]['file'];
        $fileline = $bt[$n]['line'];
        $lines = file($filename);
        $line = trim($lines[$fileline - 1]);

        $this->log->debug("hook >> \033[37m$name \033[0m(\033[33m$filename:$fileline\033[0m)");
        $this->log->debug("     >> \033[32m$line\033[0m\n");
    }

    protected $hookStack = [];

    /**
     * Invoke hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     * 
     * @return callable[]
     */
    function applyHook($name, $hookArg = null) {
        if (is_null($hookArg))
            $hookArg = [];
        else if (!is_array($hookArg))
            $hookArg = [$hookArg];

        if ($this->_config['app.log.hook']){
            $conf = $this->_config['app.log.hook'];
            if(is_bool($conf) || preg_match('#' . str_replace('*', '.*', $conf) . '#', $name)){
                $this->_logHook($name);

            }
        }

        $this->hookStack[] = (object) [
            'name' => $name,
            'args' => $hookArg,
            'bound' => false,
        ];

        $callables = $this->_getHookCallables($name);
        foreach ($callables as $callable) {
            call_user_func_array($callable, $hookArg);
        }

        array_pop($this->hookStack);

        return $callables;
    }

    /**
     * Invoke hook binding callbacks to the target object
     *
     * @param  object $target_object Object to bind hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     * 
     * @return callable[]
     */
    function applyHookBoundTo($target_object, $name, $hookArg = null) {
        if (is_null($hookArg))
            $hookArg = [];
        else if (!is_array($hookArg))
            $hookArg = [$hookArg];

        if ($this->_config['app.log.hook']){
            $conf = $this->_config['app.log.hook'];
            if(is_bool($conf) || preg_match('#' . str_replace('*', '.*', $conf) . '#', $name)){
                $this->_logHook($name);
            }
        }

        $this->hookStack[] = (object) [
            'name' => $name,
            'args' => $hookArg,
            'bound' => false,
        ];

        $callables = $this->_getHookCallables($name);
        foreach ($callables as $callable) {
            $callable = \Closure::bind($callable, $target_object);
            call_user_func_array($callable, $hookArg);
        }

        array_pop($this->hookStack);

        return $callables;
    }


    function _getHookCallables($name) {
        if(isset($this->_hookCache[$name])){
            return $this->_hookCache[$name];
        }
        $exclude_list = [];
        $result = [];

        foreach ($this->_excludeHooks as $hook => $callables) {
            if (preg_match($hook, $name))
                $exclude_list = array_merge($callables);
        }

        foreach ($this->_hooks as $hook => $_callables) {
            if (preg_match($hook, $name)) {
                foreach ($_callables as $priority => $callables) {
                    foreach ($callables as $callable) {
                        if (!in_array($callable, $exclude_list)){
                            $result[] = (object) ['callable' => $callable, 'priority' => (float) $priority];
                        }
                    }
                }
            }
        }

        usort($result, function($a,$b){
            if($a->priority > $b->priority){
                return 1;
            } elseif ($a->priority < $b->priority) {
                return -1;
            } else {
                return 0;
            }
        });

        $result = array_map(function($el) { return $el->callable; }, $result);

        $this->_hookCache[$name] = $result;

        return $result;
    }

    protected function _compileHook($hook) {
        $hook = trim($hook);

        if ($hook[0] === '-')
            $hook = substr($hook, 1);

        $replaces = [];

        while (preg_match("#\<\<([^<>]+)\>\>#", $hook, $matches)) {
            $uid = uniqid('@');
            $replaces[$uid] = $matches;

            $hook = str_replace($matches[0], $uid, $hook);
        }

        $hook = '#^' . preg_quote($hook) . '$#i';

        foreach ($replaces as $uid => $matches) {
            $regex = str_replace('*', '[^\(\)\:]*', $matches[1]);

            $hook = str_replace($uid, '(' . $regex . ')', $hook);
        }

        return $hook;
    }

    /**********************************************
     * Background Jobs
     **********************************************/

    /**
     * 
     * @param string $type_slug 
     * @param array $data 
     * @param string $start_string 
     * @param string $interval_string 
     * @param int $iterations 
     * @return Job 
     * @throws Exception 
     */
    public function enqueueJob(string $type_slug, array $data, string $start_string = 'now', string $interval_string = '', int $iterations = 1) {
        if($this->config['app.log.jobs']) {
            $this->log->debug("ENQUEUED JOB: $type_slug");
        }

        $type = $this->getRegisteredJobType($type_slug);
        
        if (!$type) {
            throw new \Exception("invalid job type: {$type_slug}");
        }

        $id = $type->generateId($data, $start_string, $interval_string, $iterations);

        if ($job = $this->repo('Job')->find($id)) {
            $this->log->debug('JOB ID JÁ EXISTE: ' . $id);
            return $job;
        }

        $job = new Job($type);

        $job->id = $id;

        $job->iterations = $iterations;

        $job->nextExecutionTimestamp = new DateTime($start_string);
        $job->intervalString = $interval_string;

        foreach ($data as $key => $value) {
            $job->$key = $value;
        }

        $job->save(true);

        return $job;
    }

    public function executeJob() {
        $conn = $this->em->getConnection();

        $job_id = $conn->fetchColumn("
            SELECT id
            FROM job
            WHERE
                next_execution_timestamp <= now() AND
                iterations_count < iterations AND
                status = 0
            ORDER BY next_execution_timestamp ASC
            LIMIT 1");

        if ($job_id) {
            $conn->executeQuery("UPDATE job SET status = 1 WHERE id = '{$job_id}'");
            $job = $this->repo('Job')->find($job_id);

            $this->disableAccessControl();
            $this->applyHookBoundTo($this, "app.executeJob:before");
            $job->execute();
            $this->applyHookBoundTo($this, "app.executeJob:after");
            $this->enableAccessControl();

            return $job_id;
        } else {
            return false;
        }
    }

    /**********************************************
     * Permissions Cache
     **********************************************/
    private $permissionCachePendingQueue = [];

    public function enqueueEntityToPCacheRecreation(Entity $entity){
        if (!$entity->__skipQueuingPCacheRecreation) {
            $entity_key = $entity->id ? "$entity" : "$entity".spl_object_id($entity);
            $this->permissionCachePendingQueue["$entity_key"] = $entity;
        }
    }

    public function isEntityEnqueuedToPCacheRecreation(Entity $entity){
        return isset($this->permissionCachePendingQueue["$entity"]);
    }

    public function persistPCachePendingQueue(){
        $created = false;
        foreach($this->permissionCachePendingQueue as $entity) {
            if (is_int($entity->id) && !$this->repo('PermissionCachePending')->findBy([
                    'objectId' => $entity->id, 'objectType' => $entity->getClassName()
                ])) {
                $pendingCache = new \MapasCulturais\Entities\PermissionCachePending();
                $pendingCache->objectId = $entity->id;
                $pendingCache->objectType = $entity->getClassName();
                $pendingCache->save(true);
                $this->log->debug("pcache pending: $entity");
                $created = true;
            }
        }

        if ($created) {
            $this->em->flush();
        }

        $this->permissionCachePendingQueue = [];
    }

    public function setCurrentSubsiteId(int $subsite_id = null) {
        if(is_null($subsite_id)) {
            $this->_subsite = null;
        } else {
            $subsite = $this->repo('Subsite')->find($subsite_id);

            if(!$subsite) {
                throw new \Exception('Subsite not found');
            }

            $this->_subsite = $subsite;
        }
    }

    private $recreatedPermissionCacheList = [];

    public function setEntityPermissionCacheAsRecreated(Entity $entity){
        $this->recreatedPermissionCacheList["$entity"] = $entity;
    }

    public function isEntityPermissionCacheRecreated(Entity $entity){
        return isset($this->recreatedPermissionCacheList["$entity"]);
    }

    public function recreatePermissionsCache(){
        $item = $this->repo('PermissionCachePending')->findOneBy(['status' => 0], ['id' => 'ASC']);
        if ($item) {
            $start_time = microtime(true);

            $this->disableAccessControl();
            $item->status = 1;
            $item->save(true);
            $this->enableAccessControl();

            $conn = $this->em->getConnection();
            $conn->beginTransaction();

            try {
                $entity = $this->repo($item->objectType)->find($item->objectId);
                if ($entity) {
                    $entity->recreatePermissionCache();
                }
                
                $this->em->remove($item);

                $this->em->flush();
                $conn->commit();
            } catch (\ExceptionAa $e ){
                
                $conn->rollBack();
                
                $this->disableAccessControl();
                $item->status = 0;
                $item->save(true);
                $this->enableAccessControl();

                if(php_sapi_name()==="cli"){
                    echo "\n\t - ERROR - {$e->getMessage()}";
                }
                throw $e;
            }

            if($this->config['app.log.pcache']){
                $end_time = microtime(true);
                $total_time = number_format($end_time - $start_time, 1);

                $this->log->info("PCACHE RECREATED FOR $item IN {$total_time} seconds\n--------\n");
            }
            $this->permissionCachePendingQueue = [];
        }
    }

    /**********************************************
     * Getters
     **********************************************/

    /**
     * Returns the current subsite ID, or null if current site is the main site
     *
     * @return (int|null) ID of the current site or Null for main site
     */
    public function getCurrentSubsiteId(){
        // @TODO: alterar isto quando for implementada a possibilidade de termos instalações de subsite com o tema diferente do Subsite
        if($this->_subsite){
            return $this->_subsite->id;
        }

        return null;
    }

    public function getCurrentSubsite(){
        return $this->_subsite;
    }

    public function getMaxUploadSize($useSuffix=true){
        $MB = 1024;
        $GB = $MB * 1024;

        $convertToKB = function($size) use($MB, $GB){
            switch(strtolower(substr($size, -1))){
                case 'k';
                    $size = intval($size);
                break;

                case 'm':
                    $size = intval($size) * $MB;
                break;

                case 'g':
                    $size = intval($size) * $GB;
                break;
            }

            return $size;
        };

        $max_upload = $convertToKB(ini_get('upload_max_filesize'));
        $max_post = $convertToKB(ini_get('post_max_size'));
        $memory_limit = $convertToKB(ini_get('memory_limit'));

        $result = min($max_upload, $max_post, $memory_limit);

        if(!$useSuffix)
            return $result;

        if($result < $MB){
            $result .= ' KB';
        }else if($result < $GB){
            $result = number_format($result / $MB, 0) . ' MB';
        }else{
            $result = $result / $GB;
            $formated = number_format($result, 1);
            if( $formated == (int) $result )
                $result = intval($result) . ' GB';
            else
                $result = $formated . ' GB';
        }

        return $result;
    }

    public function getOpportunityRegistrationAgentRelationGroupName(){
        return 'registration';
    }

    public function getSiteName(){
        return $this->_config['app.siteName'];
    }

    public function getSiteDescription(){
        return $this->_config['app.siteDescription'];
    }

    /**
     * Returns the RoutesManager
     * @return \MapasCulturais\RoutesManager
     */
    public function getRoutesManager(){
        return $this->_routesManager;
    }

    /**
     * Returns the Doctrine Entity Manager
     * @return \Doctrine\ORM\EntityManager the Doctrine Entity Manager
     */
    public function getEm(){
        return $this->_em;
    }

    /**
     * Returns the view object
     * @return \MapasCulturais\Theme
     */
    public function getView(){
        return $this->view;
    }

    /**
     * Returns the Cache Component
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getCache(){
        return $this->_cache;
    }

    /**
     * Returns the Multisite Cache Component
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function getMsCache(){
        return $this->_mscache;
    }

    /**
     * Runtime Runtime Cache Component
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    public function getRCache(){
        return $this->_rcache;
    }

    /**
     * Returns the Auth Manager Component
     * @return \MapasCulturais\Auth
     */
    public function getAuth(){
        return $this->_auth;
    }

    /**
     * Returns the base url of the project
     * @return string the base url
     */
    public function getBaseUrl(){
        return $this->_config['base.url'];
    }

    /**
     * Returns the asset url of the project
     * @return string the asset url
     */
    public function getAssetUrl(){
        return isset($this->_config['base.assetUrl']) ? $this->_config['base.assetUrl'] : $this->getBaseUrl() . 'assets/';
    }

    /**
     * Returns the logged in user
     * @return \MapasCulturais\Entities\User
     */
    public function getUser(){
        return $this->auth->getAuthenticatedUser();
    }

    /**
     * Returns the File Storage Component
     * @return \MapasCulturais\Storage
     */
    public function getStorage(){
        return $this->_storage;
    }



    /**********************************************
     * Doctrine Helpers
     **********************************************/

    /**
     * Returns a Doctrine Entity Repository
     *
     * if the given repository class name not starts with a slash this function will prepend \MapasCulturais\Entities\ to the class name
     *
     * @param string $name Repository Class Name
     * @return Repository the Entity Repository
     */
    public function repo($name){

        // add MapasCulturais\Entities namespace if no namespace in repo name
        if(strpos($name, '\\') === false)
                $name = "\MapasCulturais\Entities\\{$name}";

        return $this->em->getRepository($name);
    }


    /**********************************************
     * Register functions
     **********************************************/

    public function registerJobType(Definitions\JobType $definition) {
        if(key_exists($definition->slug, $this->_register['job_types'])){
            throw new \Exception("Job type {$definition->slug} already registered");
        }
        $this->_register['job_types'][$definition->slug] = $definition;
    }

    /**
     * 
     * @return Definitions\JobType[]
     */
    public function getRegisteredJobTypes() {
        return $this->_register['job_types'];
    }

    /**
     * 
     * @return Definitions\JobType
     */
    public function getRegisteredJobType(string $slug) {
        return $this->_register['job_types'][$slug] ?? null;
    }

    /**
     * Register a new role
     *
     * @param Definitions\Role $role the role definition
     * @return void
     */
    public function registerRole(Definitions\Role $role){
        $this->_register['roles'][$role->getRole()] = $role;
    }

    /**
     * Returns the registered roles definitions
     *
     * @return \MapasCulturais\Definitions\Role[]
     */
    public function getRoles() {
        return $this->_register['roles'];
    }

    /**
     * Returns the role definition
     *
     * @param string $role
     * @return \MapasCulturais\Definitions\Role|null
     */
    public function getRoleDefinition(string $role) {
        return $this->_register['roles'][$role] ?? null;
    }

    /**
     * Returns the role name
     *
     * @param string $role
     * @return string
     */
    public function getRoleName(string $role){
        $def = $this->_register['roles'][$role] ?? null;
        return $def ? $def->name : $role;
    }


    function registerRegistrationAgentRelation(Definitions\RegistrationAgentRelation $def){
        if(key_exists($def->agentRelationGroupName, $this->_register['registration_agent_relations'])){
            throw new \Exception('There is already a RegistrationAgentRelation with agent relation group name "' . $def->agentRelationGroupName . '"');
        }

        $this->_register['registration_agent_relations'][$def->agentRelationGroupName] = $def;
    }

    /**
     *
     * @return \MapasCulturais\Definitions\RegistrationAgentRelation[]
     */
    function getRegisteredRegistrationAgentRelations(){
        return $this->_register['registration_agent_relations'];
    }

    function getRegistrationOwnerDefinition(){
        $config = $this->getConfig('registration.ownerDefinition');
        $definition = new Definitions\RegistrationAgentRelation($config);
        return $definition;
    }

    function getRegistrationAgentsDefinitions(){
        $definitions =  ['owner' => $this->getRegistrationOwnerDefinition()];
        foreach ($this->getRegisteredRegistrationAgentRelations() as $groupName => $def){
            $definitions[$groupName] = $def;
        }
        return $definitions;
    }

    function getRegisteredRegistrationAgentRelationByAgentRelationGroupName($group_name){
        if(key_exists($group_name, $this->_register['registration_agent_relations'])){
            return $this->_register['registration_agent_relations'][$group_name];
        }else{
            return null;
        }
    }

    function registerChatThreadType(Definitions\ChatThreadType $definition)
    {
        if (isset($this->_register['chat_thread_types'][$definition->slug])) {
            throw new \Exception("Attempting to re-register " .
                                 "{$definition->slug}.");
        }
        $this->_register['chat_thread_types'][$definition->slug] = $definition;
        return;
    }

    function getRegisteredChatThreadTypes(): array
    {
        return $this->_register['chat_thread_types'];
    }

    function getRegisteredChatThreadType($slug)
    {
        return ($this->_register['chat_thread_types'][$slug] ?? null);
    }

    /**
     * Register a API Output Class
     *
     * If the $api_output_id is not informed this method will create the id based on namespace and class name
     *
     * @example Example of auto generated ids: the class <b>\MapasCulturais\ApiOutputs\Json</b> will receive the id <b>json</b>
     * @example Example of auto generated ids: the class <b>\MyPlugin\ApiOutputs\CSV</b> will receive the id <b>myplugin.apiapi_outputs.csv</b>
     *
     * @param string $api_output_class_name the api_output class name
     * @param string $api_output_id the api_output id
     *
     */
    public function registerApiOutput($api_output_class_name, $api_output_id = null){
        if(is_null($api_output_id))
            $api_output_id = strtolower(str_replace('\\', '.', str_replace('MapasCulturais\ApiOutputs\\', '', $api_output_class_name)));

        $this->_register['api_outputs'][$api_output_id] = $api_output_class_name;
    }

    /**
     * Returns the API Output by the class name.
     *
     * This method returns null if the api_output class name is not registered or
     * is not a subclass of \MapasCulturais\ApiOutput
     *
     * @param string $api_output_class_name The API Output class name
     *
     * @return \MapasCulturais\ApiOutput the API Output
     */
    public function getRegisteredApiOutputByClassName($api_output_class_name){
        if(in_array($api_output_class_name, $this->_register['api_outputs']) && class_exists($api_output_class_name) && is_subclass_of($api_output_class_name, '\MapasCulturais\ApiOutput'))
            return $api_output_class_name::i();
        else
            return null;

    }

    /**
     * Returns the API Output by the api_output id.
     *
     * This method returns null if there is no api_output class registered under the specified id.
     *
     * @param string $api_output_id The API Output Id
     *
     * @return \MapasCulturais\ApiOutput The API Output
     */
    public function getRegisteredApiOutputById($api_output_id){
        $api_output_id = strtolower($api_output_id);
        if(key_exists($api_output_id, $this->_register['api_outputs']) && class_exists($this->_register['api_outputs'][$api_output_id]) && is_subclass_of($this->_register['api_outputs'][$api_output_id], '\MapasCulturais\ApiOutput')){
            $api_output_class_name = $this->_register['api_outputs'][$api_output_id];
            return $api_output_class_name::i();
        }else{
            return null;
        }

    }

    /**
     * Returns the registered API Output Id of the given API Output or class name.
     *
     * If the $api_output is not a valid registered API Output this method returns null.
     *
     * @param \MapasCulturais\ApiOutput|string $api_output The API Output or class name
     *
     * @return sring the API Output id
     */
    public function getRegisteredApiOutputId($api_output){
        if(is_object($api_output))
            $api_output = get_class($api_output);

        $api_output_id = array_search($api_output, $this->_register['api_outputs']);

        return $api_output_id ? $api_output_id : null;
    }

    public function registerAuthProvider($name){
        $nextId = count($this->_register['auth_providers']) + 1;
        $this->_register['auth_providers'][$nextId] = strtolower($name);
    }

    public function getRegisteredAuthProviderId($name){
        return array_search(strtolower($name), $this->_register['auth_providers']);
    }

    /**
     * Register a controller class.
     *
     * @param string $id the controller id.
     * @param string $controller_class_name.
     * @param string $default_action The default action name. The deault is 'index'.
     * @param string $view_dir view dir.
     *
     * @throws \Exception
     */
    public function registerController($id, $controller_class_name, $default_action = 'index', $view_dir = null){
        $id = strtolower($id);

        if(key_exists($id, $this->_register['controllers']))
            throw new \Exception('Controller Id already in use');

        $this->_register['controllers-by-class'][$controller_class_name] = $id;

        $this->_register['controllers'][$id] = $controller_class_name;
        $this->_register['controllers_default_actions'][$id] = $default_action;
        $this->_register['controllers_view_dirs'][$id] = $view_dir ? $view_dir : $id;
    }

    public function getRegisteredControllers($return_controller_object = false){
        $controllers = $this->_register['controllers'];
        if($return_controller_object){
            foreach($controllers as $id => $class){
                $controllers[$id] = $class::i();
            }
        }

        return $controllers;
    }

    /**
     * Returns the controller object with the given id.
     *
     * If the controller is registered, returns the instance calling the method i() (singleton getInstance).
     *
     * @param string $id The controller id.
     *
     * @see \MapasCulturais\Traits\Singleton::i()
     *
     * @return \MapasCulturais\Controller|null
     */
    public function getController($id){
        $id = strtolower($id);
        if(key_exists($id, $this->_register['controllers']) && class_exists($this->_register['controllers'][$id])){
            $class = $this->_register['controllers'][$id];
            return $class::i($id);
        }else{
            return null;
        }
    }

    /**
     * Alias to getController
     *
     * @param string $idThe controller id.
     *
     * @see \MapasCulturais\App::getController()
     *
     * @return \MapasCulturais\Controller
     */
    public function controller($id){
        return $this->getController($id);
    }


    /**
     * Returns the controller of the given class.
     *
     * This method verifies if the controller is registered before try to get the instance to return.
     *
     * @param string $controller_class The controller class name.
     *
     * @return \MapasCulturais\Controller|null The controller
     */
    public function getControllerByClass($controller_class){
        if(key_exists($controller_class, $this->_register['controllers-by-class']) && class_exists($controller_class)){
            return $controller_class::i($this->_register['controllers-by-class'][$controller_class]);
        }else{
            return null;
        }
    }

    /**
     * Returns the controller of the class with the same name of the entity on the parent namespace.
     *
     * If the namespace is omited in the class name this method assumes MapasCulturais\Entities as the namespace of the entity.
     *
     * This method calls the getControllerByClass() to return the controller
     *
     * @param \MapasCulturais\Entity|string $entity The entity object or class name
     *
     * @see \MapasCulturais\App::getControllerByClass()
     *
     * @return \MapasCulturais\Controllers\EntityController|null The controller
     */
    public function getControllerByEntity($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();
        else if(is_string($entity) && strpos($entity, '\\') === false)
            $entity = '\MapasCulturais\Entities\\' . $entity;

        $controller_class = preg_replace('#\\\Entities\\\([^\\\]+)$#', '\\Controllers\\\$1', $entity);
        return $this->getControllerByClass($controller_class);
    }

    /**
     * Returns the controller id of the class with the same name of the entity on the parent namespace.
     *
     * If the namespace is omited in the class name this method assumes MapasCulturais\Entities as the namespace of the entity.
     *
     * @param \MapasCulturais\Entity|string $entity The entity object or class name
     *
     * @see \MapasCulturais\App::getControllerId()
     *
     * @return \MapasCulturais\Controller|null The controller
     */
    public function getControllerIdByEntity($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();
        else if(is_string($entity) && strpos($entity, '\\') === false)
            $entity = '\MapasCulturais\Entities\\' . $entity;

        $controller_class = preg_replace('#\\\Entities\\\([^\\\]+)$#', '\\Controllers\\\$1', $entity);

        return $this->getControllerId($controller_class);
    }

    /**
     * Return the controller id of the given controller object or class.
     *
     * @param mixed $object controller object or full class name
     *
     * @return string
     */
    public function getControllerId($object){
        if(is_object($object))
            $object = get_class($object);

        return array_search($object, $this->_register['controllers']);
    }

    /**
     * Alias to getControllerId.
     *
     * @param mixed $object controller object or full class name
     *
     * @see \MapasCulturais\App::getControllerId()
     *
     * @return string
     */
    public function controllerId($object){
        return $this->getControllerId($object);
    }


    /**
     * Returns the controller default action name.
     *
     * @param string $controller_id
     *
     * @return string
     */
    public function getControllerDefaultAction($controller_id){
        $controller_id = strtolower($controller_id);
        if(key_exists($controller_id, $this->_register['controllers_default_actions'])){
            return $this->_register['controllers_default_actions'][$controller_id];
        }else{
            return null;
        }
    }


    /**
     * Alias to getControllerDefaultAction.
     *
     * @param string $controller_id The id of the controller.
     *
     * @see \MapasCulturais\App::getControllerDefaultAction()
     *
     * @return string
     */
    public function controllerDefaultAction($controller_id){
        return $this->getControllerDefaultAction($controller_id);
    }

    /**
     * Register an Entity Type Group.
     *
     * @param \MapasCulturais\Definitions\EntityTypeGroup $group The Entity Type Group to register.
     */
    function registerEntityTypeGroup(Definitions\EntityTypeGroup $group){
        if(!key_exists($group->entity_class, $this->_register['entity_type_groups']))
                $this->_register['entity_type_groups'][$group->entity_class] = [];

        $this->_register['entity_type_groups'][$group->entity_class][] = $group;
    }

    /**
     * Returns the Entity Type Group of the given entity class and type id.
     *
     * @param string $entity The entity object or class name..
     * @param int $type_id The Entity Type id.
     *
     * @return \MapasCulturais\Definitions\EntityTypeGroup|null
     */
    function getRegisteredEntityTypeGroupByTypeId($entity, $type_id){
        if(is_object($entity))
            $entity = $entity->getClassName();

        if(key_exists($entity, $this->_register['entity_type_groups'])){
            foreach($this->_register['entity_type_groups'][$entity] as $group){
                if($group->min_id >= $type_id && $group->max_id <= $type_id)
                    return $group;
            }
            return null;
        }else{
            return null;
        }
    }

    /**
     * Returns an array with the registererd Entity Type Groups for the given entity object or class
     *
     * @param \MapasCulturais\Entity|string $entity The entity object or class name
     *
     * @return \MapasCulturais\Definitions\EntityTypeGroup[]
     */
    function getRegisteredEntityTypeGroupsByEntity($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();

        if(key_exists($entity, $this->_register['entity_type_groups'])){
            return $this->_register['entity_type_groups'][$entity];
        }else{
            return [];
        }
    }

    /**
     * Register an Entity Type.
     *
     * @param \MapasCulturais\Definitions\EntityType $type The Entity Type to register.
     */
    function registerEntityType(Definitions\EntityType $type){
        if(!key_exists($type->entity_class, $this->_register['entity_types']))
                $this->_register['entity_types'][$type->entity_class] = [];

        $this->_register['entity_types'][$type->entity_class][$type->id] = $type;
    }

    /**
     * Returns the Entity Type Definition if it exists.
     *
     * @param type $entity The entity object or class name
     * @param type $type_id The id of the type
     *
     * @return \MapasCulturais\Definitions\EntityType|null
     */
    function getRegisteredEntityTypeById($entity, $type_id){
        if(is_object($entity))
            $entity = $entity->getClassName();

        if(isset($this->_register['entity_types'][$entity][$type_id]))
            return $this->_register['entity_types'][$entity][$type_id];
        else
            return null;
    }

    /**
     * Check if the Entity Type exists.
     *
     * @param tring $entity The entity object or class name
     * @param int $type_id The type id
     *
     * @return boolean true if the entity type exists or false otherwise
     */
    function entityTypeExists($entity, $type_id){
        return !!$this->getRegisteredEntityTypeById($entity, $type_id);
    }

    /**
     * Returns the Entity Type of the given entity.
     *
     * @param \MapasCulturais\Entity $object The entity.
     *
     * @return \MapasCulturais\Definitions\EntityType
     */
    function getRegisteredEntityType(Entity $object){
        return $this->_register['entity_types'][$object->getClassName()][(string)$object->type] ?? null;
    }

    function getRegisteredEntityTypeByTypeName($entity, string $type_name) {
        foreach($this->getRegisteredEntityTypes($entity) as $type) {
            if (strtolower($type->name) == trim(strtolower($type_name))) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Returns the registered entity types for the given entity class or object.
     *
     * @param \MapasCulturais\Entity|string $entity The entity.
     *
     * @return \MapasCulturais\Definitions\EntityType[]
     */
    function getRegisteredEntityTypes($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();

        return @$this->_register['entity_types'][$entity];
    }

    function registerRegistrationFieldType(Definitions\RegistrationFieldType $registration_field){
        $this->_register['registration_fields'][$registration_field->slug] = $registration_field;
    }

    function getRegisteredRegistrationFieldTypes(){
        return $this->_register['registration_fields'];
    }

    function getRegisteredRegistrationFieldTypeBySlug($slug) {
        if (isset($this->_register['registration_fields'][$slug])) {
            return $this->_register['registration_fields'][$slug];
        } else {
            return null;
        }
    }

    /**
     * Register an Entity Metadata Definition.
     *
     * @param \MapasCulturais\Definitions\Metadata $metadata The metadata definition
     * @param string $entity_class The Entity Class Name
     * @param int $entity_type_id The Entity Type id
     */
    function registerMetadata(Definitions\Metadata $metadata, $entity_class, $entity_type_id = null){
        if($entity_class::usesTypes() && is_null($entity_type_id)){
            foreach($this->getRegisteredEntityTypes($entity_class) as $type){
                if($type){
                   $this->registerMetadata($metadata, $entity_class, $type->id);
                }
            }
            return;
        }

        $key = is_null($entity_type_id) ? $entity_class : $entity_class . ':' . $entity_type_id;
        if(!key_exists($key, $this->_register['entity_metadata_definitions']))
            $this->_register['entity_metadata_definitions'][$key] = [];

        $this->_register['entity_metadata_definitions'][$key][$metadata->key] = $metadata;

        if($entity_type_id){
            if(!key_exists($entity_class, $this->_register['entity_metadata_definitions']))
                $this->_register['entity_metadata_definitions'][$entity_class] = [];

            $this->_register['entity_metadata_definitions'][$entity_class][$metadata->key] = $metadata;
        }
    }

    function unregisterEntityMetadata($entity_class, $key = null){
        foreach($this->_register['entity_metadata_definitions'] as $k => $metadata){
            if($k === $entity_class || strpos($k . ':', $entity_class) === 0){
                if($key){
                    unset($this->_register['entity_metadata_definitions'][$k][$key]);
                } else {
                    $this->_register['entity_metadata_definitions'][$k] = [];
                }
            }
        }

    }

    /**
     * Returns an array with the Metadata Definitions of the given entity object or class name.
     *
     * If the given entity class has no registered metadata, returns an empty array
     *
     * @param \MapasCulturais\Entity $entity
     *
     * @return \MapasCulturais\Definitions\Metadata[]
     */
    function getRegisteredMetadata($entity, $type = null){
        if(is_object($entity))
            $entity = $entity->getClassName();

        $key = $entity::usesTypes() && $type ? "{$entity}:{$type}" : $entity;
        return key_exists($key, $this->_register['entity_metadata_definitions']) ? $this->_register['entity_metadata_definitions'][$key] : [];
    }

    /**
     * Return a metada definition
     * @param string $metakey
     * @param string $entity
     * @param int $type
     * @return \MapasCulturais\Definitions\Metadata
     */
    function getRegisteredMetadataByMetakey($metakey, $entity, $type = null){
        if(is_object($entity))
            $entity = $entity->getClassName();
        $metas = $this->getRegisteredMetadata($entity, $type);
        return key_exists($metakey, $metas) ? $metas[$metakey] : null;

    }

    /**
     * Register a new File Group Definition to the specified controller.
     *
     * @param string $controller_id The id of the controller.
     * @param \MapasCulturais\Definitions\FileGroup $group The group to register
     */
    function registerFileGroup($controller_id, Definitions\FileGroup $group){
        $controller_id = strtolower($controller_id);
        if(!key_exists($controller_id, $this->_register['file_groups']))
            $this->_register['file_groups'][$controller_id] = [];

        $this->_register['file_groups'][$controller_id][$group->name] = $group;
    }

    /**
     * Returns the File Group Definition for the given controller id and group name.
     *
     * If the File Group Definition not exists returns null
     *
     * @param string $controller_id The controller id.
     * @param string $group_name The group name.
     *
     * @return \MapasCulturais\Definitions\FileGroup|null The File Group Definition
     */
    function getRegisteredFileGroup($controller_id, $group_name){
        if($controller_id && $group_name && key_exists($controller_id, $this->_register['file_groups']) && key_exists($group_name, $this->_register['file_groups'][$controller_id]))
            return $this->_register['file_groups'][$controller_id][$group_name];
        else
            return null;
    }

    function getRegisteredFileGroupsByEntity($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();

        $controller_id = $this->getControllerIdByEntity($entity);

        return $controller_id && key_exists($controller_id, $this->_register['file_groups']) ? $this->_register['file_groups'][$controller_id] : [];

    }

    /**
     * Register a new image transformation.
     *
     * @see \MapasCulturais\Entities\File::_transform()
     *
     * @param type $name
     * @param type $transformation
     */
    function registerImageTransformation($name, $transformation){
        $this->_register['image_transformations'][$name] = trim($transformation);
    }

    /**
     * Returns the image transformation expression.
     *
     * @param string $name the transformation register name
     *
     * @return string The Transformation Expression
     */
    function getRegisteredImageTransformation($name){
        return key_exists($name, $this->_register['image_transformations']) ?
                $this->_register['image_transformations'][$name] :
                null;
    }

    /**
     * Register a new MetaList Group Definition to the specified controller.
     *
     * @param string $controller_id The id of the controller.
     * @param \MapasCulturais\Definitions\MetaListGroup $group The group to register
     */
    function registerMetaListGroup($controller_id, Definitions\MetaListGroup $group){
        if(!key_exists($controller_id, $this->_register['metalist_groups']))
            $this->_register['metalist_groups'][$controller_id] = [];

        $this->_register['metalist_groups'][$controller_id][$group->name] = $group;
    }

    /**
     * Returns the MetaList Group Definition for the given controller id and group name.
     *
     * If the MetaList Group Definition not exists returns null
     *
     * @param string $controller_id The controller id.
     * @param string $group_name The group name.
     *
     * @return \MapasCulturais\Definitions\MetaListGroup|null The MetaList Group Definition
     */
    function getRegisteredMetaListGroup($controller_id, $group_name){
        if(key_exists($controller_id, $this->_register['metalist_groups']) && key_exists($group_name, $this->_register['metalist_groups'][$controller_id]))
            return $this->_register['metalist_groups'][$controller_id][$group_name];
        else
            return null;
    }

    function getRegisteredMetaListGroupsByEntity($entity){
        if(is_object($entity))
            $entity = $entity->getClassName();

        $controller_id = $this->getControllerIdByEntity($entity);

        return key_exists($controller_id, $this->_register['metalist_groups']) ? $this->_register['metalist_groups'][$controller_id] : [];
    }

    /**
     * Register a Taxonomy Definition to an entity class.
     *
     * @param string $entity_class The entity class name to register.
     * @param \MapasCulturais\Definitions\Taxonomy $definition
     */
    function registerTaxonomy($entity_class, Definitions\Taxonomy $definition){
        if(!key_exists($entity_class, $this->_register['taxonomies']['by-entity']))
                $this->_register['taxonomies']['by-entity'][$entity_class] = [];

        $this->_register['taxonomies']['by-entity'][$entity_class][$definition->slug] = $definition;

        $this->_register['taxonomies']['by-id'][$definition->id] = $definition;
        $this->_register['taxonomies']['by-slug'][$definition->slug] = $definition;
    }

    /**
     * Returns the Taxonomy Definition with the given id.
     *
     * @param int $taxonomy_id The id of the taxonomy to return
     *
     * @return \MapasCulturais\Definitions\Taxonomy The Taxonomy Definition
     */
    function getRegisteredTaxonomyById($taxonomy_id){
        return key_exists($taxonomy_id, $this->_register['taxonomies']['by-id']) ? $this->_register['taxonomies']['by-id'][$taxonomy_id] : null;
    }

    /**
     * Returns the Taxonomy Definition with the given slug.
     *
     * @param string $taxonomy_slug The slug of the taxonomy to return
     *
     * @return \MapasCulturais\Definitions\Taxonomy The Taxonomy Definition
     */
    function getRegisteredTaxonomyBySlug($taxonomy_slug){
        return key_exists($taxonomy_slug, $this->_register['taxonomies']['by-slug']) ? $this->_register['taxonomies']['by-slug'][$taxonomy_slug] : null;
    }

    /**
     * Returns an array with all registered taxonomies definitions to the given entity object or class name.
     *
     * If there is no registered taxonomies to the given entity returns an empty array.
     *
     * @param \MapasCulturais\Entity|string $entity The entity object or class name
     *
     * @return \MapasCulturais\Definitions\Taxonomy[] The Taxonomy Definitions objects or an empty array
     */
    function getRegisteredTaxonomies($entity = null){
        if(is_object($entity))
            $entity = $entity->getClassName();

        if(is_null($entity)){
            return $this->_register['taxonomies']['by-entity'];
        }else{
            return key_exists($entity, $this->_register['taxonomies']['by-entity']) ? $this->_register['taxonomies']['by-entity'][$entity] : [];
        }
    }

    /**
     * Returns the registered Taxonomy Definition with the given slug for the given entity object or class name.
     *
     * If the given entity don't have the given taxonomy slug registered, returns null.
     *
     * @param type $entity The entity object or class name.
     * @param type $taxonomy_slug The taxonomy slug.
     *
     * @return \MapasCulturais\Definitions\Taxonomy The Taxonomy Definition.
     */
    function getRegisteredTaxonomy($entity, $taxonomy_slug){
        if(is_object($entity))
            $entity = $entity->getClassName();

        return key_exists($entity, $this->_register['taxonomies']['by-entity']) && key_exists($taxonomy_slug, $this->_register['taxonomies']['by-entity'][$entity]) ?
                    $this->_register['taxonomies']['by-entity'][$entity][$taxonomy_slug] : null;
    }


    /**
     * Register an Evaluation Method
     * @param \MapasCulturais\Definitions\EvaluationMethod $def
     */
    function registerEvaluationMethod(Definitions\EvaluationMethod $def){
        $this->_register['evaluation_method'][$def->slug] = $def;
    }


    /**
     * Returns the evaluation methods definitions
     * @return \MapasCulturais\Definitions\EvaluationMethod[];
     */
    function getRegisteredEvaluationMethods($return_internal = false){
        return array_filter($this->_register['evaluation_method'], function(Definitions\EvaluationMethod $em) use ($return_internal) {
            if($return_internal || !$em->internal) {
                return $em;
            }
        });
    }

    /**
     * Unregister an Evaluation Method
     * @param \MapasCulturais\Definitions\EvaluationMethod $def
     */
    function unregisterEvaluationMethod($slug){
        unset($this->_register['evaluation_method'][$slug]);
    }

    /**
     * Returns the evaluation method definition
     *
     * @param string $slug
     *
     * @return \MapasCulturais\Definitions\EvaluationMethod;
     */
    function getRegisteredEvaluationMethodBySlug($slug){
        if(isset($this->_register['evaluation_method'][$slug])){
            return $this->_register['evaluation_method'][$slug];
        } else {
            return null;
        }
    }

    /*************
     * Utils
     ************/

    function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }


    function detachRS($rs){
        $em = $this->_em;

        foreach($rs as $r){
            $em->detach($r);
        }
    }

    /**************
     * Utils
     **************/

    function getManagedEntity(Entity $entity){
        if($entity->getEntityState() > 2){
            $entity = App::i()->repo($entity->getClassName())->find($entity->id);
            $entity->refresh();
        }

        return $entity;
    }

    /**
     * returns Swift_Mailer instance
     *
     * @return \Swift_Mailer Mailer object
     */
    function getMailer() {
        $transport = [];

        // server
        $server = isset($this->_config['mailer.server']) &&  !empty($this->_config['mailer.server']) ? $this->_config['mailer.server'] : false;

        // default transport SMTP
        $transport_type = isset($this->_config['mailer.transport']) &&  !empty($this->_config['mailer.transport']) ? $this->_config['mailer.transport'] : 'smtp';

        // default port to 25
        $port = isset($this->_config['mailer.port']) &&  !empty($this->_config['mailer.port']) ? $this->_config['mailer.port'] : 25;

        // default encryption protocol to ssl
        $protocol = isset($this->_config['mailer.protocol']) ? $this->_config['mailer.protocol'] : null;

        if ($transport_type == 'smtp' && false !== $server) {

            $transport = \Swift_SmtpTransport::newInstance($server, $port, $protocol);

            // Maybe add username and password
            if (isset($this->_config['mailer.user']) && !empty($this->_config['mailer.user']) &&
                isset($this->_config['mailer.psw']) && !empty($this->_config['mailer.psw']) ) {

                $transport->setUsername($this->_config['mailer.user'])->setPassword($this->_config['mailer.psw']);
            }

        } elseif ($transport_type == 'sendmail' && false !== $server) {
            $transport = \Swift_SendmailTransport::newInstance($server);
        } elseif ($transport_type == 'mail') {
            $transport = \Swift_MailTransport::newInstance();
        } else {
            return false;
        }

        $instance = \Swift_Mailer::newInstance($transport);

        return $instance;
    }

    /**
     *
     * @param array $args
     * @return \Swift_Message
     */
    function createMailMessage(array $args = []){
        $message = \Swift_Message::newInstance();

        if($this->_config['mailer.from']){
            $message->setFrom($this->_config['mailer.from']);
        }

        if($this->_config['mailer.alwaysTo']){
            $message->setTo($this->_config['mailer.alwaysTo']);
        }

        if($this->_config['mailer.bcc']){
            $message->setBcc($this->_config['mailer.bcc']);
        }

        if($this->_config['mailer.replyTo']){
            $message->setReplyTo($this->_config['mailer.replyTo']);
        }

        $type = $message->getHeaders()->get('Content-Type');
        $type->setValue('text/html');
        $type->setParameter('charset', 'utf-8');

        $original = [];
        foreach($args as $key => $value){
            if(in_array(strtolower($key), ['to', 'cc', 'bcc']) && $this->_config['mailer.alwaysTo']){
                $original[$key] = $value;
                continue;
            }

            $key = ucfirst($key);
            $method_name = 'set' . $key;

            if(method_exists($message, $method_name)){
                $message->$method_name($value);
            }
        }

        if($this->_config['mailer.alwaysTo']){
            foreach($original as $key => $val){
                if(is_array($val)){
                    $val = implode(', ', $val);
                }
                $message->setBody("<strong>ORIGINALMENTE $key:</strong> $val <br>\n" . $message->getBody());
            }
        }

        return $message;
    }

    function sendMailMessage(\Swift_Message $message){
        $failures = [];
        $mailer = $this->getMailer();

        if (!is_object($mailer))
            return false;

        try {
            $mailer->send($message,$failures);
            return true;
        } catch(\Swift_TransportException $exception) {
            App::i()->log->info('Swift Mailer error: ' . $exception->getMessage());
            return false;
        }
    }

    function createAndSendMailMessage(array $args = []){
        $message = $this->createMailMessage($args);
        return $this->sendMailMessage($message);
    }

    function renderMustacheTemplate($template,$templateData) {
        if(!is_array($templateData) && !is_object($templateData)) {
            throw new \Exception('Template data not object or array');
        }

        $templateData = (object) $templateData;

        $templateData->siteName = $this->view->dict('site: name', false);
        $templateData->siteDescription = $this->view->dict('site: description', false);
        $templateData->siteOwner = $this->view->dict('site: owner', false);
        $templateData->baseUrl = $this->getBaseUrl();

        if(!($footer_name = $this->view->resolveFileName('templates/' . i::get_locale(), '_footer.html'))) {
            if(!($footer_name = $this->view->resolveFileName('templates/pt_BR', '_footer.html'))) {
                throw new \Exception('Email footer template not found');
            }
        }

        if(!($header_name = $this->view->resolveFileName('templates/' . i::get_locale(), '_header.html'))) {
            if(!($header_name = $this->view->resolveFileName('templates/pt_BR', '_header.html'))) {
                throw new \Exception('Email header template not found');
            }
        }

        if(!($file_name = $this->view->resolveFileName('templates/' . i::get_locale(), $template))) {
            if(!($file_name = $this->view->resolveFileName('templates/pt_BR', $template))) {
                throw new \Exception('Email Template undefined');
            }
        }

        $mustache = new \Mustache_Engine();

        $headerData = $templateData;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).headerData", [&$headerData]);
        $_header = $mustache->render(file_get_contents($header_name), $headerData);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).header", [&$_header]);

        $footerData = $templateData;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).footerData", [&$footerData]);
        $_footer = $mustache->render(file_get_contents($footer_name),$footerData);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).footer", [&$_footer]);

        $templateData->_footer = $_footer;
        $templateData->_header = $_header;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).templateData", [&$templateData]);
        $content = $mustache->render(file_get_contents($file_name), $templateData);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template}).content", [&$content]);

        return $content;
    }

    function renderMailerTemplate($slug, $templateData = []) {
        if(array_key_exists($slug,$this->_config['mailer.templates'])) {
            $message = $this->_config['mailer.templates'][$slug];
            $message['body'] = $this->renderMustacheTemplate($message['template'],$templateData);
            return $message;
        } else {
            throw new Exceptions\MailTemplateNotFound($slug);
        }
    }

    /**************
     * GetText
     **************/
    /* deprecated, use MapasCulturais\i::get_locale();
     *
     *
     *
     */
    static function getCurrentLCode(){
        return \MapasCulturais\i::get_locale();
    }

    static function getTranslations($lcode, $domain = null) {
        $app = App::i();
        $log = key_exists('app.log.translations', $app->_config) && $app->_config['app.log.translations'];

        $cache_id = $domain ? "app.translation:{$domain}:{$lcode}" : "app.translation::{$lcode}";

        $use_cache = key_exists('app.useTranslationsCache', $app->_config) && $app->_config['app.useTranslationsCache'];

        if ($use_cache && $app->cache->contains($cache_id)) {
            return $app->cache->fetch($cache_id);
        }

        $translations_filename = APPLICATION_PATH . ( $domain ? "translations/{$domain}/{$lcode}.php" : "translations/{$lcode}.php" );

        if (file_exists($translations_filename)) {
            $translations = include $translations_filename;
        } else {
            if ($log) {
                $app->applyHook("txt({$domain}.{$lcode}).missingFile");
                $app->log->warn("TXT > missing '$lcode' translation file for domain '$domain'");
            }
            $translations = [];
        }
        if ($use_cache) {
            $app->cache->save($cache_id, $translations);
        }

        return $translations;
    }

    static function txt($message, $domain = null, $lcode = null){
        $app = App::i();
        $message = trim($message);

        if(is_null($lcode)){
            $lcode = $app->getCurrentLCode();
        }

        $translations = self::getTranslations($lcode, $domain);
        $backtrace = debug_backtrace(3,1)[0];
        $file = str_replace(APPLICATION_PATH,'',$backtrace['file']);

        $log = key_exists('app.log.translations', $app->_config) && $app->_config['app.log.translations'];

        if(key_exists($file, $translations) && is_array($translations[$file]) && key_exists($message, $translations[$file])){
            $message = $translations[$file][$message];
        }elseif(key_exists($message, $translations)){
            $message = $translations[$message];
        }elseif($log){
            $app->applyHook("txt({$domain}.{$lcode}).missingTranslation");
            $app->log->warn ("TXT > missing '$lcode' translation for message '$message' in domain '$domain'");
        }


        return $message;

    }

    static function txts($singular_message, $plural_message, $n, $domain = null) {
        if ($n === 1) {
            return self::txt($singular_message, $domain);
        } else {
            return self::txt($plural_message, $domain);
        }
    }

    function getReadableName($id) {
        if (array_key_exists($id, $this->_config['routes']['readableNames'])) {
            return $this->_config['routes']['readableNames'][$id];
        }
        return null;
    }
}
