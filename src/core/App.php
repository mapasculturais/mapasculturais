<?php
declare(strict_types=1);

namespace MapasCulturais;

use DateTime;
use Slim\Factory\AppFactory;
use Slim;


use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\Common\Annotations\AnnotationReader;
use ErrorException;
use LogicException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\NotSupported;
use InvalidArgumentException;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Entities\Job;

use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Exception as GlobalException;
use Doctrine\Persistence\Mapping\MappingException;
use MapasCulturais\Definitions\ChatThreadType;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Definitions\RegistrationAgentRelation;
use MapasCulturais\Definitions\RegistrationFieldType;
use MapasCulturais\Entities\Subsite;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\MailTemplateNotFound;
use MapasCulturais\Exceptions\NotFound;
use MapasCulturais\Exceptions\WorkflowRequest;
use ReflectionException;
use RuntimeException;
use Slim\App as SlimApp;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler;
use Monolog\Level;
use Monolog\Logger;

use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Log\InvalidArgumentException as LogInvalidArgumentException;
use Respect\Validation\Factory as RespectorValidationFactory;
use Symfony\Component\Mailer\Exception\InvalidArgumentException as ExceptionInvalidArgumentException;
use Symfony\Component\Mailer\Exception\LogicException as ExceptionLogicException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use TypeError;
use Throwable;

/**
 * @property-read string $id id da aplicação
 * @property-read Slim\App $slim instância do Slim
 * @property-read Hooks $hooks gerenciador de hooks
 * @property-read EntityManager $em Doctrine Entity Manager
 * @property-read AuthProvider $auth provedor de autenticação
 * @property-read string $siteName nome do site
 * @property-read string $siteDescription descrição do site
 * @property-read Subsite $subsite Subsite atual
 * @property-read Subsite $currentSubsite Subsite atual
 * @property-read string $currentLCode código da linguagem configurada. ex: pt_BR
 * @property-read int|null $currentSubsiteId id do subsite atual
 * @property-read string|float|int $maxUploadSize tamanho máximo de arquivo para upload aceito pelo PHP
 * @property-read array $registeredGeoDivisions divisões geográficas configuradas
 * @property-read Definitions\Role[] $roles roles registrados 
 * @property-read Definitions\JobType[] registeredJobTypes lista de job tipes registrados
 * @property-read Definitions\RegistrationAgentRelation[] $registrationAgentsDefinitions Retorna as definições dos agentes relacionados das inscrições
 * @property-read Definitions\RegistrationAgentRelation[] $registeredRegistrationAgentRelations definições de agentes relacionados de inscrições registrados
 * @property-read Definitions\RegistrationAgentRelation $registrationOwnerDefinition definição do agente owner de inscrição
 * @property-read Definitions\ChatThreadType $registeredChatThreadTypes definições dos tipos de chat registrados
 
 * @property-read User $user usuário autenticado

 * 
 * @package MapasCulturais
 */
class App {
    use Traits\MagicCallers,
        Traits\MagicGetter,
        Traits\MagicSetter;

    /**
     * Array de instâncias de aplicação
     * @var App[]
     */
    protected static array $_instances = [];

    /**
     * Id da aplicação
     * @var string
     */
    protected string $id;

    /**
     * Instância do Slim
     * @var SlimApp
     */
    protected Slim\App $slim;

    /**
     * O Entity Manager do Doctrine
     * @var EntityManager
     */
    public EntityManager $em;

    /**
     * Hooks
     * @var Hooks
     */
    public Hooks $hooks;

    /**
     * Gerenciador de armazenamento de arquivos
     * @var Storage
     */
    public Storage $storage;

    /**
     * Instância do tema
     * @var Theme
     */
    public Theme $view;

    /**
     * Instância do subsite ativo
     * @var Entities\Subsite|null
     */
    protected Entities\Subsite|null $subsite = null;

    /**
     * Instância dos módulos ativos
     * @var Module[]
     */
    public array $modules = [];

    /**
     * Instância dos plugins ativos
     * @var Plugin[]
     */
    public array $plugins = [];

    /**
     * Provedor de autenticação
     * @var AuthProvider
     */
    protected AuthProvider $auth;

    /**
     * Serviço de log
     * @var Logger
     */
    public Logger $log;
    
    /**
     * Persistent Cache
     * @var Cache
     */
    public Cache $cache;

    /**
     * Multisite Persistent Cache
     * @var Cache
     */
    public Cache $mscache;

    /**
     * Runtime Cache
     * @var Cache
     */
    public Cache $rcache;
    
    /**
     * App Configuration.
     * @var array
     */
    public array $config;
    
    /**
     * Alias da prop config para compatibilidade
     * @var array
     */
    public array $_config;

    /**
     * The Application Registry.
     *
     * Here is stored the registered controllers, entity types, entity type groups, 
     * entity metadata definitions, file groups definitions and taxonomy definitions.
     *
     * @var array
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

    /**
     * The Route Manager.
     * @var RoutesManager
     */
    protected $_routesManager = null;


    /**
     * Lista de entidades para colocar na fila de processamento de cache de permissão
     * 
     * @var Entity[]
     */
    private $_permissionCachePendingQueue = [];


    /**
     * Lista das entidades já processadas pelo job recria os caches de permissão
     * @var array
     */
    private $_recreatedPermissionCacheList = [];


    /** FLAGS */

    /**
     * Contador de vezes que o disableAccessControl foi chamado
     * @var int
     */
    protected int $_disableAccessControlCount = 0;

    /**
     * Contador de vezes que o disableWorkflow foi chamado
     * @var int
     */
    protected int $_disableWorkflowCount = 0;

    /**
     * Indica se o registro da aplicação já foi executado
     * 
     * @var bool
     */
    private bool $_registered = false;

    /**
     * Hibilita os magic getter hooks
     */
    protected $__enableMagicGetterHook = true;

    /**
     * Objeto da requisição atual
     * @var Request
     */
    public Request $request;

    /**
     * Objeto que será a resposta final do gerenciador de rotas
     * @var ResponseInterface
     */
    public ResponseInterface $response;

    /**
     * Microtime do momento do instanciamento da aplicação
     * 
     * @var float
     */
    public float $startTime;
    
    /**
     * Retorna uma instância da aplicação
     * 
     * @param string $id 
     * @return App 
     */
    static function i(string $id = 'web'): App {
        $class = get_called_class();
        if (!(self::$_instances[$id] ?? null)) {
            self::$_instances[$id] = new $class($id);;
        }
        return self::$_instances[$id];
    }

    /**
     * Construtor da aplicação
     * 
     * @param string $id 
     * @return void 
     * @throws RuntimeException 
     */
    protected function __construct(string $id) {
        $this->startTime = microtime(true);
        $this->id = $id;

        $this->slim = AppFactory::create();
        $this->slim->addBodyParsingMiddleware();
        $this->slim->addRoutingMiddleware();

        $display_error_detail = \env('DISPLAY_ERROR_DETAIL', false);
        $log_errors = \env('LOG_ERRORS', true);
        $log_error_details = \env('LOG_ERROR_DETAIL', true);

        $error_middleware = $this->slim->addErrorMiddleware($display_error_detail, $log_errors, $log_error_details);
        $error_handler = $error_middleware->getDefaultErrorHandler();

        ErrorHandler::$defaultErrorHandler = $error_handler;

        $error_middleware->setDefaultErrorHandler(ErrorHandler::class);

        $error_handler->registerErrorRenderer('text/html', ErrorRender::class);
        $error_handler->registerErrorRenderer('application/json', ErrorRender::class);
        $error_handler->registerErrorRenderer('*/*', ErrorRender::class);

        $this->hooks = new Hooks($this);
    }

