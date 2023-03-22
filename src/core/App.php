<?php
declare(strict_types=1);

namespace MapasCulturais;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim;


use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;


/**
 * @property-read string $id
 * @property-read Slim\App $slim
 * @property-read Hooks $hooks
 * 
 * @package MapasCulturais
 */
class App {
    use Traits\MagicCallers,
        Traits\MagicGetter,
        Traits\MagicSetter;

    protected static array $_instances = [];

    protected string $id;

    protected Slim\App $slim;

    protected Hooks $hooks;
    
    /**
     * Persistent Cache
     * @var Cache
     */
    protected $cache = null;

    /**
     * Multisite Persistent Cache
     * @var Cache
     */
    protected $mscache = null;

    /**
     * Runtime Cache
     * @var Cache
     */
    protected $rcache = null;
    
    /**
     * App Configuration.
     * @var array
     */
    public $_config = [];
    
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

    protected function __construct(string $id) {
        $this->id = $id;

        $this->slim = AppFactory::create();
        $this->slim->addRoutingMiddleware();
        $this->slim->addErrorMiddleware(true, true, true);

        $this->hooks = new Hooks($this);
    }

    function init(array $config) {
        $this->_config = $config;

        $this->_initCache();
        $this->_initDoctrine();
    }

    public function run() {
        $this->applyHookBoundTo($this, 'mapasculturais.run:before');
        $this->slim->run();
        $this->persistPCachePendingQueue();
        $this->applyHookBoundTo($this, 'mapasculturais.run:after');
    }

    protected function _initCache() {
        $this->cache = new Cache($this->_config['app.cache']);
        $this->mscache = new Cache($this->_config['app.mscache']);
        
        $rcache_adapter = new \Symfony\Component\Cache\Adapter\ArrayAdapter(0, false);
        $this->rcache = new Cache($rcache_adapter);
    }

    
    protected EntityManager $_em;
    protected function _initDoctrine() {
        // annotation driver
        $doctrine_config = ORMSetup::createAnnotationMetadataConfiguration(
            paths: [__DIR__ . '/Entities/'],
            isDevMode: $this->_config['doctrine.isDev'],
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
        ], $doctrine_config);
        
        // obtaining the entity manager
        $this->_em = new EntityManager($connection, $doctrine_config);

        \MapasCulturais\DoctrineMappings\Types\Frequency::register();
        \MapasCulturais\DoctrineMappings\Types\Point::register();
        \MapasCulturais\DoctrineMappings\Types\Geography::register();
        \MapasCulturais\DoctrineMappings\Types\Geometry::register();


        // PhpEnumType::registerEnumTypes([
        //     DoctrineEnumTypes\ObjectType::getTypeName() => DoctrineEnumTypes\ObjectType::class,
        //     DoctrineEnumTypes\PermissionAction::getTypeName() => DoctrineEnumTypes\PermissionAction::class
        // ]);

        $platform = $this->_em->getConnection()->getDatabasePlatform();

        $platform->registerDoctrineTypeMapping('_text', 'text');
        $platform->registerDoctrineTypeMapping('point', 'point');
        $platform->registerDoctrineTypeMapping('geography', 'geography');
        $platform->registerDoctrineTypeMapping('geometry', 'geometry');
        $platform->registerDoctrineTypeMapping('object_type', 'object_type');
        $platform->registerDoctrineTypeMapping('permission_action', 'permission_action');
    }


    /** ********************************************
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
}
