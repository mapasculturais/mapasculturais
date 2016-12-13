<?php
namespace MapasCulturais;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use SwiftMailer\SwiftMailer;

/**
 * MapasCulturais Application class.
 *
 *
 * @property-read \Doctrine\ORM\EntityManager $em The Doctrine Entity Manager
 * @property-read \Slim\Log $log Slim Logger
 * @property-read \Doctrine\Common\Cache\CacheProvider $cache Cache Provider
 * @property-read \Doctrine\Common\Cache\ArrayCache $rcache Runtime Cache Provider
 * @property-read \MapasCulturais\AuthProvider $auth The Authentication Manager Component.
 * @property-read \MapasCulturais\Theme $view The MapasCulturais View object
 * @property-read \MapasCulturais\Storage\FileSystem $storage File Storage Component.
 * @property-read \MapasCulturais\Entities\User $user The Logged in user.
 * @property-read String $projectRegistrationAgentRelationGroupName Project Registration Agent Relation Group Name
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
     * @var \MapasCulturais\RouteManager
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
    protected $_config = [];

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
            'registration_fields' => []
        ];

    protected $_registerLocked = true;

    protected $_hooks = [];
    protected $_excludeHooks = [];


    protected $_accessControlEnabled = true;
    protected $_workflowEnabled = true;

    protected $_plugins = [];

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

        if($config['slim.debug'])
            error_reporting(E_ALL ^ E_STRICT);

        session_start();

        if($config['app.offline']){
            $bypass_callable = $config['app.offlineBypassFunction'];

            if(!is_callable($bypass_callable) || !$bypass_callable()){
                http_response_code(307);
                header('Location: ' . $config['app.offlineUrl']);
            }
        }

        // =============== CACHE =============== //
        if(key_exists('app.cache', $config) && is_object($config['app.cache'])  && is_subclass_of($config['app.cache'], '\Doctrine\Common\Cache\CacheProvider')){
            $this->_cache = $config['app.cache'];
        }else{
            $this->_cache = new \Doctrine\Common\Cache\ArrayCache ();
        }
        $this->_cache->setNamespace($config['app.cache.namespace']);

        spl_autoload_register(function($class) use ($config){
            $cache_id = "AUTOLOAD_CLASS:$class";
            if($config['app.useRegisteredAutoloadCache'] && $this->cache->contains($cache_id)){
                $path = $this->cache->fetch($cache_id);
                require_once $path;
                return true;
            }

            $namespaces = $config['namespaces'];

            foreach($config['plugins'] as $plugin){
                $dir = isset($plugin['path']) ? $plugin['path'] : PLUGINS_PATH . $plugin['namespace'];

                $namespaces[$plugin['namespace']] = $dir;
            }

            foreach($namespaces as $namespace => $base_dir){
                if(strpos($class, $namespace) === 0){
                    $path = str_replace('\\', '/', str_replace($namespace, $base_dir, $class) . '.php' );

                    if(\file_exists($path)){
                        require_once $path;
                        if($config['app.useRegisteredAutoloadCache'])
                            $this->cache->save($cache_id, $path, $config['app.registeredAutoloadCache.lifetime']);
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

        $theme_class = $config['themes.active'].'\Theme';

        parent::__construct([
            'log.level' => $config['slim.log.level'],
            'log.enabled' => $config['slim.log.enabled'],
            'debug' => $config['slim.debug'],
            'templates.path' => $this->_config['path.templates'],
            'view' => new $theme_class($config['themes.assetManager']),
            'mode' => $this->_config['app.mode']
        ]);

        foreach($config['plugins'] as $slug => $plugin){
            $_namespace = $plugin['namespace'];
            $_class = isset($plugin['class']) ? $plugin['class'] : 'Plugin';
            $plugin_class_name = "$_namespace\\$_class";

            $plugin_config = isset($plugin['config']) && is_array($plugin['config']) ? $plugin['config'] : [];

            $slug = is_numeric($slug) ? $_namespace : $slug;

            $this->_plugins[$slug] = new $plugin_class_name($plugin_config);
        }

        $config = $this->_config;

        // custom log writer
        if(isset($config['slim.log.writer']) && is_object($config['slim.log.writer']) && method_exists($config['slim.log.writer'], 'write')){
            $log = $this->getLog();
            $log->setWriter($config['slim.log.writer']);
        }


        // creates runtime cache component
        $this->_rcache = new \Doctrine\Common\Cache\ArrayCache ();

        // ===================================== //

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

        $proxy_dir = APPLICATION_PATH . 'lib/MapasCulturais/DoctrineProxies';
        $proxy_namespace = 'MapasCulturais\DoctrineProxies';

        $doctrine_config->setProxyDir($proxy_dir);
        $doctrine_config->setProxyNamespace($proxy_namespace);
        \Doctrine\ORM\Proxy\Autoloader::register($proxy_dir, $proxy_namespace);

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

        $doctrine_config->setMetadataCacheImpl($this->_cache);
        $doctrine_config->setQueryCacheImpl($this->_cache);
        $doctrine_config->setResultCacheImpl($this->_cache);


        // obtaining the entity manager
        $this->_em = EntityManager::create($config['doctrine.database'], $doctrine_config);

        \MapasCulturais\DoctrineMappings\Types\Frequency::register();

        \MapasCulturais\DoctrineMappings\Types\Point::register();
        \MapasCulturais\DoctrineMappings\Types\Geography::register();
        \MapasCulturais\DoctrineMappings\Types\Geometry::register();




        if(@$config['app.log.query'])
            $doctrine_config->setSQLLogger($config['app.queryLogger']);

        $this->_em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
        $this->_em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('geography', 'geography');
        $this->_em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'geometry');


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
        foreach($config['plugins.enabled'] as $plugin){
            if(file_exists(PLUGINS_PATH.$plugin.'.php')){
                include PLUGINS_PATH.$plugin.'.php';
            }
        }
        // ===================================== //

        if(defined('DB_UPDATES_FILE') && file_exists(DB_UPDATES_FILE))
            $this->_dbUpdates();
            
        //Load defaut translation textdomain
        i::load_default_textdomain();

        return $this;
    }

    public function run() {
        $this->applyHookBoundTo($this, 'mapasculturais.run:before');
        parent::run();
        $this->applyHookBoundTo($this, 'mapasculturais.run:after');
    }

    function isEnabled($entity){
        return $this->_config['app.enabled.' . $entity];
    }

    function enableAccessControl(){
        $this->_accessControlEnabled = true;
    }

    function disableAccessControl(){
        $this->_accessControlEnabled = false;
    }

    function isAccessControlEnabled(){
        return $this->_accessControlEnabled;
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

        if ($theme_seal_types = $this->view->resolveFilename('','seal-types.php')) {
            $seal_types = include $theme_seal_types;
        } else {
            $seal_types = include APPLICATION_PATH.'/conf/seal-types.php';
        }
        $seals_meta = key_exists('metadata', $seal_types) && is_array($seal_types['metadata']) ? $seal_types['metadata'] : [];

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

        $this->registerController('event',   'MapasCulturais\Controllers\Event');
        $this->registerController('agent',   'MapasCulturais\Controllers\Agent');
        $this->registerController('seal',   'MapasCulturais\Controllers\Seal');
        $this->registerController('space',   'MapasCulturais\Controllers\Space');
        $this->registerController('project', 'MapasCulturais\Controllers\Project');


        $this->registerController('app',   'MapasCulturais\Controllers\UserApp');

        $this->registerController('registration',                   'MapasCulturais\Controllers\Registration');
        $this->registerController('registrationFileConfiguration',  'MapasCulturais\Controllers\RegistrationFileConfiguration');
        $this->registerController('registrationFieldConfiguration', 'MapasCulturais\Controllers\RegistrationFieldConfiguration');

        $this->registerController('term',           'MapasCulturais\Controllers\Term');
        $this->registerController('file',           'MapasCulturais\Controllers\File');
        $this->registerController('metalist',       'MapasCulturais\Controllers\MetaList');
        $this->registerController('eventOccurrence','MapasCulturais\Controllers\EventOccurrence');

        //workflow controllers
        $this->registerController('notification', 'MapasCulturais\Controllers\Notification');


        $this->registerApiOutput('MapasCulturais\ApiOutputs\Json');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\Html');
        $this->registerApiOutput('MapasCulturais\ApiOutputs\Excel');

        // register registration field types

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'textarea',
            'name' => \MapasCulturais\i::__('Campo de texto (textarea)')
        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'text',
            'name' => \MapasCulturais\i::__('Campo de texto simples')
        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'date',
            'name' => \MapasCulturais\i::__('Campo de data')
        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'url',
            'name' => \MapasCulturais\i::__('Campo de URL (link)'),
            'validations' => [
                'v::url()' => \MapasCulturais\i::__('O valor não é uma URL válida')
            ]
        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'email',
            'name' => \MapasCulturais\i::__('Campo de email'),
            'validations' => [
                'v::email()' => \MapasCulturais\i::__('O valor não é um endereço de email válido')
            ]
        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'select',
            'name' => \MapasCulturais\i::__('Seleção única (select)'),
            'requireValuesConfiguration' => true
        ]));

//        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
//            'slug' => 'radio',
//            'name' => \MapasCulturais\i::__('Seleção única (radio)'),
//            'requireValuesConfiguration' => true
//        ]));

        $this->registerRegistrationFieldType(new Definitions\RegistrationFieldType([
            'slug' => 'checkboxes',
            'name' => \MapasCulturais\i::__('Seleção múltipla (checkboxes)'),
            'requireValuesConfiguration' => true,
            'serialize' => function (array $value) {
                return json_encode($value);
            },
            'unserialize' => function ($value) {
                return json_decode($value);
            }
        ]));

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
        $this->registerFileGroup('project', $file_groups['rules']);

        $this->registerFileGroup('seal', $file_groups['downloads']);
        $this->registerFileGroup('seal', $file_groups['header']);
        $this->registerFileGroup('seal', $file_groups['avatar']);
        $this->registerFileGroup('seal', $file_groups['gallery']);

        $this->registerFileGroup('registrationFileConfiguration', $file_groups['registrationFileConfiguration']);

        $image_transformations = include APPLICATION_PATH.'/conf/image-transformations.php';
        foreach($image_transformations as $name => $transformation)
            $this->registerImageTransformation($name, $transformation);


        // registration agent relations

        foreach($this->_config['registration.agentRelations'] as $config){
            $def = new Definitions\RegistrationAgentRelation($config);
            $projects_meta[$def->metadataName] = $def->getMetadataConfiguration();

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

        $this->registerMetaListGroup('seal', $metalist_groups['links']);
        $this->registerMetaListGroup('seal', $metalist_groups['videos']);

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

                $type_meta = $type_config['metadata'] = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];

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
            $type_config['metadata'] = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];

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
            $type_config['metadata'] = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];

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
            $type_config['metadata'] = key_exists('metadata', $type_config) && is_array($type_config['metadata']) ? $type_config['metadata'] : [];

            // add projects metadata definition to project type
            foreach($projects_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
        }

        // register seal time unit types
		$entity_class = 'MapasCulturais\Entities\Seal';

        foreach($seal_types['items'] as $type_id => $type_config){
        	$type = new Definitions\EntityType($entity_class, $type_id, $type_config['name']);
        	$this->registerEntityType($type);

        	// add projects metadata definition to project type
            foreach($seals_meta as $meta_key => $meta_config)
                if(!key_exists($meta_key, $type_meta) || key_exists($meta_key, $type_meta) && is_null($type_config['metadata'][$meta_key]))
                    $type_config['metadata'][$meta_key] = $meta_config;

            foreach($type_config['metadata'] as $meta_key => $meta_config){
                $metadata = new Definitions\Metadata($meta_key, $meta_config);
                $this->registerMetadata($metadata, $entity_class, $type_id);
            }
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

        foreach($this->_plugins as $plugin){
            $plugin->register();
        }

        $this->applyHookBoundTo($this, 'app.register',[&$this->_register]);
    }



    function getRegisteredGeoDivisions(){
        $result = [];
        foreach($this->_config['app.geoDivisionsHierarchy'] as $key => $name) {
            $d = new \stdClass();
            $d->key = $key;
            $d->name = $name;
            $d->metakey = 'geo' . ucfirst($key);
            $result[] = $d;
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
                $_FILES[$key]['name'] = $this->sanitizeFilename($_FILES[$key]['name']);
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
    function sanitizeFilename($filename){
        $filename = str_replace(' ','_', strtolower($filename));
        if(is_callable($this->_config['app.sanitize_filename_function'])){
            $cb = $this->_config['app.sanitize_filename_function'];
            $filename = $cb($filename);
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
    function hook($name, $callable, $priority = 10) {
        $this->_hookCache = [];
        $_hooks = explode(',', $name);
        foreach ($_hooks as $hook) {
            if (trim($hook)[0] === '-') {
                $hook = $this->_compileHook($hook);
                if (!key_exists($hook, $this->_excludeHooks))
                    $this->_excludeHooks[$hook] = [];

                $this->_excludeHooks[$hook][] = $callable;
            }else {
                $hook = $this->_compileHook($hook);

                if (!key_exists($hook, $this->_hooks))
                    $this->_hooks[$hook] = [];

                if (!key_exists($priority, $this->_hooks[$hook]))
                    $this->_hooks[$hook][$priority] = [];

                $this->_hooks[$hook][$priority][] = $callable;

                ksort($this->_hooks[$hook]);
            }
        }
    }

    /**
     * Invoke hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     */
    function applyHook($name, $hookArg = null) {
        if (is_null($hookArg))
            $hookArg = [];
        else if (!is_array($hookArg))
            $hookArg = [$hookArg];

        if ($this->_config['app.log.hook'])
            $this->log->debug('APPLY HOOK >> ' . $name);

        $callables = $this->_getHookCallables($name);
        foreach ($callables as $callable) {
            call_user_func_array($callable, $hookArg);
        }
    }

    /**
     * Invoke hook biding callbacks to the target object
     *
     * @param  object $target_object Object to bind hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     */
    function applyHookBoundTo($target_object, $name, $hookArg = null) {
        if (is_null($hookArg))
            $hookArg = [];
        else if (!is_array($hookArg))
            $hookArg = [$hookArg];

        if ($this->_config['app.log.hook'])
            $this->log->debug('APPLY HOOK BOUND TO >> ' . $name);

        $callables = $this->_getHookCallables($name);
        foreach ($callables as $callable) {
            $callable = \Closure::bind($callable, $target_object);
            call_user_func_array($callable, $hookArg);
        }
    }


    function _getHookCallables($name) {
        $exclude_list = [];
        $result = [];

        foreach ($this->_excludeHooks as $hook => $callables) {
            if (preg_match($hook, $name))
                $exclude_list = array_merge($callables);
        }

        foreach ($this->_hooks as $hook => $_callables) {
            if (preg_match($hook, $name)) {
                foreach ($_callables as $callables) {
                    foreach ($callables as $callable) {
                        if (!in_array($callable, $exclude_list))
                            $result[] = $callable;
                    }
                }
            }
        }

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
     * Getters
     **********************************************/

    public function getMaxUploadSize($useSuffix=true){
        $MB = 1024;
        $GB = $MB * 1024;

        $convertToKB = function($size) use($MB, $GB){
            switch(strtolower(substr($size, -1))){
                case 'k';
                    $size = (int) $size;
                break;

                case 'm':
                    $size = $size * $MB;
                break;

                case 'g':
                    $size = $size * $GB;
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

    public function getProjectRegistrationAgentRelationGroupName(){
        return key_exists('app.projectRegistrationAgentRelationGroupName', $this->_config) ?
                $this->_config['app.projectRegistrationAgentRelationGroupName'] : 'registration';
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
     * @return \MapasCulturais\View
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
     * @return \Doctrine\ORM\EntityRepository the Entity Repository
     */
    public function repo($name){

        // add MapasCulturais\Entities namespace if no namespace in repo name
        if(strpos($name, '\\') === false)
                $name = "\MapasCulturais\Entities\\{$name}";

        return $this->em->getRepository($name);
    }


    /**********************************************
     * Register
     **********************************************/

    public function registerRole($role){

    }

    public function getRoles() {
        $roles = include APPLICATION_PATH . 'conf/roles.php';
        return $roles;
    }

    public function getRoleName($role){
        $roles = $this->getRoles();
        return key_exists($role, $roles) ? $roles[$role]['name'] : $role;
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
            return $class::i();
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
            return $controller_class::i();
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
        return @$this->_register['entity_types'][$object->getClassName()][$object->type];
    }



    /**
     * Returns the Entity Type of the given entity class or object.
     *
     * @param \MapasCulturais\Entity|string $entity The entity.
     *
     * @return \MapasCulturais\Definitions\EntityType
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

    function unregisterEntityMetadata($entity_class){
        foreach(array_keys($this->_register['entity_metadata_definitions']) as $k)
            if($k == $entity_class || strpos($k, $entity_class.':') === 0)
                $this->_register['entity_metadata_definitions'][$k] = [];

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

        if(!in_array('mailer',$this->_config['plugins.enabled'])) {
            return;
        }

        if(isset($this->_config['mailer.user']) &&
            isset($this->_config['mailer.psw']) &&
            isset($this->_config['mailer.server']) &&
            isset($this->_config['mailer.port']) &&
            isset($this->_config['mailer.protocol'])) {
            $transport = \Swift_SmtpTransport::newInstance($this->_config['mailer.server'],
                                                            $this->_config['mailer.port'],
                                                            $this->_config['mailer.protocol'])
                                                            ->setUsername($this->_config['mailer.user'])
                                                            ->setPassword($this->_config['mailer.psw']);
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

        foreach($args as $key => $value){
            $key = ucfirst($key);
            $method_name = 'set' . $key;

            if(method_exists($message, $method_name)){
                $message->$method_name($value);
            }
        }

        return $message;
    }

    function sendMailMessage(\Swift_Message $message){
        $failures = [];
        $mailer = $this->getMailer();

        if(in_array('mailer',$this->_config['plugins.enabled']) && !$mailer->send($message,$failures)) {
            App::i()->log->debug($failures);
        }
    }

    function createAndSendMailMessage(array $args = []){
        $message = $this->createMailMessage($args);
        $this->sendMailMessage($message);
    }


    /**************
     * GetText
     **************/

    static function getCurrentLCode(){
        return App::i()->_config['app.lcode'];
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