    /**
     * Analisa e se necessário corrige o array de configuração da aplicação
     * 
     * @param array $config 
     * @return array 
     */
    function parseConfig(array $config): array {
        if(empty($config['base.url'])){
            $config['base.url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . 
                                  (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/';
        }

        if(empty($config['base.assetUrl'])){
            $config['base.assetUrl'] = $config['base.url'] . 'assets/';
        }

        if(!is_array($config['app.verifiedSealsIds'])) {
            if (is_numeric($config['app.verifiedSealsIds'])) { 
                $config['app.verifiedSealsIds'] = [(int) $config['app.verifiedSealsIds']];
            } else {
                $config['app.verifiedSealsIds'] = [];
            }
        }

        return $config;
    }

    /**
     * Inicializa a aplicação
     * 
     * @param array $config array de configuração da aplicação
     * 
     * @return App 
     * 
     * @throws RuntimeException 
     * @throws ErrorException 
     * @throws LogicException 
     * @throws Exception 
     */
    function init(array $config) {
        $config = $this->parseConfig($config);
        
        $this->_config = &$config;
        $this->config = &$config;

        foreach($this->config['middlewares'] as $middleware) {
            $this->slim->add($middleware);
        }

        // necessário para obter o endereço ip da origem do request
        $this->slim->add(new \RKA\Middleware\IpAddress);

        if($config['app.mode'] == APPMODE_DEVELOPMENT){
            error_reporting(E_ALL ^ E_STRICT);
        }

        session_save_path(SESSIONS_SAVE_PATH);
        session_start();

        if($config['app.offline']){
            $bypass_callable = $config['app.offlineBypassFunction'];
            
            if (php_sapi_name()!=="cli" && (!is_callable($bypass_callable) || !$bypass_callable())) {
                http_response_code(307);
                header('Location: ' . $config['app.offlineUrl']);
                die;
            }
        }

        // inicializa os validadores customizados
        $instance = RespectorValidationFactory::getDefaultInstance();
        $instance = $instance->withRuleNamespace('MapasCulturais\\Validators\\Rules');
        $instance = $instance->withExceptionNamespace('MapasCulturais\\Validators\\Exceptions');
        RespectorValidationFactory::setDefaultInstance($instance);

        $this->_initLogger();
        $this->_initAutoloader();
        $this->_initCache();
        $this->_initDoctrine();
        
        $this->_initSubsite();

        $this->_initRouteManager();
        $this->_initAuthProvider();

        $this->_initTheme();

        $this->applyHookBoundTo($this, 'app.init:before');

        $this->_initPlugins();
        $this->_initModules();

        $this->applyHookBoundTo($this, 'mapasculturais.init');

        // chama o registro da aplicação
        $this->register();

        // chama o inicializador do tema ativo
        $this->view->init();

        $this->_initStorage();

        if(defined('DB_UPDATES_FILE') && file_exists(DB_UPDATES_FILE))
            $this->_dbUpdates();

        $this->applyHookBoundTo($this, 'app.init:after');
        return $this;
    }

    /**
     * Executa a aplicação
     * 
     * Basicamente esta função executa o slim->run() e 
     * persiste a fila de recriação de cache
     * 
     * @return void 
     * @throws RuntimeException 
     * @throws InvalidArgumentException 
     * @throws PermissionDenied 
     * @throws ORMInvalidArgumentException 
     * @throws ORMException 
     * @throws OptimisticLockException 
     * @throws TransactionRequiredException 
     * @throws WorkflowRequest 
     */
    public function run() {
        $this->applyHookBoundTo($this, 'mapasculturais.run:before');
        $this->slim->run();
        $this->persistPCachePendingQueue();
        $this->applyHookBoundTo($this, 'mapasculturais.run:after');
        $this->applyHookBoundTo($this, 'slim.after');
    }

    /**
     * Inicializa o monolog
     * 
     * @return void 
     */
    protected function _initLogger() {
        $processors = $this->config['monolog.processors'];
        
        $handlers = [];
        if (is_string($this->config['monolog.handlers'])) {
            $handlers_config = explode(',', $this->config['monolog.handlers']);
    
            foreach($handlers_config as $handler_config) {
                $handler_config = explode(':', $handler_config);
    
                $type = $handler_config[0];
                $level = $handler_config[1] ?? $this->config['monolog.defaultLevel'];
    
                if ($type == 'file') {
                    $handlers[] = new Handler\StreamHandler($this->config['monolog.logsDir'] . 'app.log', 'DEBUG');

                } else if ($type == 'error_log') {
                    $formatter = new LineFormatter("%message%");
                    $handler = new Handler\ErrorLogHandler(level: $level);
                    $handler->setFormatter($formatter);
                    $handlers[] = $handler;

                } else if ($type == 'browser') {
                    $formatter = new LineFormatter("%message%");
                    $handler = new Handler\BrowserConsoleHandler(level: $level);
                    $handler->setFormatter($formatter);
                    $handlers[] = $handler;

                } else if ($type == 'telegram') {
                    $api_key = (string) $this->config['monolog.telegram.apiKey'] ?? false;
                    $channel_id = (string) $this->config['monolog.telegram.channelId'] ?? false;
                    
                    if($api_key && $channel_id) {
                        $handler = new Handler\TelegramBotHandler($api_key, $channel_id, $level, parseMode:'Markdown');
                        
                        $formatter = new LineFormatter("%message%", includeStacktraces: true);
                        
                        $handler->setFormatter($formatter);
                        
                        $handlers[] = $handler;
                    }
                }
            }
        } else if (is_array($this->config['monolog.handlers'])) {
            $handlers = $this->config['monolog.handlers'];
        }

        $this->log = new Logger('', $handlers, $processors);

        if ($this->config['app.log.query']) {
            $this->hook('app.init:after', function() use($handlers) {
                $query_logger = new QueryLogger;
                $this->em->getConnection()->getConfiguration()->setSQLLogger($query_logger);
            });
        }
    }

    /**
     * Inicializa o autoloader de classes
     * 
     * @return void 
     */
    protected function _initAutoloader() {
        $config = &$this->config;

        // list of modules
        if($handle = opendir(MODULES_PATH)){
            while (false !== ($file = readdir($handle))) {
                $dir = MODULES_PATH . $file . '/';
                if ($file != "." && $file != ".." && is_dir($dir) && file_exists("$dir/Module.php")) {
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

        spl_autoload_register(function($class) use (&$config){
            $namespaces = $config['namespaces'];

            $namespaces['MapasCulturais\\DoctrineProxies'] = DOCTRINE_PROXIES_PATH;

            $subfolders = ['Controllers','Entities','Repositories','Jobs'];

            foreach($config['plugins'] as $key => &$plugin){
                if(is_array($plugin) && isset($plugin['namespace'])) {
                    // do nothing
                } else if (is_string($key) && is_array($plugin) && !isset($plugin['namespace'])) {
                    $plugin = ['namespace' => $key, 'config' => $plugin];
                } else if (is_numeric($key) && is_string($plugin)) {
                    $plugin = ['namespace' => $plugin];
                }
                $namespace = $plugin['namespace'];
                $dir = $plugin['path'] ?? PLUGINS_PATH . $namespace;
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
                        return true;
                    }
                }
            }
        });

        $this->config = $config;
    }

    /**
     * Inicializa os gerenciadores de cache da aplicação,
     * como configurado nas chaves `app.cache` e `app.mscache`
     * 
     * @return void 
     */
    protected function _initCache() {
        $this->config['app.registeredAutoloadCache.lifetime'] = (int) $this->config['app.registeredAutoloadCache.lifetime'];
        $this->config['app.assetsUrlCache.lifetime'] = (int) $this->config['app.assetsUrlCache.lifetime'];
        $this->config['app.fileUrlCache.lifetime'] = (int) $this->config['app.fileUrlCache.lifetime'];
        $this->config['app.eventsCache.lifetime'] = (int) $this->config['app.eventsCache.lifetime'];
        $this->config['app.subsiteIdsCache.lifetime'] = (int) $this->config['app.subsiteIdsCache.lifetime'];
        $this->config['app.permissionsCache.lifetime'] = (int) $this->config['app.permissionsCache.lifetime'];
        $this->config['app.registerCache.lifeTime'] = (int) $this->config['app.registerCache.lifeTime'];
        $this->config['app.apiCache.lifetime'] = (int) $this->config['app.apiCache.lifetime'];
        $this->config['app.opportunitySummaryCache.lifetime'] = (int) $this->config['app.opportunitySummaryCache.lifetime'];

        $this->cache = new Cache($this->config['app.cache']);
        $this->mscache = new Cache($this->config['app.mscache']);
        $this->mscache->setNamespace('MS');
        
        $rcache_adapter = new \Symfony\Component\Cache\Adapter\ArrayAdapter(10000, false, 100000, 10000);
        $this->rcache = new Cache($rcache_adapter);
    }

    /**
     * Inicializa o Doctrine
     * 
     * @return void 
     * @throws RuntimeException 
     * @throws ErrorException 
     * @throws LogicException 
     * @throws Exception 
     */
    protected function _initDoctrine() {
        // annotation driver
        $doctrine_config = ORMSetup::createAnnotationMetadataConfiguration(
            paths: [__DIR__ . '/Entities/'],
            isDevMode: (bool) $this->config['doctrine.isDev'],
            cache: $this->cache->adapter
        );

        

        // tells the doctrine to ignore hook annotation.
        AnnotationReader::addGlobalIgnoredName('hook');

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
        $doctrine_config->addCustomStringFunction('st_geomfromtext', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STGeomFromText');
        $doctrine_config->addCustomNumericFunction('st_length', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STLength');
        $doctrine_config->addCustomNumericFunction('st_linecrossingdirection', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STLineCrossingDirection');
        $doctrine_config->addCustomStringFunction('st_startpoint', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STStartPoint');
        $doctrine_config->addCustomStringFunction('st_summary', 'CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STSummary');


        $doctrine_config->addCustomStringFunction('string_agg', 'MapasCulturais\DoctrineMappings\Functions\StringAgg');
        $doctrine_config->addCustomStringFunction('unaccent', 'MapasCulturais\DoctrineMappings\Functions\Unaccent');
        $doctrine_config->addCustomStringFunction('recurring_event_occurrence_for', 'MapasCulturais\DoctrineMappings\Functions\RecurringEventOcurrenceFor');
        $doctrine_config->addCustomNumericFunction('CAST', DoctrineMappings\Functions\Cast::class);
        $doctrine_config->addCustomStringFunction('CAST', DoctrineMappings\Functions\Cast::class);
        
        $doctrine_config->addCustomNumericFunction('st_dwithin', 'MapasCulturais\DoctrineMappings\Functions\STDWithin');
        $doctrine_config->addCustomStringFunction('st_envelope', 'MapasCulturais\DoctrineMappings\Functions\STEnvelope');
        $doctrine_config->addCustomNumericFunction('st_within', 'MapasCulturais\DoctrineMappings\Functions\STWithin');
        $doctrine_config->addCustomNumericFunction('st_makepoint', 'MapasCulturais\DoctrineMappings\Functions\STMakePoint');

        $metadata_cache_adapter = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter();
        $doctrine_config->setMetadataCache($metadata_cache_adapter);
        $doctrine_config->setQueryCache($this->mscache->adapter);
        $doctrine_config->setResultCache($this->mscache->adapter);

        $doctrine_config->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
        
        // obtaining the entity manager
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'dbname' => $this->config['db.dbname'],
            'user' => $this->config['db.user'],
            'password' => $this->config['db.password'],
            'host' => $this->config['db.host'],
            'wrapperClass' => Connection::class
        ], $doctrine_config);
        
        
        // obtaining the entity manager
        $this->em = new EntityManager($connection, $doctrine_config);

        DoctrineMappings\Types\Frequency::register();
        DoctrineMappings\Types\Point::register();
        DoctrineMappings\Types\Geography::register();
        DoctrineMappings\Types\Geometry::register();

        \Acelaya\Doctrine\Type\PhpEnumType::registerEnumTypes([
            DoctrineEnumTypes\ObjectType::getTypeName() => DoctrineEnumTypes\ObjectType::class,
            DoctrineEnumTypes\PermissionAction::getTypeName() => DoctrineEnumTypes\PermissionAction::class
        ]);

        $platform = $this->em->getConnection()->getDatabasePlatform();

        $platform->registerDoctrineTypeMapping('_text', 'text');
        $platform->registerDoctrineTypeMapping('point', 'point');
        $platform->registerDoctrineTypeMapping('geography', 'geography');
        $platform->registerDoctrineTypeMapping('geometry', 'geometry');
        $platform->registerDoctrineTypeMapping('object_type', 'object_type');
        $platform->registerDoctrineTypeMapping('permission_action', 'permission_action');
    }

    /**
     * Inicializa o subsite ativo
     * 
     * @return void 
     */
    protected function _initSubsite($domain = null) {
        if (!$domain){
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        }

        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }

        // para permitir o db update rodar para criar a tabela do subsite
        if(($pos = strpos($domain, ':')) !== false){
            $domain = substr($domain, 0, $pos);
        }
        try{
            $subsite = $this->repo('Subsite')->findOneBy(['url' => $domain, 'status' => 1]) ?:
                       $this->repo('Subsite')->findOneBy(['aliasUrl' => $domain, 'status' => 1]);

            if ($subsite){
                $this->subsite = $subsite;
            }
        } catch ( \Exception $e) { }


        $this->hook('app.init:after', function () {
            if($this->subsite){
                $this->subsite->applyApiFilters();
                $this->subsite->applyConfigurations();
            }
        });
    }

    /**
     * Inicializa o provedor de autenticação
     * @return void 
     */
    protected function _initAuthProvider() {
        // register auth providers
        $this->registerAuthProvider('OpenID');
        $this->registerAuthProvider('logincidadao');
        $this->registerAuthProvider('authentik');
        
        $auth_class_name = strpos($this->config['auth.provider'], '\\') !== false ? 
            $this->config['auth.provider'] : 
            'MapasCulturais\AuthProviders\\' . $this->config['auth.provider'];
        $auth = new $auth_class_name($this->config['auth.config']);
        
        $auth->setCookies();
        
        $this->auth = $auth;
    }

    /**
     * Inicializa a instância do tema
     * @return void 
     */
    protected function _initTheme() {

        if($this->subsite){
            $this->cache->setNamespace($this->config['app.cache.namespace'] . ':' . $this->subsite->id);

            $theme_class = $this->subsite->namespace . "\Theme";
            $theme_instance = new $theme_class($this->config['themes.assetManager'], $this->subsite);
        } else {
            $theme_class = $this->config['themes.active'] . '\Theme';

            // dd($theme_class);

            $theme_instance = new $theme_class($this->config['themes.assetManager']);
        }

        $theme_path = $theme_class::getThemeFolder() . '/';

        if (file_exists($theme_path . 'conf-base.php')) {
            $theme_config = require $theme_path . 'conf-base.php';
            $this->config = array_merge($this->config, $theme_config);
        }

        if (file_exists($theme_path . 'config.php')) {
            $theme_config = require $theme_path . 'config.php';
            $this->config = array_merge($this->config, $theme_config);
        }

        $this->view = $theme_instance;
    }

    /**
     * Inicializa os módulos
     * 
     * @hook app.modules.init:before
     * @hook app.module({$module}).init:before
     * @hook app.module({$module}).init:after
     * @hook app.modules.init:after
     * 
     * @return void 
     */
    protected function _initModules() {
        $available_modules = [];
        if($handle = opendir(MODULES_PATH)){
            while (false !== ($file = readdir($handle))) {
                $dir = MODULES_PATH . $file . '/';
                if ($file != "." && $file != ".." && is_dir($dir) && file_exists($dir."/Module.php")) {
                    $available_modules[] = $file;
                }
            }
            closedir($handle);
        }

        sort($available_modules);

        $this->applyHookBoundTo($this, 'app.modules.init:before', [&$available_modules]);
        foreach ($available_modules as $module){
            $module_class_name = "$module\Module";
            $module_config = isset($config["module.$module"]) ? 
            $config["module.$module"] : [];
            
            $this->applyHookBoundTo($this, "app.module({$module}).init:before", [&$module_config]);
            $this->modules[$module] = new $module_class_name($module_config);
            $this->applyHookBoundTo($this, "app.module({$module}).init:after");
        }
        $this->applyHookBoundTo($this, 'app.modules.init:after');
    }

    /**
     * Inicializa os plugins
     * 
     * @hook app.plugins.init:before
     * @hook app.plugins.init:after
     * 
     * @return void 
     */
    protected function _initPlugins() {
        // esta constante é usada no script que executa os db-updates, 
        // para que na primeira rodada do db-update não sejam incluídos os plugins
        if(!env('DISABLE_PLUGINS')) {
            $this->applyHookBoundTo($this, 'app.plugins.preInit:before');
            $plugins = [];

            foreach($this->config['plugins'] as $slug => $plugin){
                if (is_numeric($slug) && is_string($plugin)) {
                    $_namespace = $plugin;
                    $slug = $plugin;
                    $plugin_class_name = "$_namespace\\Plugin";
                } else {
                    $_namespace = $plugin['namespace'];
                    $_class = isset($plugin['class']) ? $plugin['class'] : 'Plugin';
                    $plugin_class_name = "$_namespace\\$_class";
                }

                if(class_exists($plugin_class_name)){
                    $plugin_config = isset($plugin['config']) && is_array($plugin['config']) ? $plugin['config'] : [];

                    $slug = is_numeric($slug) ? $_namespace : $slug;

                    $plugins[] = [
                        'slug' => $slug,
                        'class' => $plugin_class_name,
                        'config' => $plugin_config
                    ];

                    $plugin_class_name::preInit();
                }
            }

            $this->applyHookBoundTo($this, 'app.plugins.preInit:after', ['plugins' => &$plugins]);

            $this->hook('app.modules.init:after', function() use ($plugins) {
                $this->applyHookBoundTo($this, 'app.plugins.init:before');
                foreach ($plugins as $plugin) {
                    $slug = $plugin['slug'];
                    $plugin_class_name = $plugin['class'];
                    $plugin_config = $plugin['config'];

                    $this->plugins[$slug] = new $plugin_class_name($plugin_config);
                }
                $this->applyHookBoundTo($this, 'app.plugins.init:after');
            });
        }
    }    

    /**
     * Inicializa o gerenciador de rotas
     * @return void 
     */
    protected function _initStorage() {
        $storage_class = $this->config['storage.driver'] ?? '';
        if($storage_class && class_exists($storage_class) && is_subclass_of($storage_class, Storage::class)){
            $storage_config = $this->config['storage.config'] ?? null;
            $this->storage =  $storage_class::i($storage_config);
        }else{
            $this->storage = Storage\FileSystem::i();
        }
    }

    /**
     * Inicializa o gerenciador de rotas
     * @return void 
     */
    protected function _initRouteManager() {
        $this->_routesManager = new RoutesManager;
    }



    /**************************************
     *              SETTERS 
     **************************************/

    /**
     * Define o subsite pelo id
     * 
     * @param int|null $subsite_id 
     * @return void 
     * @throws NotSupported 
     * @throws GlobalException 
     */
    public function setCurrentSubsiteId(int $subsite_id = null) {
        if(is_null($subsite_id)) {
            $this->subsite = null;
        } else {
            $subsite = $this->repo('Subsite')->find($subsite_id);

            if(!$subsite) {
                throw new \Exception('Subsite not found');
            }

            $this->subsite = $subsite;
        }
    }


    /**************************************
     *              GETTERS 
     **************************************/

    /**
     * Retorna o prefixo dos hooks
     * 
     * @return string 
     */
    public static function getHookPrefix() {
        return 'App';
    }

    /**
     * Retorna a versão do core da aplicação
     * 
     * @return string 
     */
    public function getVersion(){
        $version_filename = PROTECTED_PATH . 'version.txt';
        $version = trim(file_get_contents($version_filename));
        if(is_numeric($version[0])) {
            return sprintf('v%s', $version);
        } else {
            return $version;
        }
    }

    /**
     * Retorna o nome do site
     * 
     * configurado pela chave `app.siteName`
     * 
     * @return string 
     */
    public function getSiteName(): string {
        return $this->config['app.siteName'];
    }

    /**
     * Retorna a descrição do site
     * 
     * configurado pela chave `app.siteDescription`
     * 
     * @return string 
     */
    public function getSiteDescription(): string {
        return $this->config['app.siteDescription'];
    }
    
    /**
     * Returns the RoutesManager
     * @return RoutesManager
     */
    public function getRoutesManager(): RoutesManager{
        return $this->_routesManager;
    }

    /**
     * Returns the base url of the project
     * @return string the base url
     */
    public function getBaseUrl(){
        return $this->config['base.url'];
    }

    /**
     * Returns the asset url of the project
     * @return string the asset url
     */
    public function getAssetUrl(){
        return isset($this->config['base.assetUrl']) ? $this->config['base.assetUrl'] : $this->getBaseUrl() . 'assets/';
    }

    /**
     * Returns the logged in user
     * @return UserInterface
     */
    public function getUser(): UserInterface {
        return $this->auth->getAuthenticatedUser();
    }

    /**
     * Instância do subsite atual
     * 
     * @return Entities\Subsite|null 
     */
    public function getCurrentSubsite(): Entities\Subsite|null {
        return $this->subsite;
    }

    /**
     * Returns the current subsite ID, or null if current site is the main site
     *
     * @return int|null ID of the current site or Null for main site
     */
    public function getCurrentSubsiteId(): int|null {
        // @TODO: alterar isto quando for implementada a possibilidade de termos 
        // instalações de subsite com o tema diferente do Subsite
        if($this->subsite){
            return $this->subsite->id;
        }

        return null;
    }

    /**
     * Retorna o tamanho máximo de upload configurado no PHP
     * 
     * @param bool $useSuffix 
     * @return mixed 
     */
    public function getMaxUploadSize(bool $useSuffix = true): string|float|int {
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

        if ($memory_limit == -1) {
            $result = min($max_upload, $max_post);
        } else {
            $result = min($max_upload, $max_post, $memory_limit);
        }

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

    /**
     * Retorna as divisões geográficas (geoDivisions) configuradas
     * 
     * @return array 
     */
    function getRegisteredGeoDivisions(): array {
        $result = [];
        foreach($this->config['app.geoDivisionsHierarchy'] as $key => $division) {

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
     * Retorna o código de linguagem atual
     * 
     * @return string 
     */
    static function getCurrentLCode(): string{
        return i::get_locale();
    }

    /**
     * Retorna o nome do grupo de agente relacionado das inscrições
     * @return string 
     */
    public function getOpportunityRegistrationAgentRelationGroupName(): string {
        return 'registration';
    }

    /**
     * Returns the configuration array or the specified configuration
     *
     * @param string $key configuration key
     *
     * @return mixed
     */
    public function getConfig(string $key = null) {
        if (is_null($key)) {
            return $this->config;
        } else {
            return key_exists ($key, $this->config) ? $this->config[$key] : null;
        }

    }

    /**
     * Retorna um token aleatório
     * 
     * @param int $length 
     * @return string 
     */
    static function getToken(int $length):string {
        /**
         * http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string/13733588#13733588
         */
        $crypto_rand_secure = function ($min, $max) {
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
        };

        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$crypto_rand_secure(0, $max)];
        }
        
        return $token;
    }


    /**
     * Retorna o nome legível de um termo
     * 
     * @todo remover essa função e refatorar onde for usada
     * 
     * @param mixed $slug 
     * @return string|null
     */
    function getReadableName(string $slug): string|null {
        if (array_key_exists($slug, $this->config['routes']['readableNames'])) {
            return $this->config['routes']['readableNames'][$slug];
        }
        return null;
    }


    /**********************************************
     *  FLAGS - FUNÇÕES QUE MANIPULAM AS FLAGS 
     **********************************************/

    /**
     * Verifica se uma entidade está ativa
     * 
     * @param string $entity 
     * @return bool 
     */
    function isEnabled(string $entity){

        $entities = [
            'agent' => 'agents',
            'space' => 'spaces',
            'project' => 'projects',
            'opportunity' => 'opportunities',
            'event' => 'events',
            'subsite' => 'subsites',
            'seal' => 'seals',
            'app' => 'apps',
        ];

        $entity = $entities[$entity] ?? $entity;

        $enabled = (bool) $this->config['app.enabled.' . $entity] ?? false;
        
        $this->applyHookBoundTo($this, "app.isEnabled({$entity})", [&$enabled]);

        return $enabled;
    }

     /**
      * Habilita o controle de acesso
      * @return void 
      */
    function enableAccessControl(){
        if ($this->_disableAccessControlCount > 0) {
            $this->_disableAccessControlCount--;
        }
    }

    /**
     * Desabilita o controle de acesso
     * @return void 
     */
    function disableAccessControl(){
        $this->_disableAccessControlCount++;
    }

    /**
     * Indica se o controle de acesso está habilitado
     * @return bool 
     */
    function isAccessControlEnabled(){
        return $this->_disableAccessControlCount === 0;
    }

    /**
     * Habilita a criação de Requests
     * @return void 
     */
    function enableWorkflow(){
        if ($this->_disableWorkflowCount > 0) {
            $this->_disableWorkflowCount--;
        }
    }

    /**
     * Desabilita a criação de Requests
     * @return void 
     */
    function disableWorkflow(){
        $this->_disableWorkflowCount++;
    }

    /**
     * Indica se a criação de Requests está habilitada
     * @return bool 
     */
    function isWorkflowEnabled(){
        return $this->_disableWorkflowCount === 0;
    }

    /*******************************
     *            Utils
     *******************************/


     /**
      * Bloqueia a execução do código posterior ao chamamento da função,
      * para o mesmo $name, enquanto não for chamado o unlock para o $name.
      * 
      * Caso o segundo parâmetro tenha sido informado, espera o tempo informado
      * pelo unlock.
      *
      * @param string $name 
      * @param float|int $wait_for_unlock 
      * @param float|int $expire_in 
      * @return void 
      * @throws GlobalException 
      */
      function lock(string $name, float $wait_for_unlock = 0, float $expire_in = 10) {
          $name = $this->slugify($name);
  
          $filename = sys_get_temp_dir() . "/lock-{$name}.lock";
  
          if ($expire_in && file_exists($filename) && (microtime (true) - filectime($filename) > $expire_in)) {
              unlink($filename);
          }
  
          if ($wait_for_unlock) {
              $count = 0;
              while (file_exists($filename) && $count < $wait_for_unlock) {
                  if ($expire_in && (microtime (true) - filectime($filename) > $expire_in)) {
                      unlink($filename);
                  } else {
                      $count += 0.1;
                      usleep(100000);
                  }
              }
          }
  
          if (file_exists($filename)) {
              throw new \Exception("{$name} is locked");
          }
  
          file_put_contents($filename, "1");
      }

     /**
      * Desbloqueia a execução do código posterior ao chamamento do lock($name)

      * @param string $name 
      * @return void 
      */
     function unlock(string $name) {
        $name = $this->slugify($name);
        
        $filename = sys_get_temp_dir()."/lock-{$name}.lock"; 

        unlink($filename);
     }
     
     /**
      * Transforma o texto num slug
      * @param string $text 
      * @return string 
      */
    function slugify(string $text): string {        
        return Utils::slugify($text);
    }

    /**
     * Remove os acentos de um texto
     * 
     * @param string $string 
     * @return string 
     */
    function removeAccents(string $string): string {
        return Utils::removeAccents($string);
    }

    /**
     * Creates a URL to an controller action action
     *
     * @param string $controller_id the controller id
     * @param string $action_name the action name
     * @param array $data the data to pass to action
     *
     * @see RoutesManager::createUrl()
     *
     * @return string the URL to action
     */
    public function createUrl(string $controller_id, string $action_name = '', array $data = []): string {
        return $this->_routesManager->createUrl($controller_id, $action_name, $data);
    }

    /**
     * Retorna o repositório de uma entidade dada a entidade
     *
     * if the given repository class name not starts with a slash this function will prepend \MapasCulturais\Entities\ to the class name
     *
     * @param string $entity_name Repository Class Name
     * @return Repository the Entity Repository
     */
    public function repo(string $entity_name): Repository {

        // add MapasCulturais\Entities namespace if no namespace in repo name
        if(strpos($entity_name, '\\') === false)
                $entity_name = "\MapasCulturais\Entities\\{$entity_name}";

        return $this->em->getRepository($entity_name);
    }

    /**
     * Renderiza um template Mustache
     * 
     * @param string $template_name 
     * @param array|object $template_data 
     * @return string 
     * @throws GlobalException 
     */
    function renderMustacheTemplate(string $template_name, array|object $template_data): string {
        if(!is_array($template_data) && !is_object($template_data)) {
            throw new \Exception('Template data not object or array');
        }

        $template_data = (object) $template_data;

        if ($this->view->version >= 2) {
            $template_data->siteName = $this->siteName;
            $template_data->siteDescription = $this->siteDescription;

        } else {
            $template_data->siteName = $this->view->dict('site: name', false);
            $template_data->siteDescription = $this->view->dict('site: description', false);
            $template_data->siteOwner = $this->view->dict('site: owner', false);
        }
        
        $template_data->baseUrl = $this->getBaseUrl();

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

        if(!($file_name = $this->view->resolveFileName('templates/' . i::get_locale(), $template_name))) {
            if(!($file_name = $this->view->resolveFileName('templates/pt_BR', $template_name))) {
                throw new \Exception('Email Template undefined');
            }
        }

        $header = file_get_contents($header_name);
        $footer = file_get_contents($footer_name);
        $content = file_get_contents($file_name);

        $matches = [];
        if(preg_match_all('#\{\{asset:([^\}]+)\}\}#', $header, $matches)){
            foreach($matches[0] as $i => $tag){
                $header = str_replace($tag, $this->view->asset($matches[1][$i], false), $header);
            }
        }

        $matches = [];
        if(preg_match_all('#\{\{asset:([^\}]+)\}\}#', $footer, $matches)){
            foreach($matches[0] as $i => $tag){
                $footer = str_replace($tag, $this->view->asset($matches[1][$i], false), $footer);
            }
        }

        $matches = [];
        if(preg_match_all('#\{\{asset:([^\}]+)\}\}#', $content, $matches)){
            foreach($matches[0] as $i => $tag){
                $content = str_replace($tag, $this->view->asset($matches[1][$i], false), $content);
            }
        }
        
        $mustache = new \Mustache_Engine();

        $headerData = $template_data;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).headerData", [&$headerData]);
        $_header = $mustache->render($header, $headerData);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).header", [&$_header]);

        $footerData = $template_data;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).footerData", [&$footerData]);
        $_footer = $mustache->render($footer, $footerData);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).footer", [&$_footer]);

        $template_data->_footer = $_footer;
        $template_data->_header = $_header;
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).templateData", [&$template_data]);
        $content = $mustache->render($content, $template_data);
        $this->applyHookBoundTo($this, "mustacheTemplate({$template_name}).content", [&$content]);

        return $content;
    }

    /**
     * Dispara um 404
     * 
     * @return never 
     * @throws NotFound 
     */
    function pass() {
        throw new Exceptions\NotFound;
    }

    function redirect(string $destination, int $status_code = 302) {
        $this->response = $this->response->withHeader('Location', $destination);
        $this->halt($status_code);
    }

    /**
     * Interrompo a execução da aplicação com o status e mensagem informados
     * 
     * @param int $status_code 
     * @param string $message 
     * 
     * @return never 
     * @throws RuntimeException 
     * @throws InvalidArgumentException 
     */
    function halt(int $status_code, string $message = '') {
        $this->response = $this->response->withStatus($status_code);

        if ($message) {
            $this->response->getBody()->write($message);
        }

        throw new Exceptions\Halt;
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
     * @see App::sanitizeFilename()
     *
     * @return Entities\File|Entities\File[]
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
                    throw new Exceptions\FileUploadError($key, $_FILES[$key]['error']);
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
     * @param string $filename
     *
     * @return string The sanitized filename.
     */
    function sanitizeFilename($filename, $mimetype = false){
        $filename = str_replace(' ','_', strtolower($filename));
        if(is_callable($this->config['app.sanitize_filename_function'])){
            $cb = $this->config['app.sanitize_filename_function'];
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

    /**********************************************
     * Hooks System
     **********************************************/ 

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string   $name   A hook name (Optional)
     */
    public function clearHooks(string $name = null) {
        $this->hooks->clear($name);
    }

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
    public function getHooks(string $name = null) {
        return $this->hooks->get($name);
    }

    /**
     * Assign hook
     * @param  string   $name       The hook name
     * @param  callable    $callable   A callable object
     * @param  int      $priority   The hook priority; 0 = high, 10 = low
     */
    function hook(string $name, callable $callable, int $priority = 10) {
        $this->hooks->hook($name, $callable, $priority);
    }

    /**
     * Invoke hook
     * @param  string   $name       The hook name
     * @param  mixed    $hookArgs   (Optional) Argument for hooked functions
     * 
     * @return callable[]
     */
    function applyHook(string $name, array $hookArg = []): array {
        return $this->hooks->apply($name, $hookArg);
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
    function applyHookBoundTo(object $target_object, string $name, array $hookArg = []) {
        return $this->hooks->applyBoundTo($target_object, $name, $hookArg);
    }



    /**********************************************
     *              Background Jobs
     **********************************************/

    /**
     * Enfileira um job e substitui um job existente com o mesmo ID, se necessário.
     *
     * @param string $type_slug Tipo do job a ser enfileirado
     * @param array $data Dados do job a ser enfileirado
     * @param string $start_string Data/hora de início do job, no formato 'now' ou uma data/hora válida
     * @param string $interval_string Intervalo de execução do job, no formato de intervalo do PHP (ex: '1 hour', '30 minutes')
     * @param int $iterations Número de vezes que o job deve ser executado
     * @param User|int $user Usuário responsável pelo job, pode ser um objeto User ou um ID de usuário
     * @param Subsite|int $subsite Subsite responsável pelo job, pode ser um objeto Subsite ou um ID de subsite
     * @return Job Retorna o objeto Job criado
     */
    public function enqueueOrReplaceJob(string $type_slug, array $data, string $start_string = 'now', string $interval_string = '', int $iterations = 1, User|int $user = null, Subsite|int $subsite = null) {
        return $this->enqueueJob($type_slug, $data, $start_string, $interval_string, $iterations, true, $user, $subsite);
    }
    
    /**
     * Enfileira um job
     *
     * @param string $type_slug Tipo do job a ser enfileirado
     * @param array $data Dados do job a ser enfileirado
     * @param string $start_string Data/hora de início do job, no formato 'now' ou uma data/hora válida
     * @param string $interval_string Intervalo de execução do job, no formato de intervalo do PHP (ex: '1 hour', '30 minutes')
     * @param int $iterations Número de vezes que o job deve ser executado
     * @param bool $replace Se verdadeiro, substitui o job existente com o mesmo ID
     * @param User|int $user Usuário responsável pelo job, pode ser um objeto User ou um ID de usuário
     * @param Subsite|int $subsite Subsite responsável pelo job, pode ser um objeto Subsite ou um ID de subsite
     * @return Job Retorna o objeto Job criado
     * @throws Exception Se o tipo de job for inválido
     */
    public function enqueueJob(string $type_slug, array $data, string $start_string = 'now', string $interval_string = '', int $iterations = 1, $replace = false, User|int $user = null, Subsite|int $subsite = null) {
        if($this->config['app.log.jobs']) {
            $this->log->debug("ENQUEUED JOB: $type_slug");
        }

        if($user && is_numeric($user)) {
            $user = $this->repo('User')->find($user);
        } else if (is_null($user) && !$this->user->is('guest')) {
            $user = $this->repo('User')->find($this->user->id);
        }

        if($subsite && is_numeric($subsite)) {
            $subsite = $this->repo('Subsite')->find($subsite);
        } else if (is_null($subsite)) {
            $subsite = $this->subsite ? $this->subsite->refreshed() : null;
        }

        $type = $this->getRegisteredJobType($type_slug);
        
        if (!$type) {
            throw new \Exception("invalid job type: {$type_slug}");
        }

        $id = $type->generateId($data, $start_string, $interval_string, $iterations);

        if ($job = $this->repo('Job')->find($id)) {
            if ($replace) {
                $job->delete(true);
            } else {
                return $job;
            }
        }

        $job = new Job($type);

        if($subsite) {
            $job->subsite = $subsite;
        }

        if($user) {
            $job->user = $user;
        }

        $job->id = $id;

        $job->iterations = $iterations;

        $job->nextExecutionTimestamp = new DateTime($start_string);
        $job->intervalString = $interval_string;

        foreach ($data as $key => $value) {
            $job->$key = $value;
        }

        try{
            if($this->config['app.executeJobsImmediately']) {
                $job->execute();
            } else {
                $job->save(true);
            }
        } catch (\Exception $e) {
            $this->log->error('ERRO AO SALVAR JOB: ' . print_r(array_keys($data), true));
        }

        return $job;
    }

    /**
     * Desinfileira um job
     * 
     * @param string $type_slug 
     * @param array $data 
     * @param string $start_string 
     * @param string $interval_string 
     * @param int $iterations 
     * @return void 
     * @throws GlobalException 
     * @throws PermissionDenied 
     * @throws ORMInvalidArgumentException 
     * @throws ORMException 
     * @throws OptimisticLockException 
     */
    public function unqueueJob(string $type_slug, array $data, string $start_string = 'now', string $interval_string = '', int $iterations = 1) {
        if($this->config['app.log.jobs']) {
            $this->log->debug("UNQUEUED JOB: $type_slug");
        }

        $type = $this->getRegisteredJobType($type_slug);
        
        if (!$type) {
            throw new \Exception("invalid job type: {$type_slug}");
        }

        $id = $type->generateId($data, $start_string, $interval_string, $iterations);

        if ($job = $this->repo('Job')->find($id)) {
            $job->delete(true);
        }
    }

    /**
     * Executa um trabalho agendado (job) que esteja pronto para ser executado.
     * 
     * Essa função verifica se há algum trabalho agendado que esteja pronto para ser executado (com base na data de próxima execução e no número de iterações restantes), e então executa esse trabalho.
     * 
     * Ela também realiza algumas tarefas adicionais, como:
     * - Atualiza o status do trabalho para "em execução"
     * - Inicializa o subsite associado ao trabalho, se houver
     * - Autentica o usuário associado ao trabalho
     * - Registra informações de log sobre a execução do trabalho
     * - Aplica os hooks "app.executeJob:before" e "app.executeJob:after" antes e depois da execução do trabalho
     * - Persiste a fila de reprocessamento do cache de permissões
     * 
     * @return int|false O ID do trabalho executado, ou false se nenhum trabalho estiver pronto para ser executado
     */
    public function executeJob(): int|false {
        /** @var $conn Connection */
        $conn = $this->em->getConnection();
        $now = date('Y-m-d H:i:s');
        $job_id = $conn->fetchScalar("
            SELECT id
            FROM job
            WHERE
                next_execution_timestamp <= '$now' AND
                iterations_count < iterations AND
                status = 0
            ORDER BY next_execution_timestamp ASC
            LIMIT 1");

        if ($job_id) {
            /** @var Job $job */
            $conn->executeQuery("UPDATE job SET status = 1 WHERE id = '{$job_id}'");
            $job = $this->repo('Job')->find($job_id);

            if($job->subsite) {
                $this->_initSubsite($job->subsite->url);
            }
            $this->auth->authenticatedUser = $job->user;


            if($this->config['app.log.jobs']) {
                $this->log->debug("EXECUTING JOB: {$job->id} of type {$job->type}");
                $this->log->debug("AUTHENTICATED USER: {$this->user->id}");
                $this->log->debug("SUBSITE: {$this->subsite->url}");

            }

            // $this->disableAccessControl();
            $this->applyHookBoundTo($this, "app.executeJob:before");
            $job->execute();
            $this->applyHookBoundTo($this, "app.executeJob:after");
            // $this->enableAccessControl();
            $this->persistPCachePendingQueue();
            return (int) $job_id;
        } else {
            return false;
        }
    }


    /**********************************************
     * Permissions Cache
     **********************************************/

    /**
     * Adiciona a entidade na fila de reprocessamento de cache de permissão 
     * 
     * @param Entity $entity 
     * @param User $user = null
     * 
     * @return void 
     */
    public function enqueueEntityToPCacheRecreation(Entity $entity, User $user = null) {
        if (!$entity->__skipQueuingPCacheRecreation) {
            $entity_key = $entity->id ? "{$entity}" : "{$entity}:".spl_object_id($entity);
            if($user) {
                $entity_key = "{$entity_key}:{$user->id}";
            }
            $this->_permissionCachePendingQueue[$entity_key] = [$entity, $user];
        }
    }

    /**
     * Verifica se a entidade já está na fila de reprocessamento de cache de permissão
     * 
     * @param Entity $entity 
     * @param User $user = null
     * 
     * @return bool 
     */
    public function isEntityEnqueuedToPCacheRecreation(Entity $entity, User $user = null) {
        $entity_key = $entity->id ? "{$entity}" : "{$entity}:".spl_object_id($entity);
        if($user) {
            $entity_key = "{$entity_key}:{$user->id}";
        }

        return isset($this->_permissionCachePendingQueue[$entity_key]);
    }

    /**
     * Persiste a fila de entidades para reprocessamento de cache de permissão
     */
    public function persistPCachePendingQueue() {
        $conn = $this->em->getConnection();

        foreach($this->_permissionCachePendingQueue as $config) {
            $entity = $config[0];
            $user = $config[1];
            
            if (is_int($entity->id)){
                $params = [
                    'object_type' => $entity->getClassName(),
                    'object_id' => $entity->id
                ];

                if($user) {
                    $where = 'usr_id = :usr_id AND';
                    $params['usr_id'] = $user->id;
                } else {
                    $where = 'usr_id IS NULL AND';
                }
                // verifica se já há uma entrada na tabela para a entidade que não está sendo processada ainda
                $sql = "
                    SELECT id 
                    FROM permission_cache_pending 
                    WHERE 
                        object_type = :object_type AND 
                        object_id = :object_id AND 
                        {$where}
                        status = 0";

                $exists = $conn->fetchOne($sql, $params);

                // se existir, não precisa adicionar novamente
                if($exists) {
                    continue;
                }

                // adiciona a entrada no banco
                $conn->executeQuery("
                    INSERT INTO permission_cache_pending 
                        (id, object_type, object_id, usr_id) 
                    VALUES 
                        (nextval('agent_id_seq'::regclass), :object_type, :object_id, :usr_id)",

                    [
                        'object_type' => $entity->getClassName(),
                        'object_id' => $entity->id,
                        'usr_id' => $user ? $user->id : null
                    ]
                );


                // se foi adicionado a fila o processamento para todos os usuários, 
                // não precisa processar a fila para cada usuário individualmente
                if(!$user) {
                    $conn->executeQuery("
                        DELETE FROM 
                            permission_cache_pending 
                        WHERE 
                            object_type = :object_type AND 
                            object_id = :object_id AND 
                            usr_id IS NOT NULL AND
                            status = 0", 
                            [
                                'object_type' => $entity->getClassName(),
                                'object_id' => $entity->id
                            ]);
                }
            }
        }

        $this->_permissionCachePendingQueue = [];
    }

    /**
     * Marca uma entidade como já tendo o cache de permissão recriado
     * 
     * @param Entity $entity 
     * @return void 
     */
    public function setEntityPermissionCacheAsRecreated(Entity $entity) {
        $this->_recreatedPermissionCacheList["$entity"] = $entity;
    }

    /**
     * Verifica se a entidade já teve o cache de permissão recriado
     * 
     * @param Entity $entity 
     * 
     * @return bool 
     */
    public function isEntityPermissionCacheRecreated(Entity $entity) {
        return isset($this->_recreatedPermissionCacheList["$entity"]);
    }

    /**
     * Processa a primeira entidade da fila de reprocessamento de cache de permissão
     * 
     * @return void 
     * 
     * @throws NotSupported 
     * @throws RuntimeException 
     * @throws PermissionDenied 
     * @throws ORMInvalidArgumentException 
     * @throws ORMException 
     * @throws OptimisticLockException 
     * @throws TransactionRequiredException 
     * @throws WorkflowRequest 
     * @throws GlobalException 
     */
    public function recreatePermissionsCache(){
        /** @var Connection $conn */
        $conn = $this->em->getConnection();

        $max_entities = $this->config['pcache.maxEntitiesPerProcess'] ?: 1;

        for($i = 0; $i < $max_entities; $i++) {
            $queue_summary = $conn->fetchAll("
                SELECT COUNT(*) AS num, object_type, status 
                FROM permission_cache_pending 
                WHERE status in (0,1)
                GROUP BY object_type, status 
                ORDER BY num DESC, status DESC");

            $running = [];
            $not_running = [];

            foreach($queue_summary as $line) {
                $line = (object) $line;
                if($line->status == 1) {
                    $running[$line->object_type] = $line->num;
                } else {
                    $not_running[$line->object_type] = $line->num;
                }
            }

            $eligible_classes = [];
            foreach($not_running as $class => $count) {
                if(!isset($running[$class])) {
                    $eligible_classes[] = $class;
                }
            }

            if($eligible_classes) {
                $eligible_classes = implode("','", $eligible_classes);
                $eligible_classes = "AND object_type IN ('$eligible_classes')";
            } else {
                $eligible_classes = '';
            }

            $cache_pending = $conn->fetchAssoc("
                SELECT *
                FROM permission_cache_pending
                WHERE 
                    status = 0 $eligible_classes AND 
                    CONCAT (object_type, object_id, usr_id) NOT IN (
                        SELECT CONCAT(object_type, object_id, usr_id) 
                        FROM permission_cache_pending WHERE 
                        status > 0 
                    ) ORDER BY id ASC");

            if(!$cache_pending) { 
                return;
            }

            $caches_pending = $conn->fetchAll('
                SELECT id, usr_id 
                FROM permission_cache_pending 
                WHERE 
                    object_type = :object_type AND
                    object_id = :object_id AND 
                    status = 0
                    ',
                [
                    'object_type' => $cache_pending['object_type'],
                    'object_id' => $cache_pending['object_id']
                ]);
            
            if(!$caches_pending) {
                continue;
            }

            $cache_pending_ids = array_map(fn($item) => $item['id'], $caches_pending);
            $cache_pending_ids = implode(',',$cache_pending_ids);

            $conn->executeQuery("
                UPDATE permission_cache_pending 
                SET status=1 
                WHERE id in($cache_pending_ids)");

            $start_time = microtime(true);
            try {
                $entity = $this->repo($cache_pending['object_type'])->find($cache_pending['object_id']);
                if ($entity) {
                    $user_ids = array_map(fn($item) => $item['usr_id'], $caches_pending);

                    if(in_array(null,$user_ids)) {
                        $user_ids = null;
                    }
                    $entity->recreatePermissionCache($user_ids);
                }

                $conn->executeQuery("
                    DELETE FROM permission_cache_pending 
                    WHERE id in($cache_pending_ids)");
                
            } catch (\Exception $e ){
                $conn->executeQuery("
                    UPDATE permission_cache_pending 
                    SET status=2 
                    WHERE id in($cache_pending_ids)");

                if($this->config['app.log.pcache'] && php_sapi_name()==="cli"){
                    echo "\n\t - ERROR - {$e->getMessage()}";
                }
                throw $e;
            }

            if($this->config['app.log.pcache']){
                $end_time = microtime(true);
                $total_time = number_format($end_time - $start_time, 1);

                $this->log->info("PCACHE RECREATED FOR {$cache_pending['object_type']}:{$cache_pending['object_id']} IN {$total_time} seconds\n--------\n");
            }
            $this->_permissionCachePendingQueue = [];
        }
    }

    /*********************************************************
     *                       MAILER
     *********************************************************/

    /**
     * Cria e retorna uma instância do Mailer Transport
     * 
     * @return TransportInterface 
     *
     * @throws ExceptionInvalidArgumentException 
     * @throws UnsupportedSchemeException 
     */
    function getMailerTransport(): TransportInterface {
        $transport = Transport::fromDsn($this->config['mailer.transport']);

        $this->applyHook('mailer.transport', [&$transport]);

        return $transport;
    }

    /**
     * Retorna uma instância configurada do Mailer
     * 
     * @return Mailer 
     * 
     * @throws ExceptionInvalidArgumentException 
     * @throws UnsupportedSchemeException 
     */
    function getMailer(): Mailer {
        $mailer = new Mailer($this->getMailerTransport());

        return $mailer;
    }


    /**
     * Cria uma mensagem de email
     * 
     */
    function createMailMessage(array $args = []): Email {
        $message = new Email();

        if($this->config['mailer.from']){
            $message->from($this->config['mailer.from']);
        }

        if($this->config['mailer.alwaysTo']){
            $message->to($this->config['mailer.alwaysTo']);
        }

        if($this->config['mailer.bcc']){
            $bcc = is_array($this->config['mailer.bcc']) ? 
                $this->config['mailer.bcc']:
                explode(',', $this->config['mailer.bcc']);

            
            $message->bcc(...$bcc);
        }

        if($this->config['mailer.replyTo']){
            $message->replyTo($this->config['mailer.replyTo']);
        }

        $original = [];
        foreach($args as $method_name => $value){
            if(in_array($method_name, ['to', 'cc', 'bcc'])) {
                if($method_name == 'bcc' && isset($bcc)) {
                    $value = [...$bcc, ...(is_array($value) ? $value : explode(',', $value))];
                }
                if ($this->config['mailer.alwaysTo']) {
                    $original[$method_name] = $value;
                } else {
                    $value = is_array($value) ? $value : explode(',', $value);
                    $message->$method_name(...$value);
                }
            } else {
                if($method_name == 'body') {
                    $method_name = 'html';
                }
    
                if(method_exists($message, $method_name)){
                    if($method_name == 'attach' && $value) {
                        if (file_exists($value)) {
                            $message->addPart(new DataPart(new File($value)));
                        }
                    } else {
                        $message->$method_name($value);
                    }
                }
            }

        }

        if($this->config['mailer.alwaysTo']){
            foreach($original as $key => $val){
                if(is_array($val)){
                    $val = implode(', ', $val);
                }
                $current_body = $message->getHtmlBody();
                $message->html("<strong>ORIGINALMENTE $key:</strong> $val <br>\n $current_body");
            }
        }

        return $message;
    }

    /**
     * Envia uma mensagem de email
     * 
     * @param Email $message 
     * @return bool 
     */
    function sendMailMessage(Email $message): bool {
        $mailer = $this->getMailer();

        if (!is_object($mailer))
            return false;

        try {
            $mailer->send($message);
            return true;
        } catch(TransportExceptionInterface $exception) {
            $this->log->error('Mailer error: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Cria e envia uma mensagem de email
     * 
     * @param array $args 
     * @return bool 
     * @throws TypeError 
     * @throws ExceptionInvalidArgumentException 
     * @throws UnsupportedSchemeException 
     * @throws LogInvalidArgumentException 
     * @throws Throwable 
     * @throws ExceptionLogicException 
     */
    function createAndSendMailMessage(array $args = []){
        $message = $this->createMailMessage($args);
        return $this->sendMailMessage($message);
    }    
    
    /**
     * Renderiza um template de email
     * 
     * O template deve ser um template mustache
     * 
     * @param string $template_name 
     * @param array|object $template_data 
     * @return array 
     * @throws GlobalException 
     * @throws MailTemplateNotFound 
     */
    function renderMailerTemplate(string $template_name, array|object $template_data = []): array {
        if($message = $this->config['mailer.templates'][$template_name] ?? null) {
            $message['body'] = $this->renderMustacheTemplate($message['template'], $template_data);
        } else {
            throw new Exceptions\MailTemplateNotFound($template_name);
        }

        return $message;
    }



    /*********************************************************
     *                REGISTRO DA APLICAÇÃO  
     *********************************************************/

    /**
     * Executa o registro da aplicação
     * 
     * 1. Registra os controladores
     * 2. Registra os tipos de outputs da API
     * 3. Registra os roles
     * 4. Registra os grupos de arquivos
     * 5. Registra as transformações de imagens
     * 6. Registra os grupos de agentes relacionados das registrations
     * 7. Registra os grupos de metalists
     * 8. Registra os tipos e metadados das entidades 
     * 9. Registra as taxonomias
     * 10. Chama o register do tema
     * 11. Chama o register dos módulos
     * 11. Chama o register dos plugins
     * 
     * @hook app.register:before
     * @hook app.register
     * @hook app.register:after
     * 
     * @return void 
     * 
     * @throws GlobalException 
     * @throws ReflectionException 
     * @throws MappingException 
     */
    protected function register(){
        if($this->_registered)
            return;

        $this->_registered = true;

        $this->applyHookBoundTo($this, 'app.register:before');

        // register controllers

        $this->registerController('site',    'MapasCulturais\Controllers\Site');
        $this->registerController('auth',    'MapasCulturais\Controllers\Auth');

        if(($this->view) instanceof Themes\BaseV1\Theme ) {
            $this->registerController('panel',   'MapasCulturais\Controllers\Panel');
        }

        $this->registerController('user',   'MapasCulturais\Controllers\User');

        $this->registerController('event',          'MapasCulturais\Controllers\Event');
        $this->registerController('agent',          'MapasCulturais\Controllers\Agent');
        $this->registerController('space',          'MapasCulturais\Controllers\Space');
        $this->registerController('project',        'MapasCulturais\Controllers\Project');

        $this->registerController('opportunity',    'MapasCulturais\Controllers\Opportunity');
        $this->registerController('evaluationMethodConfiguration', 'MapasCulturais\Controllers\EvaluationMethodConfiguration');

        $this->registerController('subsite',        'MapasCulturais\Controllers\Subsite');

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
            'avatar' => new Definitions\FileGroup('avatar', ['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true),
            'header' => new Definitions\FileGroup('header', ['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true),
            'gallery' => new Definitions\FileGroup('gallery', ['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), false),
            'registrationFileConfiguration' => new Definitions\FileGroup('registrationFileTemplate', ['^application/.*'], i::__('O arquivo enviado não é um documento válido.'), true),
            'rules' => new Definitions\FileGroup('rules', ['^application/.*'], i::__('O arquivo enviado não é um documento válido.'), true),
            'logo'  => new Definitions\FileGroup('logo',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true),
            'background' => new Definitions\FileGroup('background',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true),
            'share' => new Definitions\FileGroup('share',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'),true),
            'institute'  => new Definitions\FileGroup('institute',['^image/(jpeg|png)$'], i::__('O arquivo enviado não é uma imagem válida.'), true),
            'favicon'  => new Definitions\FileGroup('favicon',['^image/(jpeg|png|x-icon|vnd.microsoft.icon)$'], i::__('O arquivo enviado não é uma imagem válida.'), true),
            'zipArchive'  => new Definitions\FileGroup('zipArchive',['^application/zip$'], i::__('O arquivo não é um ZIP.'), true, null, true),
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
        $this->registerFileGroup('subsite',$file_groups['downloads']);

        if ($theme_image_transformations = $this->view->resolveFilename('','image-transformations.php')) {
            $image_transformations = include $theme_image_transformations;
        } else {
            $image_transformations = include APPLICATION_PATH.'/conf/image-transformations.php';
        }

        foreach($image_transformations as $name => $transformation)
            $this->registerImageTransformation($name, $transformation);


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
                            'required' => i::__('O link é obrigatório')
                        ]
                    ],
                ],
                i::__('O arquivo enviado não é uma imagem válida.'),
                true
            ),
            'videos' => new Definitions\MetaListGroup('videos',
                [
                    'title' => [
                        'label' => 'Nome'
                    ],
                    'value' => [
                        'label' => 'Vídeo',
                        'validations' => [
                            'required' => i::__('O link do vídeo é obrigatório')
                        ]
                    ],
                ],
                i::__('O arquivo enviado não é uma imagem válida.'),
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



        // registration agent relations
        foreach($this->config['registration.agentRelations'] as $config){
            $def = new Definitions\RegistrationAgentRelation($config);
            $opportunities_meta[$def->metadataName] = $def->getMetadataConfiguration();

            $this->registerRegistrationAgentRelation($def);
        }


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
            $entities = key_exists('entities', $taxonomy_definition) ? $taxonomy_definition['entities'] : [];

            $definition = new Definitions\Taxonomy($taxonomy_id, $taxonomy_slug, $taxonomy_description, $restricted_terms, $taxonomy_required, $entities);
            $definition->name = $taxonomy_definition['name'] ?? '';
            $entity_classes = $taxonomy_definition['entities'];

            foreach($entity_classes as $entity_class){
                $this->registerTaxonomy($entity_class, $definition);
            }
        }

        $this->view->register();

        foreach($this->modules as $module){
            $module->register();
        }

        foreach($this->plugins as $plugin){
            $plugin->register();
        }

        $this->applyHookBoundTo($this, 'app.register',[&$this->_register]);
        $this->applyHookBoundTo($this, 'app.register:after');
    }

    /** 
     * ============ JOBS ============ 
     */

    /**
     * Registra um tipo de Job
     * 
     * @param JobType $definition 
     * @return void 
     * @throws GlobalException 
     */
    public function registerJobType(Definitions\JobType $definition) {
        if(key_exists($definition->slug, $this->_register['job_types'])){
            throw new \Exception("Job type {$definition->slug} already registered");
        }
        $this->_register['job_types'][$definition->slug] = $definition;
    }

    /**
     * Retorna a os tipos de Job registrados
     * 
     * @return Definitions\JobType[]
     */
    public function getRegisteredJobTypes(): array {
        return $this->_register['job_types'];
    }

    /**
     * Retorna um tipo de job registrado dado o slug
     * 
     * @return Definitions\JobType
     */
    public function getRegisteredJobType(string $slug): Definitions\JobType|null {
        return $this->_register['job_types'][$slug] ?? null;
    }


    /** 
     * ============ ROLES ============ 
     */

    /**
     * Register a new role
     *
     * @param Definitions\Role $role the role definition
     * @return void
     */
    public function registerRole(Definitions\Role $role) {
        $this->_register['roles'][$role->getRole()] = $role;
    }

    /**
     * Retorna a lista de roles registradas
     *
     * @return Definitions\Role[]
     */
    public function getRoles(): array {
        return $this->_register['roles'];
    }

    /**
     * Returns the role definition
     *
     * @param string $role_slug
     * @return Definitions\Role|null
     */
    public function getRoleDefinition(string $role_slug): Definitions\Role|null {
        return $this->_register['roles'][$role_slug] ?? null;
    }

    /**
     * Retorna o nome de uma role
     * 
     * @param string $role_slug 
     * @return string|null 
     */
    public function getRoleName(string $role_slug): string|null {
        $def = $this->_register['roles'][$role_slug] ?? null;
        return $def ? $def->name : $role_slug;
    }


    /** 
     * ============ AGENTES RELACIONADOS DAS INSCRIÇÕES ============ 
     */

    /**
     * Registra um grupo de agente relacionado de inscrição
     * 
     * @param RegistrationAgentRelation $def 
     * @return void 
     * @throws GlobalException 
     */
    function registerRegistrationAgentRelation(Definitions\RegistrationAgentRelation $def) {
        $group_name = $def->agentRelationGroupName;
        if($this->_register['registration_agent_relations'][$group_name] ?? false){
            throw new \Exception('There is already a RegistrationAgentRelation with agent relation group name "' . $def->agentRelationGroupName . '"');
        }
        $this->_register['registration_agent_relations'][$group_name] = $def;
    }

    /**
     * Retorna os grupos de agente relacionado de inscrição registrados
     * 
     * @return Definitions\RegistrationAgentRelation[]
     */
    function getRegisteredRegistrationAgentRelations(): array {
        return $this->_register['registration_agent_relations'];
    }

    /**
     * Retorna a definição do agente relacionado owner de inscrição
     * 
     * @return Definitions\RegistrationAgentRelation 
     */
    function getRegistrationOwnerDefinition(): Definitions\RegistrationAgentRelation{
        $config = $this->getConfig('registration.ownerDefinition');
        $definition = new Definitions\RegistrationAgentRelation($config);
        return $definition;
    }

    /**
     * Retorna as definições dos agentes relacionados das inscrições
     * 
     * @return Definitions\RegistrationAgentRelation[] 
     */
    function getRegistrationAgentsDefinitions(): array {
        $definitions =  ['owner' => $this->getRegistrationOwnerDefinition()];
        foreach ($this->getRegisteredRegistrationAgentRelations() as $groupName => $def){
            $definitions[$groupName] = $def;
        }
        return $definitions;
    }

    /**
     * Retorna a definição de um agente relacionado de inscrição dado o nome do grupo
     * @param string $group_name 
     * @return RegistrationAgentRelation|null 
     */
    function getRegisteredRegistrationAgentRelationByAgentRelationGroupName(string $group_name): Definitions\RegistrationAgentRelation|null {
        return $this->_register['registration_agent_relations'][$group_name] ?? null;
    }


    /** 
     * ============ AGENTES RELACIONADOS DAS INSCRIÇÕES ============ 
     */

     /**
      * Registra um tipo de thread de chat
      *
      * @param ChatThreadType $definition 
      * @return void 
      * @throws GlobalException 
      */
    function registerChatThreadType(Definitions\ChatThreadType $definition) {
        if (isset($this->_register['chat_thread_types'][$definition->slug])) {
            throw new \Exception("Attempting to re-register {$definition->slug}.");
        }
        $this->_register['chat_thread_types'][$definition->slug] = $definition;
    }

    /**
     * Retorna as definições dos tipos de chat registrados
     * 
     * @return array 
     */
    function getRegisteredChatThreadTypes(): array {
        return $this->_register['chat_thread_types'];
    }

    /**
     * Retorna um tipo de chat registrado dado o slug
     * 
     * @param mixed $slug 
     * @return ChatThreadType|null 
     */
    function getRegisteredChatThreadType($slug): Definitions\ChatThreadType|null    {
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
     * is not a subclass of ApiOutput
     *
     * @param string $api_output_class_name The API Output class name
     *
     * @return ApiOutput|null the API Output
     */
    public function getRegisteredApiOutputByClassName($api_output_class_name): ApiOutput|null {
        if(in_array($api_output_class_name, $this->_register['api_outputs']) && class_exists($api_output_class_name) && is_subclass_of($api_output_class_name, '\MapasCulturais\ApiOutput')) {
            return $api_output_class_name::i();
        } else {
            return null;
        }
    }

    /**
     * Returns the API Output by the api_output id.
     *
     * This method returns null if there is no api_output class registered under the specified id.
     *
     * @param string $api_output_id The API Output Id
     *
     * @return ApiOutput|null The API Output
     */
    public function getRegisteredApiOutputById(string $api_output_id): ApiOutput|null {
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
     * @param ApiOutput|string $api_output The API Output or class name
     *
     * @return string|null the API Output id
     */
    public function getRegisteredApiOutputId($api_output): string|null {
        if (is_object($api_output)) {
            $api_output = get_class($api_output);
        }

        $api_output_id = array_search($api_output, $this->_register['api_outputs']);

        return $api_output_id ? $api_output_id : null;
    }


    /** 
     * ============ PROVEDORES DE AUTENTICAÇÃO ============ 
     */

    /**
     * Registra um provedor de autenticação
     * 
     * @param string $name 
     * @return void 
     */
    public function registerAuthProvider(string $name) {
        $nextId = count($this->_register['auth_providers']) + 1;
        $this->_register['auth_providers'][$nextId] = strtolower($name);
    }

    /**
     * Retorna o id de um provedor de autenticação
     * @param mixed $name 
     * @return int|string|false 
     */
    public function getRegisteredAuthProviderId($name){
        return array_search(strtolower($name), $this->_register['auth_providers']);
    }


    /** 
     * ============ CONTROLADORES ============ 
     */

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
    public function registerController(string $id, string $controller_class_name, string $default_action = 'index', $view_dir = null) {
        $id = strtolower($id);

        if(key_exists($id, $this->_register['controllers']))
            throw new \Exception('Controller Id already in use');

        $this->_register['controllers-by-class'][$controller_class_name] = $id;

        $this->_register['controllers'][$id] = $controller_class_name;
        $this->_register['controllers_default_actions'][$id] = $default_action;
        $this->_register['controllers_view_dirs'][$id] = $view_dir ? $view_dir : $id;
    }

    /**
     * Retorna os controladores registrados
     * 
     * @param bool $return_controller_object 
     * 
     * @return array 
     */
    public function getRegisteredControllers(bool $return_controller_object = false): array {
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
     * @param string $controller_id
     *
     * @see Traits\Singleton::i()
     *
     * @return Controller|null
     */
    public function getController(string $controller_id): Controller|null {
        $controller_id = strtolower($controller_id);
        if(key_exists($controller_id, $this->_register['controllers']) && class_exists($this->_register['controllers'][$controller_id])){
            $class = $this->_register['controllers'][$controller_id];
            return $class::i($controller_id);
        }else{
            return null;
        }
    }

    /**
     * Alias to getController
     *
     * @param string $controller_id
     *
     * @see App::getController()
     *
     * @return Controller|null
     */
    public function controller(string $controller_id): Controller|null {
        return $this->getController($controller_id);
    }


    /**
     * Returns the controller of the given class.
     *
     * This method verifies if the controller is registered before try to get the instance to return.
     *
     * @param string $controller_class The controller class name.
     *
     * @return Controller|null The controller
     */
    public function getControllerByClass(string $controller_class): Controller|null {
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
     * @param Entity|string $entity The entity object or class name
     *
     * @see App::getControllerByClass()
     *
     * @return Controllers\EntityController|null The controller
     */
    public function getControllerByEntity(Entity|string $entity): Controller|null {
        if(is_object($entity))
            $entity = $entity->getClassName();
        
        $controller_class = $entity::getControllerClassName();
        return $this->getControllerByClass($controller_class);
    }

    /**
     * Returns the controller id of the class with the same name of the entity on the parent namespace.
     *
     * If the namespace is omited in the class name this method assumes MapasCulturais\Entities as the namespace of the entity.
     *
     * @param Entity|string $entity The entity object or class name
     *
     * @see App::getControllerId()
     *
     * @return Controller|null The controller
     */
    public function getControllerIdByEntity(Entity|string $entity): string|null {
        if(is_object($entity))
            $entity = $entity->getClassName();

        $controller_class = $entity::getControllerClassName();

        return $this->getControllerId($controller_class);
    }

    /**
     * Return the controller id of the given controller object or class.
     *
     * @param Controller|string $controller controller object or full class name
     *
     * @return string|null
     */
    public function getControllerId(Controller|string $controller): string|null {
        if(is_object($controller))
            $controller = get_class($controller);

        return array_search($controller, $this->_register['controllers']) ?: null;
    }

    /**
     * Alias to getControllerId.
     *
     * @param Controller|string $controller controller object or full class name
     *
     * @see App::getControllerId()
     *
     * @return string|null
     */
    public function controllerId(Controller|string $controller): string|null {
        return $this->getControllerId($controller);
    }


    /**
     * Returns the controller default action name.
     *
     * @param string $controller_id
     *
     * @return string|null
     */
    public function getControllerDefaultAction(string $controller_id): string|null {
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
     * @param string $controller_id
     *
     * @see App::getControllerDefaultAction()
     *
     * @return string|null
     */
    public function controllerDefaultAction(string $controller_id): string|null {
        return $this->getControllerDefaultAction($controller_id);
    }


    /** 
     * ============ TIPOS DE ENTIDADE ============ 
     */

    /**
     * Register an Entity Type Group.
     *
     * @param Definitions\EntityTypeGroup $group The Entity Type Group to register.
     */
    function registerEntityTypeGroup(Definitions\EntityTypeGroup $group){
        if(!key_exists($group->entity_class, $this->_register['entity_type_groups']))
                $this->_register['entity_type_groups'][$group->entity_class] = [];

        $this->_register['entity_type_groups'][$group->entity_class][] = $group;
    }

    /**
     * Returns the Entity Type Group of the given entity class and type id.
     *
     * @param Entity|string $entity The entity object or class name..
     * @param int $type_id The Entity Type id.
     *
     * @return Definitions\EntityTypeGroup|null
     */
    function getRegisteredEntityTypeGroupByTypeId(Entity|string $entity, int $type_id): Definitions\EntityTypeGroup|null {
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
     * @param Entity|string $entity The entity object or class name
     *
     * @return Definitions\EntityTypeGroup[]
     */
    function getRegisteredEntityTypeGroupsByEntity(Entity|string $entity): array {
        if(is_object($entity))
            $entity = $entity->getClassName();

        if(key_exists($entity, $this->_register['entity_type_groups'])){
            return $this->_register['entity_type_groups'][$entity];
        }else{
            return [];
        }
    }

    /**
     * Registra um tipo de entidade
     * 
     * @param Definitions\EntityType $type 
     * @return void 
     */
    function registerEntityType(Definitions\EntityType $type){
        if (!key_exists($type->entity_class, $this->_register['entity_types'])){
            $this->_register['entity_types'][$type->entity_class] = [];
        }

        $this->_register['entity_types'][$type->entity_class][$type->id] = $type;
    }

    /**
     * Retorna a definição do tipo de entidade
     * 
     * @param Entity|string $entity a entidade ou a classe
     * @param int|string $type_id 
     * @return mixed 
     * 
     * @throws ReflectionException 
     * @throws MappingException 
     */
    function getRegisteredEntityTypeById(Entity|string $entity, int|string|null $type_id): Definitions\EntityType|null {
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }

        return $this->_register['entity_types'][$entity][$type_id] ?? null;
    }

    /**
     * Verifica se o tipo de entidade existe
     *
     * @param Entity|string $entity a entidade ou a classe
     * @param int|string $type_id 
     *
     * @return boolean
     */
    function entityTypeExists(Entity|string $entity, int|string $type_id): bool {
        return !!$this->getRegisteredEntityTypeById($entity, $type_id);
    }

    /**
     * Retorna a definição do tipo de uma entidade
     *
     * @param Entity $entity 
     *
     * @return Definitions\EntityType
     */
    function getRegisteredEntityType(Entity $entity): Definitions\EntityType|null {
        return $this->_register['entity_types'][$entity->getClassName()][(string)$entity->type] ?? null;
    }

    /**
     * Retorna a definição de um tipo de entidade
     * 
     * @param Entity|string $entity 
     * @param string $type_name 
     * @return Definitions\EntityType|null 
     * 
     * @throws ReflectionException 
     * @throws MappingException 
     */
    function getRegisteredEntityTypeByTypeName(Entity|string $entity, string $type_name): Definitions\EntityType|null {
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
     * @param Entity|string $entity The entity.
     *
     * @return Definitions\EntityType[]
     */
    function getRegisteredEntityTypes(Entity|string $entity): array {
        if(is_object($entity))
            $entity = $entity->getClassName();

        return $this->_register['entity_types'][$entity] ?? [];
    }



    /** 
     * ============ TIPOS DE CAMPOS DE FORMULÁRIO DE OPORTUNIDADE ============ 
     */

    /**
     * Registra um tipo de campo de formulário de inscrição
     * 
     * @param RegistrationFieldType $registration_field 
     * @return void 
     */
    function registerRegistrationFieldType(Definitions\RegistrationFieldType $registration_field) {
        $this->_register['registration_fields'][$registration_field->slug] = $registration_field;
    }

    /**
     * Retornaos tipos de campo de formulário de inscrição registrados
     * 
     * @return Definitions\RegistrationFieldType[] 
     */
    function getRegisteredRegistrationFieldTypes(): array {
        return $this->_register['registration_fields'];
    }

    /**
     * Retorna a definição do campo de formulário de inscrição pelo slug informado
     * 
     * @param string $slug 
     * @return RegistrationFieldType|null 
     */
    function getRegisteredRegistrationFieldTypeBySlug(string $slug): Definitions\RegistrationFieldType|null {
        return $this->_register['registration_fields'][$slug] ?? null;
    }



    /** 
     * ============ METADADOS ============ 
     */

    /**
     * Registra um metadado para a entidade especificada
     *
     * @param Definitions\Metadata $metadata
     * @param string $entity_class 
     * @param int|string|null $entity_type_id
     */
    function registerMetadata(Definitions\Metadata $metadata, string $entity_class, int|string $entity_type_id = null) {
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

    /**
     * Desregistra um metadado de uma entidade
     * 
     * Se a chave do metadado não for informada, desregistra todos os metadados
     * 
     * @param string $entity_class 
     * @param string $key 
     * 
     * @return void 
     */
    function unregisterEntityMetadata(string $entity_class, string $key = null) {
        foreach($this->_register['entity_metadata_definitions'] as $class => $metadata){
            if($class === $entity_class || strpos($class . ':', $entity_class) === 0){
                if($key){
                    unset($this->_register['entity_metadata_definitions'][$class][$key]);
                } else {
                    $this->_register['entity_metadata_definitions'][$class] = [];
                }
            }
        }

    }

    /**
     * Returns an array with the Metadata Definitions of the given entity object or class name.
     *
     * If the given entity class has no registered metadata, returns an empty array
     *
     * @param Entity|string $entity
     *
     * @return Definitions\Metadata[]
     */
    function getRegisteredMetadata(Entity|string $entity, int|Definitions\EntityType $type = null){
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }

        $key = $entity::usesTypes() && $type ? "{$entity}:{$type}" : $entity;

        return key_exists($key, $this->_register['entity_metadata_definitions']) ? 
            $this->_register['entity_metadata_definitions'][$key] : [];
    }

    /**
     * Retorna a definição de um metadado
     * 
     * @param string $metakey
     * @param string $entity
     * @param int $type
     * @return Definitions\Metadata|null
     */
    function getRegisteredMetadataByMetakey(string $metakey, Entity|string $entity, int $type = null): Definitions\Metadata|null {
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }
        
        $metas = $this->getRegisteredMetadata($entity, $type);

        return $metas[$metakey] ?? null;
    }



    /** 
     * ============ GRUPOS DE ARQUIVO ============ 
     */

    /**
     * Register a new File Group Definition to the specified controller.
     *
     * @param string $controller_id The id of the controller.
     * @param Definitions\FileGroup $group The group to register
     */
    function registerFileGroup(string $controller_id, Definitions\FileGroup $group){
        $controller_id = strtolower($controller_id);

        if(!key_exists($controller_id, $this->_register['file_groups'])){
            $this->_register['file_groups'][$controller_id] = [];
        }

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
     * @return Definitions\FileGroup|null The File Group Definition
     */
    function getRegisteredFileGroup(string $controller_id, string $group_name): Definitions\FileGroup|null {
        return $this->_register['file_groups'][$controller_id][$group_name] ?? null;
    }

    /**
     * Retorna os grupos de arquivo registrados para a entidade
     * 
     * @param Entity|string $entity 
     * @return Definitions\FileGroup[]|null 
     * 
     * @throws ReflectionException 
     * @throws MappingException 
     */
    function getRegisteredFileGroupsByEntity(Entity|string $entity): array {
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }

        $controller_id = $this->getControllerIdByEntity($entity);

        return $this->_register['file_groups'][$controller_id] ?? [];
    }



    /** 
     * ============ TRANSFORMAÇÕES DE IMAGENS ============ 
     */

    /**
     * Register a new image transformation.
     *
     * @see Entities\File::_transform()
     *
     * @param string $name
     * @param string $transformation
     */
    function registerImageTransformation(string $name, string $transformation) {
        $this->_register['image_transformations'][$name] = trim($transformation);
    }

    /**
     * Returns the image transformation expression.
     *
     * @param string $name the transformation register name
     *
     * @return string The Transformation Expression
     */
    function getRegisteredImageTransformation(string $name): string {
        return $this->_register['image_transformations'][$name] ?? null;
    }



    /** 
     * ============ METALISTS ============ 
     */

    /**
     * Register a new MetaList Group Definition to the specified controller.
     *
     * @param string $controller_id The id of the controller.
     * @param Definitions\MetaListGroup $group The group to register
     */
    function registerMetaListGroup(string $controller_id, Definitions\MetaListGroup $group) {
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
     * @return Definitions\MetaListGroup|null The MetaList Group Definition
     */
    function getRegisteredMetaListGroup(string $controller_id, string $group_name): Definitions\MetaListGroup|null {
        return $this->_register['metalist_groups'][$controller_id][$group_name] ?? null;
    }

    /**
     * Retorna os grupos de metalists registrados para entiadde informada
     * 
     * @param Entity|string $entity 
     * 
     * @return Definitions\MetaListGroup[]
     * 
     * @throws ReflectionException 
     * @throws MappingException 
     */
    function getRegisteredMetaListGroupsByEntity(Entity|string $entity): array {
        if(is_object($entity))
            $entity = $entity->getClassName();

        $controller_id = $this->getControllerIdByEntity($entity);

        return $this->_register['metalist_groups'][$controller_id] ?? [];
    }

    /**
     * Register a Taxonomy Definition to an entity class.
     *
     * @param string $entity_class The entity class name to register.
     * @param Definitions\Taxonomy $definition
     */
    function registerTaxonomy($entity_class, Definitions\Taxonomy $definition) {
        if (!key_exists($entity_class, $this->_register['taxonomies']['by-entity'])) {
            $this->_register['taxonomies']['by-entity'][$entity_class] = [];
        }

        $this->_register['taxonomies']['by-entity'][$entity_class][$definition->slug] = $definition;

        $this->_register['taxonomies']['by-id'][$definition->id] = $definition;
        $this->_register['taxonomies']['by-slug'][$definition->slug] = $definition;
    }

    /**
     * Returns the Taxonomy Definition with the given id.
     *
     * @param int $taxonomy_id The id of the taxonomy to return
     *
     * @return Definitions\Taxonomy The Taxonomy Definition
     */
    function getRegisteredTaxonomyById($taxonomy_id): Definitions\Taxonomy|null {
        return $this->_register['taxonomies']['by-id'][$taxonomy_id] ?? null;
    }

    /**
     * Returns the Taxonomy Definition with the given slug.
     *
     * @param string $taxonomy_slug The slug of the taxonomy to return
     *
     * @return Definitions\Taxonomy The Taxonomy Definition
     */
    function getRegisteredTaxonomyBySlug(string $taxonomy_slug): Definitions\Taxonomy|null {
        return $this->_register['taxonomies']['by-slug'][$taxonomy_slug] ?? null;
    }

    /**
     * Returns an array with all registered taxonomies definitions to the given entity object or class name.
     *
     * If there is no registered taxonomies to the given entity returns an empty array.
     *
     * @param Entity|string $entity The entity object or class name
     *
     * @return Definitions\Taxonomy[] The Taxonomy Definitions objects or an empty array
     */
    function getRegisteredTaxonomies(Entity|string $entity = null): array {
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }

        if(is_null($entity)){
            return $this->_register['taxonomies']['by-slug'];
        }else{
            return $this->_register['taxonomies']['by-entity'][$entity] ?? [];
        }
    }

    /**
     * Returns the registered Taxonomy Definition with the given slug for the given entity object or class name.
     *
     * If the given entity don't have the given taxonomy slug registered, returns null.
     *
     * @param Entity|string $entity The entity object or class name.
     * @param string $taxonomy_slug The taxonomy slug.
     *
     * @return Definitions\Taxonomy|null The Taxonomy Definition.
     */
    function getRegisteredTaxonomy(Entity|string $entity, string $taxonomy_slug): Definitions\Taxonomy|null {
        if (is_object($entity)) {
            $entity = $entity->getClassName();
        }

        return $this->_register['taxonomies']['by-entity'][$entity][$taxonomy_slug] ?? null;
    }



    /** 
     * ============ MÉTODOS DE AVALIAÇÃO ============ 
     */

    /**
     * Registra um método de avaliação
     * 
     * @param Definitions\EvaluationMethod $def
     */
    function registerEvaluationMethod(Definitions\EvaluationMethod $def) {
        $this->_register['evaluation_method'][$def->slug] = $def;
    }


    /**
     * Returns the evaluation methods definitions
     * 
     * @param bool $return_internal 
     * 
     * @return Definitions\EvaluationMethod[];
     */
    function getRegisteredEvaluationMethods(bool $return_internal = false): array {
        return array_filter($this->_register['evaluation_method'], function(Definitions\EvaluationMethod $em) use ($return_internal) {
            if($return_internal || !$em->internal) {
                return $em;
            }
        });
    }

    /**
     * Desregistra um método de avaliação
     * 
     * @param Definitions\EvaluationMethod $def
     */
    function unregisterEvaluationMethod(string $slug){
        unset($this->_register['evaluation_method'][$slug]);
    }

    /**
     * Retorna o método de avaliação pelo slug
     *
     * @param string $slug
     *
     * @return Definitions\EvaluationMethod;
     */
    function getRegisteredEvaluationMethodBySlug(string $slug){
        return $this->_register['evaluation_method'][$slug] ?? null;
    }


    /**************************************
     *              DB UPDATES 
     **************************************/

    /**
     * Aplica os db-uptades
     */
    function _dbUpdates(){
        $this->disableAccessControl();

        $executed_updates = [];

        foreach($this->repo('DbUpdate')->findAll() as $update)
            $executed_updates[] = $update->name;

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
                        $update = new Entities\DbUpdate();
                        $update->name = $name;
                        $update->save(true);
                    }
                }catch(\Exception $e){
                    echo "\nERROR " . $e . "\n";
                }
                echo "\n-------------------------------------------------------------------------------------------------------\n\n";
            }
        }

        if($new_updates){
            $this->em->flush();
            $this->cache->deleteAll();
        }

        $this->enableAccessControl();
    }


    /** 
     * ============ MÉTODOS DE VERIFICAÇÃO DO CAPTCHA ============ 
     */
    function verifyCaptcha(string $token = '')
    {
        // If we don't receive the token, there is no reason to advance to the verification
        if (empty($token)) {
            return false;
        }

        // In this point we are sure that the provider was defined
        $provider = $this->config['captcha']['provider'];

        // If there are no providers available, it means that there was an error in the configuration
        // Because if it is the new configuration, the provider is mandatory
        // If it is the old one, the provider is defined by default
        if (!isset($this->config['captcha']['providers']) || empty($this->config['captcha']['providers'])) {
            throw new \Exception('No captcha providers defined');
        }

        // Is necessary to validate if the defined provider exists, because it may have been defined incorrectly in the new configuration
        if (!in_array($provider, array_keys($this->config['captcha']['providers']))) {
            return false;
        }

        // Using the defined provider
        $selectedProvider = $this->config['captcha']['providers'][$provider];

        // If the provider does not have the token validation address, do not advance
        if (empty($selectedProvider['verify'])) {
            throw new \Exception('No verify url defined for the selected provider');
        }

        // If the provider does not have the secret, do not advance or throw an exception?
        if (empty($selectedProvider['secret'])) {
            throw new \Exception('No secret defined for the selected provider');
        }

        // ############################# Start the verification process #############################
        // Prepare the request
        $options = [
            "http" => [
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query([
                    'secret' => $selectedProvider['secret'],
                    'response' => $token
                ])
            ]
        ];

        // Create the context
        $context = stream_context_create($options);
     
        // Send the request
        $result = file_get_contents($selectedProvider['verify'], false, $context);

        if ($result === false) {
            return false;
        }
     
        $result = json_decode($result);

        return $result->success;
    }
}
