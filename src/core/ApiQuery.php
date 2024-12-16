<?php

namespace MapasCulturais;

use Apps\Entities\UserApp;
use Doctrine\ORM\Query;
use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\User;
use MapasCulturais\Types\GeoPoint;

class ApiQuery {
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers;
    
    /**
     * Number of query objects to generate query ids
     * @var int
     */
    protected static $queryCounter = 0;
    
    /**
     * Id of this query
     * @var int
     */
    protected $__queryNum;

    /**
     * Global counter used to name DQL alias
     * @var int
     */
    protected $__counter = 0;
    
    /**
     * Maximum number of results before use subquery instead of a list of ids in secondary queries
     * @var int
     */
    protected $maxBeforeSubquery = 4096;
    
    /**
     * The ApiQuery Name
     * @var string
     */
    protected $name;
    
    /**
     * Doctrine Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Doctrine Entity Class Metadata
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $entityClassMetadata;

    protected $rootEntityClassName;

    protected $pk = 'id';

    /**
     * The Entity Class Name
     *
     * @example "MapasCulturais\Entities\Agent"
     * @var string
     */
    protected $entityClassName;

    /**
     * The Entity Metadata Class Name
     *
     * @example "MapasCulturais\Entities\AgentMeta"
     * @var string
     */
    protected $metadataClassName;
    
    /**
     * The Entity File Class Name
     *
     * @example "MapasCulturais\Entities\AgentFile"
     * @var string
     */
    protected $fileClassName;
    
    /**
     * The Entity Term Relation Class Name
     *
     * @example "MapasCulturais\Entities\AgentTermRelation"
     * @var string
     */
    protected $termRelationClassName;
    
    /**
     * The Entity Agent Relation Class Name
     *
     * @example "MapasCulturais\Entities\AgentAgentRelation"
     * @var string
     */
    protected $agentRelationClassName;
    
    /**
     * The Entity Space Relation Class Name
     *
     * @example "MapasCulturais\Entities\AgentSpaceRelation"
     * @var string
     */
    protected $spaceRelationClassName;
    
    /**
     * The Entity Permission Cache Class Name
     *
     * @example "MapasCulturais\Entities\AgentPermissionCache"
     * @var string
     */
    protected $permissionCacheClassName;

    /**
     * The Seal Relation Entity Class Name
     *
     * @example "MapasCulturais\Entities\AgentSealRelation"
     * @var string
     */
    protected $sealRelationClassName;
    
    /**
     * The entity controller
     * @var \MapasCulturais\Controllers\EntityController
     */
    protected $entityController;
    
    /**
     * The entity repository object
     * @var \MapasCulturais\Repository
     */
    protected $entityRepository;
    
    /**
     * The entity uses space relations?
     * @var bool
     */
    protected $usesSpaceRelations;
    
    /**
     * The entity uses agent relations?
     * @var bool
     */
    protected $usesAgentRelations;
    
    /**
     * The entity uses files?
     * @var bool
     */
    protected $usesFiles;
    
    /**
     * The entity uses MetaLists?
     * @var bool
     */
    protected $usesMetalists;
    
    /**
     * The entity uses permission cache?
     * @var bool
     */
    protected $usesPermissionCache;
    
    /**
     * The entity uses taxonomies?
     * @var bool
     */
    protected $usesTaxonomies;
    
    /**
     * The entity uses metadata?
     * @var bool
     */
    protected $usesMetadata;
    
    /**
     * The entity uses origin subsite?
     * @var bool
     */
    protected $usesOriginSubsite;
    
    /**
     * The entity uses seal relation?
     * @var bool
     */
    protected $usesSealRelation;
    
    /**
     * The entity uses types?
     * @var bool
     */
    protected $usesTypes;
    
    /**
     * The entity uses owner agent?
     * @var bool
     */
    protected $usesOwnerAgent;
    
    /**
     * The entity has the status property
     * @var bool
     */
    protected $usesStatus;
    
    /**
     * List of the entity properties
     * @var array
     */
    protected $entityProperties = [];

    /**
     * List of entity ralations
     * @var array
     */
    protected $entityRelations = [];

    /**
     * List of registered metadata to the requested entity for this context (subsite?)
     * @var array
     */
    protected $registeredMetadata = [];

    /**
     * List of the registered taxonomies for this context
     * @var array
     */
    protected $registeredTaxonomies = [];
    
    /**
     * List of subsite ids in which the logged in user is admin
     * @var array
     */
    protected $adminInSubsites = [];

    /**
     * the parameter of api query
     *
     * @example ['@select' => 'id,name', '@order' => 'name ASC', 'id' => 'GT(10)', 'name' => 'ILIKE(fulano%)']
     * @var array
     */
    protected $apiParams = [];

    /**
     * The SELECT part of the DQL that will be executed
     *
     * @example "e.id, e.name"
     * @var string
     */
    public $select = "";

    /**
     * The JOINs fo the DQL that will be executed
     * @var string
     */
    public $joins = "";

    /**
     * The WHERE part of the DQL that will be executed
     *
     * @example "e.id > 10"
     * @var string
     */
    public $where = "";

    /**
     * List of expressions used to compose the where part of the DQL that will be executed
     * @var array
     */
    protected $_whereDqls = [];

    /**
     * Mapping of the api query params to dql params
     * @var array
     */
    protected $_keys = [];

    /**
     * List of parameters that will be used to run the DQL
     * @var array
     */
    public $_dqlParams = [];

    /**
     * Fields that are being selected
     * @var array
     */
    protected $_selecting = [];

    /**
     * Slice of the fields that are being selected that are properties of the entity
     * @var array
     */
    protected $_selectingProperties = [];

    /**
     * Slice of the fields that are being selected that are metadata of the entity
     * @var array
     */
    protected $_selectingMetadata = [];
    
    /**
     * Slice of the fields that are being selected that are relations of the entity
     * @var array
     */
    protected $_selectingRelations = [];
    
    /**
     * Urls that are being selected
     * @var array
     */
    protected $_selectingUrls = [];

    /**
     * Agent relations that are being selected
     * @var array
     */
    protected $_selectingAgentRelations = [];

    /**
     * Related Agentes that are being selected
     * @var array
     */
    protected $_selectingRelatedAgents = [];

    /**
     * Space relations that are being selected
     * @var array
     */
    protected $_selectingSpaceRelations = [];

    /**
     * Related Spaces that are being selected
     * @var array
     */
    protected $_selectingRelatedSpaces = [];

    /**
     * Files that are being selected
     * @var array
     */
    protected $_selectingFiles = [];
    
    /**
     * Properties of files that are being selected
     * @var array
     */
    protected $_selectingFilesProperties = ['url'];

    /**
     * Metalists that are being selected
     * @var array
     */
    protected $_selectingMetalists = [];

    /**
     * Permissions that are being selected
     * @var array
     */
    protected $_selectingCurrentUserPermissions = [];

    /**
     * Indica se foi usado o formato permissionTo
     * @var array
     */
    protected $_usingLegacyPermissionFormat = false;

    /**
     * Indica se foi usado o formato @files na seleção
     * @var bool
     */
    protected $_usingLegacyImageSelectFormat = false;

    protected $_selectingIsVerfied = false;
    
    protected $_selectingVerfiedSeals = false;

    protected $_selectingSeals = false;
    
    /**
     * Subqueries configuration
     * @var array
     */
    protected $_subqueriesSelect = [];
    
    /**
     * Result Order
     *
     * @example 'name ASC'
     * @var string
     */
    protected $_order = 'id ASC';
    
    /**
     * Query offset
     * @var int
     */
    protected $_offset;
    
    /**
     * Maximum results to return
     * @var int
     */
    protected $_limit;
    
    /**
     * Page number. Used to create the query offset.
     * @var int
     */
    protected $_page;
    
    /**
     * Keyword filter
     * @var string
     */
    protected $_keyword;
    
    /**
     * Seals filter
     * @var array
     */
    protected $_seals = [];
    
    /**
     *
     * @var string
     */
    protected $_permission = [];
    
    protected $_subqueryFilters = [];
    protected $_op = ' AND ';
    protected $_templateJoinMetadata = "\n\tLEFT JOIN e.__metadata {ALIAS} WITH {ALIAS}.key = '{KEY}'";
    protected $_templateJoinTerm = "\n\tLEFT JOIN e.__termRelations {ALIAS_TR} LEFT JOIN {ALIAS_TR}.term {ALIAS_T} WITH {ALIAS_T}.taxonomy = '{TAXO}'";

    protected $_selectingOriginSiteUrl = false;
    protected $_selectingType = false;
    protected $_usingSubquery = false;
    
    protected $_subsiteId = false;

    protected $_selectAll = false;
    
    protected $_accessControlEnabled = true;

    /**
     * @var string prefixo dos hooks 
     */
    protected $hookPrefix;

    /**
     * Casts das ordenações
     * @var array
     */
    protected $orderCasts = [];

    /**
     *
     * @var ApiQuery
     */
    protected $parentQuery;

    public $__cacheTLS;
    public $__useDQLCache;
    
    public function __construct($entity_class_name, $api_params, $is_subsite_filter = false, $select_all = false, $disable_access_control = false, $parentQuery = null) {
        if($disable_access_control){
            $this->_accessControlEnabled = false;
        }

        if($parentQuery){
            $this->parentQuery = $parentQuery;
        }

        $this->_subsiteId = $is_subsite_filter;
        
        $this->_selectAll = $select_all;

        $this->initialize($entity_class_name, $api_params);

        $this->parseQueryParams();
    }

    /**
     * Initializes the ApiQuery properties
     *
     * @param string $class
     * @param array $api_params
     */
    protected function initialize($class, array $api_params) {
        $app = App::i();
        
        $_hook_class_path = $class::getHookClassPath();
        $this->hookPrefix = "ApiQuery({$_hook_class_path})";

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.params", [&$api_params]);
        
        $this->__queryNum = self::$queryCounter++;
        
        $this->em = $app->em;
        
        krsort($api_params);
        $this->apiParams = $api_params;
        
        // para quando se está consultando as oportunidades de uma outra entidade, por exemplo:
        // /api/agent/find?@select=id,name,ownedOpportunities
        if($class != Opportunity::class && strpos($class, Opportunity::class) === 0 && $this->parentQuery){
            $parent_class = $this->parentQuery->entityClassName;
            if($parent_class != 'MapasCulturais\Entities\Opportunity') {
                $class = $parent_class::getOpportunityClassName();
            }
        }

        $controller =  $app->getControllerByEntity($class::getClassName());

        if($class[0] == '\\'){
            $class = substr($class, 1);
        }

        $this->entityClassName = $class;
        $this->entityClassMetadata = $this->em->getClassMetadata($this->entityClassName);
        $this->rootEntityClassName = $this->entityClassMetadata->rootEntityName;
        
        $this->pk = $this->entityClassMetadata->identifier[0];

        $this->entityProperties = array_keys($this->entityClassMetadata->fieldMappings);
        $this->entityRelations = $this->entityClassMetadata->associationMappings;
        
        $this->entityController = $controller;
        $this->entityRepository = $app->repo($this->entityClassName);
        
        $this->usesFiles = $class::usesFiles();
        $this->usesMetalists = $class::usesMetalists();

        $this->usesAgentRelations = $class::usesAgentRelation();
        $this->usesSpaceRelations = $class::usesSpaceRelation();
        
        $this->usesPermissionCache = $class::usesPermissionCache();
        $this->usesTaxonomies = $class::usesTaxonomies();
        $this->usesMetadata = $class::usesMetadata();
        $this->usesOriginSubsite = $class::usesOriginSubsite();
        $this->usesOwnerAgent = $class::usesOwnerAgent();
        $this->usesTypes = $class::usesTypes();
        $this->usesSealRelation = $class::usesSealRelation();
        
        $this->usesStatus = in_array('status', $this->entityProperties);

        if ($this->usesFiles) {
            $this->fileClassName = $class::getFileClassName();
        }
        
        if ($this->usesPermissionCache) {
            $this->permissionCacheClassName = $class::getPermissionCacheClassName();
        }
        
        if ($this->usesAgentRelations) {
            $this->agentRelationClassName = $class::getAgentRelationEntityClassName();
        }
        
        if ($this->usesSpaceRelations) {
            $this->spaceRelationClassName = $class::getSpaceRelationEntityClassName();
        }

        if ($this->usesSealRelation) {
            $this->sealRelationClassName = $class::getSealRelationEntityClassName();
        }
        
        if ($this->usesTaxonomies) {
            $this->termRelationClassName = $class::getTermRelationClassName();
            
            foreach ($app->getRegisteredTaxonomies($class) as $obj) {
                $this->registeredTaxonomies['term:' . $obj->slug] = $obj->slug;
            }
        }
        
        if ($this->usesMetadata) {
            $this->metadataClassName = $class::getMetadataClassName();

            foreach ($app->getRegisteredMetadata($class) as $meta) {
                $this->registeredMetadata[] = $meta->key;
            }
        }
        
        if($this->usesOriginSubsite){
            if(!$app->user->is('guest')){
                foreach($app->user->roles as $role){
                    if($role->name == 'admin' || $role->name == 'superAdmin'){
                        $this->adminInSubsites[] = $role->subsiteId;
                    }
                }
            }
        }

        $controller_id = $controller->id ?? -1;
        $this->__useDQLCache = $app->config['app.useApiCache'];
        $this->__cacheTLS = $app->config['app.apiCache.lifetimeByController'][$controller_id] ?? $app->config['app.apiCache.lifetime'];

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.init:after");

    }
    
    protected function getAlias($name){
        if(!$name){
            $name = uniqid();
        }
        $num = $this->__counter++;
        return "q{$this->__queryNum}_{$name}{$num}";
    }
    
    
    public function logDql($dql, $action, $params = []){
        $app = App::i();
        if($app->config['app.log.apiDql']){
            $dql = str_replace("\n", "\n\t\t", "\t\t$dql");
            $name = $this->name ? "({$this->name})" : '';
            $log = "\n\nAPI DQL$name --> $action ( $this->entityClassName )\n\n$dql\n";
            $_s1 = [];
            $_s2 = [];
            foreach($params as $k => $v){
                $_s1[] = ":$k";
                $_s2[] = '`'. $v . '`';
            }
            
            $log = str_replace($_s1, $_s2, $log);
            
            $app->log->debug($log);
        }
        
    }

    protected $_findingIds = false;

    public function findIds() {
        $this->_findingIds = true;
        $pk = $this->pk ?: 'id';
        $result = $this->getFindResult("e.{$pk}");
        $this->_findingIds = false;

        return array_map(function ($row) {
            return $row[$this->pk];
        }, $result);
    }

    public function findOne(){
        return $this->getFindOneResult();
    }
    
    public function getFindOneResult() {
        $app = App::i();
        
        $cache_key = $this->getCacheKey(__METHOD__, offset: $this->getOffset());

        if($app->rcache->contains($cache_key)) {
            return $app->rcache->fetch($cache_key);
        }

        if ($this->entityClassMetadata->inheritanceType == 3 && $this->entityClassMetadata->subClasses) {
            $result = $this->getSubClassesResult();
            if(!empty($result)) {
                return $result[0];
            }
        }

        $dql = $this->getFindDQL();

        $q = $this->em->createQuery($dql);

        if($this->__useDQLCache){
            $q->enableResultCache($this->__cacheTLS);
        }

        if ($offset = $this->getOffset()) {
            $q->setFirstResult($offset);
        }

        $q->setMaxResults(1);

        $params = $this->getDqlParams();

        $q->setParameters($params);

        $result = $q->getOneOrNullResult(Query::HYDRATE_ARRAY);
        
        $this->logDql($dql, __FUNCTION__, $params);

        if ($result) {
            $_tmp = [&$result]; // php !!!!
            $this->processEntities($_tmp);

        }

        $app->rcache->save($cache_key, $result);

        return $result;
    }

    public function find(){
        return $this->getFindResult();
    }

    protected function getSubClassesResult() {
        $ids = $this->findIds();
        $entities = [];
        $subclasses = $this->entityClassMetadata->subClasses;
        $main_class = $this->entityClassName;
        foreach($subclasses as $subclass) {
            $this->entityClassName = $subclass;
            $this->entityClassMetadata = $this->em->getClassMetadata($this->entityClassName);
            $this->entityProperties = array_keys($this->entityClassMetadata->fieldMappings);
            $this->entityRelations = $this->entityClassMetadata->associationMappings;

            $entities = array_merge($entities, $this->getFindResult());
        }
        $this->entityClassName = $main_class;
        $this->entityClassMetadata = $this->em->getClassMetadata($this->entityClassName);
        $this->entityProperties = array_keys($this->entityClassMetadata->fieldMappings);
        $this->entityRelations = $this->entityClassMetadata->associationMappings;

        $result = [];
        foreach ($ids as $id) {
            foreach($entities as $entity) {
                if($entity[$this->pk] == $id) {
                    $result[] = $entity;
                }
            }
        }
        return $result;
    }

    function getCacheKey($method, string $select = null, $offset = null, $limit = null) {
        $dql = $this->getFindDQL($select);
        $params = $this->getDqlParams();

        return md5(print_r([
            $method,
            str_replace(array_keys($params), array_values($params), $dql),
            $offset,
            $limit
        ], true));
    }
    
    private $__inSubclassesQuery = false;
    public function getFindResult(string $select = null) {
        $app = App::i();

        $cache_key = $this->getCacheKey(__METHOD__, $select, $this->getOffset(), $this->getLimit());

        if($app->rcache->contains($cache_key)) {
            return $app->rcache->fetch($cache_key);
        }

        if (!$this->_findingIds && !$this->__inSubclassesQuery && $this->entityClassMetadata->inheritanceType == 3 && $this->entityClassMetadata->subClasses) {
            $this->__inSubclassesQuery = true;
            $result = $this->getSubClassesResult();
            $this->__inSubclassesQuery = false;
        } else {
            $dql = $this->getFindDQL($select);
            $q = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $q->enableResultCache($this->__cacheTLS);
            }
    
            if ($offset = $this->getOffset()) {
                $q->setFirstResult($offset);
            }
    
            if ($limit = $this->getLimit()) {
                $q->setMaxResults($limit);
            }
    
            $params = $this->getDqlParams();
    
            $q->setParameters($params);
            $this->logDql($dql, __FUNCTION__, $params);
            
            $result = [];
            $ids = [];
            
            // removes duplicated values
            foreach($q->getResult(Query::HYDRATE_ARRAY) as $r){
                if(!isset($ids[$r[$this->pk]])){
                    $ids[$r[$this->pk]] = true;
                    $result[] = $r;
                }
            }
    
            if(!$this->_findingIds) {
                $this->processEntities($result);
            }
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.findResult", [&$result]);

        $app->rcache->save($cache_key, $result);

        return $result;
    }

    
    public function count(){
        return $this->getCountResult();
    }
    
    public function getCountResult() {
        $app = App::i();

        $cache_key = $this->getCacheKey(__METHOD__, offset: $this->getOffset(), limit: $this->getLimit());

        if($app->rcache->contains($cache_key)) {
            return $app->rcache->fetch($cache_key);
        }

        $dql = $this->getCountDQL();

        $q = $this->em->createQuery($dql);
        if($this->__useDQLCache){
            $q->enableResultCache($this->__cacheTLS);
        }
        
        $params = $this->getDqlParams();

        $q->setParameters($params);
        
        $this->logDql($dql, __FUNCTION__, $params);

        $result = $q->getSingleScalarResult();

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.countResult", [&$result]);

        $app->rcache->save($cache_key, $result);

        return $result;
    }
    
    function getDqlParams(){
        $params = $this->_dqlParams;
        
        $subqueries = $this->getSubqueryFilters();
        foreach($subqueries as $filter){
            $params += $filter['subquery']->getDqlParams();
        }
        
        return $params;
    }

    public function getFindDQL(string $select = null) {
        $where = $this->generateWhere();
        $order = $this->generateOrder();
        $joins = $this->generateJoins();
        if($select) {
            foreach($this->orderCasts as $order_cast) {
                $select .= ", $order_cast";
            }
        } else {
            $select = $this->generateSelect();
        }

        $dql = "SELECT\n\t{$select}\nFROM \n\t{$this->entityClassName} e {$joins}";
        if ($where) {
            $dql .= "\nWHERE\n\t{$where}";
        }

        if ($order) {
            if($this->entityClassName === UserApp::class) {
                $dql .= "\n\nORDER BY {$order}";
            } else {
                $dql .= "\n\nORDER BY {$order}, e.id ASC";
            }
        } else {
            $dql .= "\n\nORDER BY e.id ASC";
        }

        return $dql;
    }

    public function getCountDQL() {
        $where = $this->generateWhere();
        $joins = $this->generateJoins();

        $dql = "SELECT\n\tCOUNT(e.{$this->pk})\nFROM \n\t{$this->entityClassName} e {$joins}";
        if ($where) {
            $dql .= "\nWHERE\n\t{$where}";
        }

        return $dql;
    }

    public function getSubDQL($prop = null, $cast = null) {
        if(is_null($prop)) {
            $prop = $this->pk;
        }

        $where = $this->generateWhere();
        $joins = $this->generateJoins();

        $alias = 'e_' . uniqid();
        
        if(isset($this->entityRelations[$prop])){
            $identity = "IDENTITY({$alias}.{$prop})";
        } else {
            if($cast){
                switch(strtolower($cast)) {
                    case 'string':
                        $cast = 'VARCHAR';
                        break;
                    case 'int':
                        $cast = 'INTEGER';
                        break;
                    case 'bool':
                        $cast = 'BOOLEAN';
                        break;
                }
                $identity = "CAST({$alias}.{$prop} AS {$cast})";
            } else {
                $identity = "{$alias}.{$prop}";
            }
        }
        $dql = " SELECT $identity FROM {$this->entityClassName} {$alias} {$joins} ";
        if ($where) {
            $dql .= " WHERE {$where} ";
        }

        $result = preg_replace('#([^a-z0-9_])e([\. ])#i', "$1{$alias}$2", $dql);

        // faz os alias dos joins de metadados serem únicos
        if(preg_match_all('#\.__metadata ([\w\d]+) WITH #i', $result, $matches)){
            foreach($matches[1] as $alias) {
                $result = str_replace($alias, uniqid ("{$alias}__"), $result);
            }
        }
        
        // faz os alias dos joins dos termRelations serem únicos
        if(preg_match_all('#\.__termRelations ([\w\d]+) LEFT#i', $result, $matches)){
            foreach($matches[1] as $alias) {
                $result = str_replace($alias, uniqid ("{$alias}__"), $result);
            }
        }

        // faz os alias dos joins de termos serem únicos
        if(preg_match_all('#\.term ([\w\d]+) WITH#i', $result, $matches)){
            foreach($matches[1] as $alias) {
                $result = str_replace($alias, uniqid ("{$alias}__"), $result);
            }
        }
        return $result;
    }
    
    protected function getSelecting(){
        return $this->_selecting;
    }

    protected function getSubqueryInIdentities(array $entities, $property = null, $force_ids = false) {
        if(is_null($property)) {
            $property = $this->pk;
        }
        if (count($entities) > $this->maxBeforeSubquery) {
            $this->_usingSubquery = true;
            $result = $this->getSubDQL($property);
        } else {
            $identity = [];
            foreach($entities as $entity){
                if(isset($entity[$property])){
                    $identity[] = $entity[$property];
                }
            }
            if(!$identity){
                $result = null;
            } else {
                $result = implode(',', array_unique($identity));
            }
        }
        
        return $result;
    }
    
    function getKeywordSubDQL(){
        $subdql = '';
        if($this->_keyword){
            $dqls = [];
            $keywords = explode(';', $this->_keyword);
            $alias = $this->getAlias('kword');
            foreach($keywords as $i => $keyword) {
                $keyword = trim($keyword);
                if(empty($keyword)) {
                    continue;
                }
                $kword_alias = 'kword'.md5($keyword);
                
                if($i === 0) {
                    $_keyword_dql = $this->entityRepository->getIdsByKeywordDQL($keyword, $kword_alias);
                } else {
                    $_keyword_dql = $this->entityRepository->getKeywordDQLWhere($keyword, $kword_alias);
                }
                
                $_keyword_dql = preg_replace('#([^a-z0-9_])e\.#i', "$1{$alias}.", $_keyword_dql);
                foreach ($this->entityClassMetadata->subClasses as $class) {
                    $_keyword_dql = str_replace("{$class} e", "{$class} {$alias}", $_keyword_dql);
                }
                foreach ($this->entityClassMetadata->parentClasses as $class) {
                    $_keyword_dql = str_replace("{$class} e", "{$class} {$alias}", $_keyword_dql);
                }
                $_keyword_dql = str_replace("{$this->entityClassName} e", "{$this->entityClassName} {$alias}", $_keyword_dql);
            
                $dqls[] = "$_keyword_dql";
                $this->_dqlParams[$kword_alias] = "%{$keyword}%";
            }
            $subdql = "e.{$this->pk} IN (". implode(' OR ', $dqls) . ')';
        }

        return $subdql;
    }

    function getLimit() {
        return $this->_limit;
    }

    function getOffset() {
        if ($this->_offset) {
            return $this->_offset;
        } else if ($this->_page && $this->_page > 1 && $this->_limit) {
            return $this->_limit * ($this->_page - 1);
        } else {
            return 0;
        }
    }
    
    function getSubqueryFilters(){
        $app = App::i();
        
        $filters = $this->_subqueryFilters;
        
        if(!$this->_subsiteId){
            if($subsite = $app->getCurrentSubsite()){
                $subsite_query = $subsite->getApiQueryFilter($this->entityClassName);

                $app->applyHookBoundTo($this, "{$this->hookPrefix}.subsiteFilters", [&$subsite_query]);

                if($subsite_query){
                    $filters[] = ['subquery' => $subsite_query, 'subquery_property' => $this->pk, 'property' => $this->pk];
                }
            }
        }
        
        $app->applyHookBoundTo($this, "{$this->hookPrefix}.subqueryFilters", [&$filters]);

        return $filters;
    }

    protected function generateWhere() {
        $app = App::i();

        $where = $this->where;
        $where_dqls = implode(" $this->_op \n\t", $this->_whereDqls);
        
        if($where){
            $where = $where_dqls ? "$where AND $where_dqls" : $where;
        } else {
            $where = $where_dqls;
        }

        if($this->usesStatus && (!isset($this->apiParams['status']) || !$this->_permission)){
            $params = $this->apiParams;
            
            if($this->rootEntityClassName === Opportunity::class && (isset($params['id']) || isset($params['status']) || isset($params['parent']))) {
                $where_status = '(e.status > 0 OR e.status = -1)';    
            } else {
                $where_status = 'e.status > 0';
            }
            $where = $where ? "($where) AND $where_status" : $where_status;
        }
        
        if($keyword_where = $this->getKeywordSubDQL()){
            $where .= " AND $keyword_where";
        }
        
        $filters = $this->getSubqueryFilters();
        
        foreach($filters as $filter){
            /** @var ApiQuery */
            $subquery = $filter['subquery'];
            $subquery_property = $filter['subquery_property'];
            $property = $filter['property'];
            
            $property_type = $this->entityClassMetadata->fieldMappings[$property]['type'] ?? false;

            $sub_dql = $subquery->getSubDQL($subquery_property, $property_type);
            
            $where .= " AND e.{$property} IN ({$sub_dql})";
        }
        
        if(!$where) {
            $where = "1 = 1";
        }
        
        if($this->_subsiteId){
            $where = "($where) OR e._subsiteId = {$this->_subsiteId}";

            if($this->entityClassName == "MapasCulturais\Entities\Agent" && App::i()->auth->isUserAuthenticated()) { // entidade é agent e o usuário esta logado?
                $userID = App::i()->user->id;
                $where = "$where OR e.userId = {$userID}"; //Adiciona todos os agentes pertecentes ao usuário a resposta.
            }
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.where", [&$where]);

        return $where;
    }

    protected function generateJoins() {
        $app = App::i();
        $joins = $this->joins;
        $class = $this->entityClassName;
        
        if($this->_selectingOriginSiteUrl && $this->usesOriginSubsite){
            $subsite_alias = $this->getAlias('subsite');
            $joins .= " LEFT JOIN MapasCulturais\Entities\Subsite {$subsite_alias} WITH {$subsite_alias}.id = e._subsiteId";
        }
        
        if($this->usesSealRelation && $this->_seals){
            $sl = $this->getAlias('sl');
            $slv = implode(',', $this->_seals);
            $joins .= " JOIN e.__sealRelations {$sl} WITH {$sl}.seal IN ($slv)";
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.joins", [&$joins]);

        return $joins;
    }
    
    protected $_removeFromResult = [];

    protected function generateSelect() {
        $app = App::i();

        $select = $this->select;

        if(!in_array($this->pk, $this->_selectingProperties)){
            $this->_selectingProperties = array_merge([$this->pk], $this->_selectingProperties);
        }

        if($this->entityClassName == 'MapasCulturais\Entities\Registration'){
            $this->_selectingProperties = array_merge(['number'], $this->_selectingProperties);
        }
        
        if (count($this->_selectingProperties) >= 1 && in_array('publicLocation', $this->entityProperties) && !in_array('publicLocation', $this->_selectingProperties)) {
            $this->_selectingProperties[] = 'publicLocation';
            $this->_removeFromResult[] = 'publicLocation';
        }
        
        if (count($this->_selectingProperties) >= 1 && !in_array('_subsiteId', $this->_selectingProperties) && $this->usesOriginSubsite) {
            $this->_selectingProperties[] = '_subsiteId';
            $this->_removeFromResult[] = '_subsiteId';
        }
        
        $_select = implode(', ', array_map(function ($e) {
            return "e.{$e}";
        }, $this->_selectingProperties));
        
        if($select && $_select){
            $select = "$_select, $select";
            
        } else if ($_select) {
           $select = $_select;
        }
                
        // to prevent new queries when selecting only the id of relations
        foreach ($this->_subqueriesSelect as $key => &$cfg) {
            $prop = $cfg['property'];
            $mapping = null;
            if(isset($this->entityRelations[$prop])){
                $mapping = $this->entityRelations[$prop];
            } else if(isset($this->entityRelations['_' . $prop])) {
                $mapping = $this->entityRelations['_' . $prop];
            }
            if ($mapping && ($mapping['type'] === 2 || $mapping['type'] === 1) && $mapping['isOwningSide']) {
                $select .= ", IDENTITY(e.{$prop}) AS $prop";
                $cfg['selected'] = true;

                if ($cfg['select'] === $this->pk) {
                    $cfg['skip'] = true;
                }
            }
        }
        
        if($this->_selectingOriginSiteUrl){
            $select .= ', __subsite__.url AS originSiteUrl';
        }
        
        foreach($this->orderCasts as $order_cast) {
            $select .= ", $order_cast";
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.select", [&$select]);

        return $select;
    }

    protected function generateOrder() {
        if ($this->_order) {
            $order = [];
            $_order = null;
            foreach (explode(',', $this->_order) as $prop) {
                $key = trim(preg_replace('#asc|desc#i', '', $prop));
                $cast = null;
                if(preg_match('#(\w+) +AS +(\w+)#i', $key, $matches)) {
                    $key = $matches[1];
                    $cast = $matches[2];
                    $prop = str_replace($matches[0], $key, $prop);

                    if (!in_array(strtoupper($cast), ['VARCHAR', 'INTEGER', 'FLOAT'])) {
                        throw new Exception(i::__('CAST inválido para a ordenação'));
                    }
                }
                if (key_exists($key, $this->_keys)) {
                    $_order = str_ireplace($key, $this->_keys[$key], $prop);
                } elseif (in_array($key, $this->entityProperties)) {
                    $_order = str_ireplace($key, 'e.' . $key, $prop);
                } elseif (in_array($key, $this->registeredMetadata)) {
                    
                    $meta_alias = $this->getAlias('meta_'.$key);
                    
                    $this->joins .= str_replace(['{ALIAS}', '{KEY}'], [$meta_alias, $key], $this->_templateJoinMetadata);

                    $_order = str_replace($key, "$meta_alias.value", $prop);

                // ordenação de usuário pelo nome do agente profile
                } else if ($this->entityClassName == User::class && $key == 'name') {
                    $this->joins .= "\n\tLEFT JOIN e.profile __profile__";
                    $_order = str_replace($key, "__profile__.name", $prop);
                }

                if($_order) {
                    if ($cast) {
                        $new_oder = str_replace('.', '_', preg_replace('#^([^ ]+)#', '$1_' . $cast, $_order));
                        $alias = preg_replace("# .*#", '', $new_oder);
                        $_prop = preg_replace("# .*#", '', $_order);
                        $order_cast = "CAST({$_prop} AS $cast) AS HIDDEN $alias";
                        if(!in_array($order_cast, $this->orderCasts)){
                            $this->orderCasts[] = $order_cast;
                        }
                        $_order = $new_oder;

                    }
                    $order[] = $_order;
                }
            }
            return implode(', ', $order);
        } else {
            return null;
        }
    }
    
    protected function processEntities(array &$entities) {
        if(empty($entities)) {
            return;
        }

        $this->appendCurrentUserPermissions($entities);
        $this->appendMetadata($entities);
        $this->appendRelations($entities);
        $this->appendTerms($entities);
        $this->appendMetalists($entities);
        $this->appendFiles($entities);
        $this->appendAgentRelations($entities);
        $this->appendRelatedAgents($entities);
        $this->appendSpaceRelations($entities);
        $this->appendRelatedSpaces($entities);
        $this->appendIsVerified($entities);
        $this->appendVerifiedSeals($entities);
        $this->appendSeals($entities);
        
        $app = app::i();
        
        // @TODO: não existe hoje uma forma de conseguir a url do site principal
        $main_site_url = $app->config['base.url'];
        
        if($this->_selectingType){
            $types = $app->getRegisteredEntityTypes($this->entityClassMetadata->rootEntityName ?? $this->entityClassName);
        }

        if($this->permissionCacheClassName){
            $permissions = $this->getViewPrivateDataPermissions($entities);
        }
        
        foreach ($entities as $index => &$entity){
            // remove location if the location is not public
            if($this->permissionCacheClassName && isset($entity['location']) && isset($entity['publicLocation']) && !$entity['publicLocation']){
                if(!$permissions[$entity[$this->pk]]){
                    if (isset($this->apiParams['location']) || isset($this->apiParams['_geoLocation'])) {
                        unset($entities[$index]);
                    }
                    $entity['location']->latitude = 0;
                    $entity['location']->longitude = 0;
                }
            }

            foreach($this->_selectingUrls as $action){
                $entity["{$action}Url"] = $this->entityController->createUrl($action, [$entity[$this->pk]]);
            }
            
            // convert Occurrences rules to object
            if (key_exists('rule', $entity) && !empty($entity['rule'])) {
                $entity['rule'] = json_decode($entity['rule']);
            }
            
            if($this->_selectingOriginSiteUrl && empty($entity['originSiteUrl'])){
                $entity['originSiteUrl'] = $main_site_url;
            }
            if($this->_selectingType && isset($entity['_type'])){
                $entity['type'] = $types[$entity['_type']];
                unset($entity['_type']);
            }
            
            foreach($this->_selecting as $prop){    
                if($prop && $prop[0] != '#' && !isset($entity[$prop])){
                    $entity[$prop] = null;
                }
            }
            
            foreach($this->_removeFromResult as $prop){
                unset($entity[$prop]);
            }
            
            foreach($this->_selecting as $prop){
                if(!$prop){
                    continue;
                }
                if($prop[0] === '#'){
                    $prop = array_search($prop, $this->_selectingRelations);
                    if(!$prop){
                        continue;
                    }
                }
                if(!isset($entity[$prop])){
                    $entity[$prop] = null;
                }
            }

            $class = $this->entityClassName;
            $entity['@entityType'] = $this->entityController->id ?? $class::getControllerId();
        }

        $entities = array_values($entities);
    }

    protected function appendMetadata(array &$entities) {
        $app = App::i();
        $metadata = [];
        $definitions = $app->getRegisteredMetadata($this->entityClassMetadata->rootEntityName ?? $this->entityClassName);

        if ($this->_selectingMetadata && count($entities) > 0) {
            
            $permissions = $this->getViewPrivateDataPermissions($entities);
            $meta_keys = [];
            foreach ($this->_selectingMetadata as $meta) {
                $meta_keys[uniqid('p')] = $meta;
            }

            $keys = ':' . implode(',:', array_keys($meta_keys));

            $in_entities_dql = $this->getSubqueryInIdentities($entities);
            
            if (!$in_entities_dql) {
                return;
            }

            $dql = "
                SELECT
                    e.key,
                    e.value,
                    IDENTITY(e.owner) AS objectId
                FROM
                    {$this->metadataClassName} e
                WHERE
                    e.owner IN ({$in_entities_dql}) AND
                    e.key IN({$keys})
                ORDER BY e.{$this->pk}";

            $q = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $q->enableResultCache($this->__cacheTLS);
            }

            if($this->_usingSubquery){
                $q->setParameters($meta_keys + $this->getDqlParams());
            } else {
                $q->setParameters($meta_keys);
            }
            
            foreach ($q->getArrayResult() as $meta) {
                if (!isset($metadata[$meta['objectId']])) {
                    $metadata[$meta['objectId']] = [];
                }
                $metadata[$meta['objectId']][$meta['key']] = $meta['value'];
            }
        }

        foreach ($entities as &$entity) {
            $entity_id = $entity[$this->pk];
            
            if (isset($metadata[$entity_id])) {
                
                $can_view = $permissions[$entity_id] ?? false;
                
                $meta = $metadata[$entity_id];
                foreach($meta as $k => $v){
                    $private = $definitions[$k]->private;
                    if($private instanceof \Closure){ 
                        $private = \Closure::bind($private, (object) $entity);
                        if($private() && !$can_view){
                            unset($meta[$k]);
                        }
                    }else if($private && !$can_view){
                        unset($meta[$k]);
                    }
                }
                
                $entity += $meta;

                foreach($meta as $k => &$v){
                    $unserialize = $definitions[$k]->unserialize;
                    if($unserialize) {
                        $entity[$k] = $unserialize($v, (object) $entity);
                    }
                }
            }
        }
    }    
    
    protected function appendRelations(array &$entities) {
        if ($this->_subqueriesSelect) {
            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities);
            if(!$_subquery_where_id_in){
                return;
            }
            foreach ($this->_subqueriesSelect as $k => &$cfg) {

                $special_relations = [
                    'files' => '_selectingFiles',
                    'metalists' => '_selectingMetalists',
                    'currentUserPermissions' => '_selectingCurrentUserPermissions',
                    'permissionTo' => '_selectingCurrentUserPermissions',
                    'agentRelations' => '_selectingAgentRelations',
                    'relatedAgents' => '_selectingRelatedAgents',
                ];

                $is_special = false;
                foreach ($special_relations as $prop => $_selecting) {
                    if($cfg['property'] == $prop) {
                        if ($prop == 'permissionTo') { 
                            $this->_usingLegacyPermissionFormat = true;
                        }

                        if($cfg['selectAll']) {
                            $this->$_selecting[] = "*";
                        } else {
                            foreach($cfg['select'] as $sub) {
                                if(!in_array("$sub.*", $this->$_selecting)) {
                                    $this->$_selecting[] = $prop === 'files' ? "$sub.*" : $sub;
                                }
                            }
                        }
    
                        $is_special = true;
                    }
                }

                if ($is_special) {
                    continue;
                }

                $prop = $cfg['property'];
                
                // do usuário só permite id e profile
                if($prop == 'user') {
                    $cfg['select'] = array_filter($cfg['select'], function($field) {
                        if ($field == 'id' || substr($field, 0, 7) == 'profile') {
                            return $field;
                        }
                    });
                }

                if(isset($this->entityRelations[$prop])){
                    $mapping = $this->entityRelations[$prop];
                } else if(isset($this->entityRelations['_' . $prop])){
                    $mapping = $this->entityRelations['_' . $prop];
                } else if($prop == 'user'){
                    $mapping = [
                        'targetEntity' => 'MapasCulturais\Entities\User',
                        'type' => 2,
                        'users' => array_map(function($e) { return $e['user']; }, $entities)
                    ];
                        
                    $cfg['selected'] = true;
                } else {
                    continue;
                }
/**
 * types de mapeamentos
 * 1 - OneToOne
 * 2 - ManyToOne
 * 4 - OneToMany
 * 
===> ManyToOne <===    
"parent" => [
    "fieldName" => "parent",
    "joinColumns" => [
        [
        "name" => "parent_id",
        "unique" => false,
        "nullable" => true,
        "onDelete" => "CASCADE",
        "columnDefinition" => null,
        "referencedColumnName" => "id",
        ],
    ],
    "cascade" => [],
    "inversedBy" => null,
    "targetEntity" => "MapasCulturais\Entities\Opportunity",
    "fetch" => 2,
    "type" => 2,
    "mappedBy" => null,
    "isOwningSide" => true,
    "sourceEntity" => "MapasCulturais\Entities\Opportunity",
    "isCascadeRemove" => false,
    "isCascadePersist" => false,
    "isCascadeRefresh" => false,
    "isCascadeMerge" => false,
    "isCascadeDetach" => false,
    "sourceToTargetKeyColumns" => [
        "parent_id" => "id",
    ],
    "joinColumnFieldNames" => [
        "parent_id" => "parent_id",
    ],
    "targetToSourceKeyColumns" => [
        "id" => "parent_id",
    ],
    "orphanRemoval" => false,
    "inherited" => "MapasCulturais\Entities\Opportunity",
    "declared" => "MapasCulturais\Entities\Opportunity",
],

===> OneToMany <===
"_children" => [
    "fieldName" => "_children",
    "mappedBy" => "parent",
    "targetEntity" => "MapasCulturais\Entities\Opportunity",
    "cascade" => [
        "remove",
    ],
    "orphanRemoval" => false,
    "fetch" => 2,
    "type" => 4,
    "inversedBy" => null,
    "isOwningSide" => false,
    "sourceEntity" => "MapasCulturais\Entities\Opportunity",
    "isCascadeRemove" => true,
    "isCascadePersist" => false,
    "isCascadeRefresh" => false,
    "isCascadeMerge" => false,
    "isCascadeDetach" => false,
    "inherited" => "MapasCulturais\Entities\Opportunity",
    "declared" => "MapasCulturais\Entities\Opportunity",
],

===> OneToOne <===
"evaluationMethodConfiguration" => [
    "fieldName" => "evaluationMethodConfiguration",
    "targetEntity" => "MapasCulturais\Entities\EvaluationMethodConfiguration",
    "joinColumns" => [],
    "mappedBy" => "opportunity",
    "inversedBy" => null,
    "cascade" => [],
    "orphanRemoval" => false,
    "fetch" => 2,
    "type" => 1,
    "isOwningSide" => false,
    "sourceEntity" => "MapasCulturais\Entities\Opportunity",
    "isCascadeRemove" => false,
    "isCascadePersist" => false,
    "isCascadeRefresh" => false,
    "isCascadeMerge" => false,
    "isCascadeDetach" => false,
    "inherited" => "MapasCulturais\Entities\Opportunity",
    "declared" => "MapasCulturais\Entities\Opportunity",
],

    ===> OneToOne <===
"opportunity" => [
    "fieldName" => "opportunity",
    "targetEntity" => "MapasCulturais\Entities\Opportunity",
    "joinColumns" => [
        [
        "name" => "opportunity_id",
        "unique" => true,
        "nullable" => false,
        "onDelete" => "CASCADE",
        "columnDefinition" => null,
        "referencedColumnName" => "id",
        ],
    ],
    "mappedBy" => null,
    "inversedBy" => "evaluationMethodConfiguration",
    "cascade" => [
        "persist",
    ],
    "orphanRemoval" => false,
    "fetch" => 2,
    "type" => 1,
    "isOwningSide" => true,
    "sourceEntity" => "MapasCulturais\Entities\EvaluationMethodConfiguration",
    "isCascadeRemove" => false,
    "isCascadePersist" => true,
    "isCascadeRefresh" => false,
    "isCascadeMerge" => false,
    "isCascadeDetach" => false,
    "sourceToTargetKeyColumns" => [
        "opportunity_id" => "id",
    ],
    "joinColumnFieldNames" => [
        "opportunity_id" => "opportunity_id",
    ],
    "targetToSourceKeyColumns" => [
        "id" => "opportunity_id",
    ],
],
*/

                $skip = isset($cfg['skip']) && $cfg['skip'];
                $selected = isset($cfg['selected']) && $cfg['selected'];
                $mtype = $mapping['type'];
                $original_select = $select = implode(',', $cfg['select']);
                $target_class = $mapping['targetEntity'];

                $subquery_result_index = [];

                $_target_property = null;
                // if this relation was not selected in the main query
                if (!$skip) {
                    if(isset($mapping['users'])){
                        $_subquery_where_id_in = implode($mapping['users']);
                        $_target_property = $this->pk;
                    
                    // OneToOne
                    }else if ($mtype === 1) {
                        if($mapping['isOwningSide']) { // por exemplo o EvaluationMethodConfiguration->opportunity
                            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities, $mapping['fieldName']);
                            $_target_property = $mapping['joinColumns'][0]['referencedColumnName'];
                        
                        } else { // por exemplo o Opportunity->evaluationMethodConfiguration
                            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities);
                            $_target_property = $mapping['mappedBy'];
                        }

                    // ManyToOne
                    }else if ($mtype === 2) {
                        if ($selected) {
                            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities, $prop);
                        } else {
                            $_subquery_where_id_in = $this->getSubDQL($prop);
                        }
                        $_target_property = $mapping['joinColumns'][0]['referencedColumnName'];
                        
                    // OneToMany
                    } else if($mtype === 4) {
                        $_subquery_where_id_in = $this->getSubqueryInIdentities($entities, $this->pk);

                        $_target_property = $mapping['mappedBy'] ?: 'id';
                    }
                    
                    if(!$_subquery_where_id_in){
                        continue;
                    }
                    
                    if($select != '*'){
                        $select = "$_target_property,$select";
                    }

                    $qdata = ['@select' => $select];

                    if ($this->entityClassName == Entities\User::class && $prop == 'profile' || $mapping['isOwningSide']) {
                        $qdata['status'] = 'GTE(-10)';
                        $qdata['@permissions'] = 'view';
                    }

                    $query = new ApiQuery($target_class, $qdata, false, $cfg['selectAll'], !$this->_accessControlEnabled, $this);
                    
                    $query->name = "{$this->name}->$prop";
                    $query->where = (empty($query->where)) ? 
                        "e.{$_target_property} IN ({$_subquery_where_id_in})" : 
                        $query->where. " AND e.{$_target_property} IN ({$_subquery_where_id_in})";
                    
                    if(str_contains($_subquery_where_id_in, 'SELECT')){
                        foreach($this->getDqlParams() as $k => $v){
                            $query->_dqlParams[$k] = $v;
                        }
                    }

                    $cfg['query'] = $query;
                    $cfg['query_result'] = [];
                    $subquery_result = $query->getFindResult();
                    
                    if($mtype == 1 || $mtype === 2) {
                        foreach ($subquery_result as &$r) {
                            if($original_select === $this->pk){
                                $subquery_result_index[$r[$_target_property]] = $r[$this->pk];
                            } else {
                                $subquery_result_index[$r[$_target_property]] = &$r;
                                if(!in_array($_target_property, $query->_selecting)){
                                    unset($subquery_result[$_target_property]);
                                }
                            }
                        }
                    } else {
                        foreach ($subquery_result as &$r) {
                            if (is_array($r[$_target_property])) {
                                if (isset($r[$_target_property][0])) {
                                    $__tgt = $r[$_target_property][0];
                                } else {
                                    $__tgt = $r[$_target_property]['id'];
                                }
                            } else {
                                $__tgt = $r[$_target_property];
                            }

                            if(!isset($subquery_result_index[$__tgt])){
                                $subquery_result_index[$__tgt] = [];
                            }
                            if($original_select === $this->pk){
                                $subquery_result_index[$__tgt][] = $r[$this->pk];

                            } else {
                                $subquery_result_index[$__tgt][] = &$r;
                                if(!in_array($_target_property, $query->_selecting)){
                                    unset($r[$_target_property]);
                                }
                            }
                        }
                    }
                    
                }

                foreach ($entities as &$entity) {
                    if ($skip) {
                        continue;
                    } elseif ($selected) {
                        $val = $entity[$prop];
                        $entity[$prop] = isset($subquery_result_index[$val]) ? $subquery_result_index[$val] : null;
                    } else {
                        $prop = $prop[0] == '_' ? substr($prop,1) : $prop;
                        
                        $entity[$prop] = [];
                        foreach ($subquery_result_index as $k => $relation){
                            if($k == $entity[$this->pk] || $k == $entity){
                                $entity[$prop] = $relation;
                            }
                        }
                    }
                }
                

            }
        }
    }
    
    protected function appendFiles(array &$entities){
        if(!$this->_selectingFiles || !$this->usesFiles){
            return;
        }
        
        $app = App::i();

        $entity_class_name = $this->entityClassMetadata->parentClasses[0] ?? $this->entityClassName;
        
        $file_groups = $app->getRegisteredFileGroupsByEntity($entity_class_name);
        
        $where = [];
        foreach($this->_selectingFiles as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
            } elseif (strpos($select, '.') > 0){
                list($group, $transformation) = explode('.', $select);
                if ($transformation == '*') {
                    $where[] = "(fp.group = '{$group}') OR (f.group = '{$group}')";
                } else {
                    $where[] = "(f.group = 'img:{$transformation}' AND fp.group = '{$group}')";
                }
            } else {
                $where[] = "(f.group = '{$select}')";
            }
        }

        $files = [];

        $sub = $this->getSubqueryInIdentities($entities);

        if ($sub) {
            $where = implode(' OR ', $where);

            $dql = "
                SELECT
                    f.id,               
                    f.name,
                    f.mimeType,
                    f.description,
                    f._path,
                    f.group as file_group,
                    fp.id as parent_id,
                    f.private,
                    fp.group as parent_group,
                    IDENTITY(f.owner) AS owner_id,
                    f.private,
                    f.createTimestamp
                FROM
                    {$this->fileClassName} f
                        LEFT JOIN f.parent fp
                WHERE
                    f.owner IN ({$sub}) AND ({$where})
                ORDER BY f.id ASC";
                    
                    
            $query = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $query->enableResultCache($this->__cacheTLS);
            }

            if($this->_usingSubquery){
                $query->setParameters($this->getDqlParams());
            }

            $this->logDql($dql, __FUNCTION__, $this->_usingSubquery ? $this->getDqlParams() : []);
            
            $restul = $query->getResult(Query::HYDRATE_ARRAY);
                
            foreach($restul as $f){
                $owner_id = $f['owner_id'];
                if(!isset($files[$owner_id])){
                    $files[$owner_id] = [];
                }

                if ($f['private'] === TRUE) {
                $f['url'] = $app->storage->getPrivateUrlById($f['id']);
                } else {
                    $f['url'] = $app->storage->getUrlFromRelativePath($f['_path']);
                }

                if($f['parent_group']) {
                    $f['transformed'] = true;
                    $f['mainGroup'] = $f['parent_group'];
                    $f['group'] = $f['parent_group'] . '.' . str_replace('img:', '', $f['file_group']);
                } else {
                    $f['transformed'] = false;
                    $f['mainGroup'] = $f['file_group'];
                    $f['group'] = $f['file_group'];
                }
                            
                $files[$owner_id][] = $f;
            }
        }

        foreach($entities as &$entity){
            $id = $entity[$this->pk];
            $entity['files'] = [];
            if(isset($files[$id])){
                // método para compatibilidade da v1 da api
                foreach($files[$id] as $f){
                    if(!isset($file_groups[$f['mainGroup']])){
                        continue;
                    }
                    
                    if($this->_usingLegacyImageSelectFormat) { 
                        $file = [];
                        if(in_array('id', $this->_selectingFilesProperties)){
                            $file['id'] = $f['id'];
                        }
                        
                        if(in_array('name', $this->_selectingFilesProperties)){
                            $file['name'] = $f['name'];
                        }
                        
                        if(in_array('url', $this->_selectingFilesProperties)){
                            $file['url'] = $f['url'];
                        }
                        
                        if(in_array('description', $this->_selectingFilesProperties)){
                            $file['description'] = $f['description'];
                        }
                        
                        $key = '@files:' . $f['group'];
                        if($file_groups[$f['mainGroup']]->unique){

                            $entity[$key] = $file;
                        } else {
                            if(!isset($entity[$key])){
                                $entity[$key] = [];
                            }
                            $entity[$key][] = $file;
                        }
                    }                     
                }

                $raw_files = [];
                $transformed_files = [];

                foreach($files[$id] as $f){
                    $file = (object) [
                        'id' => $f['id'],
                        'name' => $f['name'],
                        'mimeType' => $f['mimeType'],
                        'url' => $f['url'],
                        'createTimestamp' => $f['createTimestamp']
                    ];

                    list($group, $transformation) = explode('.', "{$f['group']}.");
                    
                    if ($transformation) {
                        $parent = $f['parent_id'];

                        $transformed_files[$parent] = $transformed_files[$parent] ?? [];
                        $transformed_files[$parent][$transformation] = $file;
                    } else {
                        $file->description = $f['description'];
                        
                        /**
                         * Se o filegroup não existe, considerando que é um anexo de inscrição
                         */
                        if ($file_groups[$group]->unique ?? true) {
                            $raw_files[$group] = $file;
                        } else {
                            $raw_files[$group] = $raw_files[$group] ?? [];
                            $raw_files[$group][] = $file;
                        }
                    }
                }

                foreach($raw_files as $group => &$fs){
                    /**
                     * Se o filegroup não existe, considerando que é um anexo de inscrição
                     */
                    if ($file_groups[$group]->unique ?? true) {
                        $fs->transformations = $transformed_files[$fs->id] ?? null;
                    } else {
                        foreach($fs as &$file) {
                            $file->transformations = $transformed_files[$file->id] ?? null;
                        }
                    }
                }

                $entity['files'] = $raw_files;
            }
        }
        
    }
    protected function appendMetalists(array &$entities){
        if(!$this->_selectingMetalists || !$this->usesMetalists){
            return;
        }
        
        $where = [];
        $all = false;
        foreach($this->_selectingMetalists as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
                $all = true;
            } else {
                $where[] = "'{$select}'";
            }
        }

        $metalists = [];

        $sub = $this->getSubqueryInIdentities($entities);
        
        if($sub) {
            if ($all) {
                $where = '1 = 1';
            } else {
                $where = "ml.group IN (" . implode(',', $where) . ')';
            }

            $dql = "
                SELECT
                    ml.id,               
                    ml.objectId AS ownerId,
                    ml.group,
                    ml.title,
                    ml.description,
                    ml.value
                FROM
                    MapasCulturais\Entities\MetaList ml
                WHERE
                    ml.objectType = '{$this->rootEntityClassName}' AND 
                    ml.objectId IN ({$sub}) AND ({$where})
                ORDER BY ml.id ASC";
                    
            
            $this->logDql($dql, __FUNCTION__);
            
            $query = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $query->enableResultCache($this->__cacheTLS);
            }

            if($this->_usingSubquery){
                $query->setParameters($this->getDqlParams());
            }
            
            $restult = $query->getResult(Query::HYDRATE_ARRAY);
                    
            foreach($restult as $ml){
                $ownerId = $ml['ownerId'];
                $group = $ml['group'];
                
                $metalists[$ownerId] = $metalists[$ownerId] ?? [];
                $metalists[$ownerId][$group] = $metalists[$ownerId][$group] ?? [];
                
                unset($ml['ownerId'], $ml['group']);
                            
                $metalists[$ownerId][$group][] = $ml;
            }
        }
        
        foreach($entities as &$entity){
            $id = $entity[$this->pk];
            $entity['metalists'] = $entity['metalists'] ?? [];
            if(isset($metalists[$id])){
                $entity['metalists'] = $metalists[$id];
            }
        }
    }

    protected function appendAgentRelations(array &$entities) {
        if (!$this->_selectingAgentRelations || !$this->usesAgentRelations) {
            return;
        }

        $relation_class_name = $this->agentRelationClassName;
        
        $where = [];
        $all = false;
        foreach($this->_selectingAgentRelations as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
                $all = true;
            } else {
                $where[] = "'{$select}'";
            }
        }

        $sub = $this->getSubqueryInIdentities($entities);
        
        if(!$sub) {
            return;
        }

        if ($all) {
            $where = '1 = 1';
        } else {
            $where = "ar.group IN (" . implode(',', $where) . ')';
        }

        $dql = "
            SELECT
                ar.objectId as ownerId,
                ar.id,
                ar.group,
                ar.status,
                ar.hasControl,
                ar.createTimestamp,
                ar.metadata,
                a.id as agentId

            FROM
                $relation_class_name ar
                JOIN ar.agent a
            WHERE
                ar.objectId IN ({$sub}) AND 
                ({$where})
            ORDER BY ar.group ASC, ar.createTimestamp ASC";
                        
        
        $this->logDql($dql, __FUNCTION__);
    
        $query = $this->em->createQuery($dql);
        if($this->__useDQLCache){
            $query->enableResultCache($this->__cacheTLS);
        }

        if($this->_usingSubquery){
            $query->setParameters($this->getDqlParams());
        }
        
        $relations = $query->getResult(Query::HYDRATE_ARRAY);

        if ($relations) {
            $relations_by_owner_id = [];
            $agent_ids = implode(',', array_unique(array_map(function($item) {
                return $item['agentId'];
            }, $relations)));

            $agents_query = new ApiQuery(Agent::class, ['@select' => 'id,type,name,shortDescription,files.avatar,terms,singleUrl', 'id' => "IN($agent_ids)"]);
            $agents = $agents_query->find();
            $agents_by_id = [];
            foreach($agents as $agent) {
                $agents_by_id[$agent['id']] = $agent;
            }

            foreach($relations as $relation) {
                $group = $relation['group'];
                $owner_id = $relation['ownerId'];
                $agent_id = $relation['agentId'];

                unset($relation['agentId'], $relation['ownerId']);

                // o agente pode estar na lixeira e por isso não ter sido retornado na query
                if (!isset($agents_by_id[$agent_id])) {
                    continue;
                }

                $relations_by_owner_id[$owner_id][$group] = $relations_by_owner_id[$owner_id][$group] ?? [];
                $relation['agent'] = $agents_by_id[$agent_id];

                $relations_by_owner_id[$owner_id][$group][] = $relation;
            }

            foreach($entities as &$entity) {
                $entity_id = $entity[$this->pk];

                $entity['agentRelations'] = $relations_by_owner_id[$entity_id] ?? (object)[];
                $permisions = $entity['currentUserPermissions'];

                $can_view_pending = ($permisions['@controll'] ?? false) || 
                                    ($permisions['viewPrivateData'] ?? false) ||
                                    ($permisions['createAgentRelation'] ?? false) ||
                                    ($permisions['removeAgentRelation'] ?? false);

                if (!$can_view_pending) {
                    foreach ($entity['agentRelations'] as $group => &$relations) {
                        $relations = array_filter($relations, function($item) {
                            if($item['status'] > 0) {
                                return $item;
                            }
                        });

                        if (empty($relations)) {
                            unset($entity['agentRelations'][$group]);
                        }
                    }
                }
            }

        }
    }

    protected function appendRelatedAgents(array &$entities) {
        if (!$this->_selectingRelatedAgents || !$this->usesAgentRelations) {
            return;
        }

        $relation_class_name = $this->agentRelationClassName;
        
        $where = [];
        $all = false;
        foreach($this->_selectingRelatedAgents as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
                $all = true;
            } else {
                $where[] = "'{$select}'";
            }
        }

        $sub = $this->getSubqueryInIdentities($entities);

        if(!$sub) {
            return;
        }
        
        if ($all) {
            $where = '1 = 1';
        } else {
            $where = "ar.group IN (" . implode(',', $where) . ')';
        }

        $dql = "
            SELECT
                ar.objectId AS ownerId,
                ar.group,
                ar.status AS relationStatus,
                a.id as agentId

            FROM
                $relation_class_name ar
                JOIN ar.agent a
            WHERE
                
                ar.objectId IN ({$sub}) AND 
                ({$where})
            ORDER BY ar.group ASC, ar.createTimestamp ASC";
                        
        
        $this->logDql($dql, __FUNCTION__);
    
        $query = $this->em->createQuery($dql);
        if($this->__useDQLCache){
            $query->enableResultCache($this->__cacheTLS);
        }

        if($this->_usingSubquery){
            $query->setParameters($this->getDqlParams());
        }
        
        $relations = $query->getResult(Query::HYDRATE_ARRAY);

        if ($relations) {
            $relations_by_owner_id = [];
            $agent_ids = implode(',', array_unique(array_map(function($item) {
                return $item['agentId'];
            }, $relations)));

            $agents_query = new ApiQuery(Agent::class, ['@select' => 'id,type,name,shortDescription,files.avatar,terms,singleUrl', 'id' => "IN($agent_ids)"]);
            $agents = $agents_query->find();
            $agents_by_id = [];
            foreach($agents as $agent) {
                $agents_by_id[$agent['id']] = $agent;
            }

            foreach($relations as $relation) {
                $group = $relation['group'];
                $owner_id = $relation['ownerId'];
                $agent_id = $relation['agentId'];

                // o agente pode estar na lixeira e por isso não ter sido retornado na query
                if (!isset($agents_by_id[$agent_id])) {
                    continue;
                }

                $relations_by_owner_id[$owner_id][$group] = $relations_by_owner_id[$owner_id][$group] ?? [];
                $agent = $agents_by_id[$agent_id];
                $agent['relationStatus'] = $relation['relationStatus'];

                $relations_by_owner_id[$owner_id][$group][] = $agent;
            }

            foreach($entities as &$entity) {
                $entity_id = $entity[$this->pk];

                $entity['relatedAgents'] = $relations_by_owner_id[$entity_id] ?? (object)[]; 
                
                $permisions = $entity['currentUserPermissions'];

                $can_view_pending = ($permisions['@controll'] ?? false) || 
                                    ($permisions['viewPrivateData'] ?? false) ||
                                    ($permisions['createAgentRelation'] ?? false) ||
                                    ($permisions['removeAgentRelation'] ?? false);

                if (!$can_view_pending) {
                    foreach ($entity['relatedAgents'] as $group => &$relations) {
                        $relations = array_filter($relations, function($item) {
                            if($item['relationStatus'] > 0) {
                                return $item;
                            }
                        });

                        if (empty($relations)) {
                            unset($entity['relatedAgents'][$group]);
                        }
                    }
                }
            }

        }
    }

    protected function appendSpaceRelations(array &$entities) {
        if (!$this->_selectingSpaceRelations || !$this->usesSpaceRelations) {
            return;
        }

        $relation_class_name = $this->spaceRelationClassName;
        
        $where = [];
        $all = false;
        foreach($this->_selectingSpaceRelations as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
                $all = true;
            } else {
                $where[] = "'{$select}'";
            }
        }

        $sub = $this->getSubqueryInIdentities($entities);
        
        if(!$sub) {
            return;
        }
        
        
        $where = implode(',', $where);

        if($where) {
            $where = " AND ($where)";
        }

        $dql = "
            SELECT
                sr.objectId as ownerId,
                sr.id,
                sr.status,
                sr.createTimestamp,
                s.id as spaceId
                
            FROM
                $relation_class_name sr
                JOIN sr.space s
            WHERE
                sr.objectId IN ({$sub}) {$where}
            ORDER BY 
                sr.createTimestamp ASC";
                        
        
        $this->logDql($dql, __FUNCTION__);
    
        $query = $this->em->createQuery($dql);
        if($this->__useDQLCache){
            $query->enableResultCache($this->__cacheTLS);
        }

        if($this->_usingSubquery){
            $query->setParameters($this->getDqlParams());
        }
        
        $relations = $query->getResult(Query::HYDRATE_ARRAY);
        if ($relations) {
            $relations_by_owner_id = [];
            $space_ids = implode(',', array_unique(array_map(function($item) {
                return $item['spaceId'];
            }, $relations)));

            $spaces_query = new ApiQuery(Space::class, [
                    '@select' => 'id,type,name,shortDescription,files.avatar,terms,singleUrl', 
                    'id' => "IN($space_ids)"
                ]);
            $spaces = $spaces_query->find();
            $spaces_by_id = [];
            foreach($spaces as $space) {
                $spaces_by_id[$space['id']] = $space;
            }

            foreach($relations as $relation) {
                $owner_id = $relation['ownerId'];
                $space_id = $relation['spaceId'];

                unset($relation['spaceId'], $relation['ownerId']);

                // o espaço pode estar na lixeira e por isso não ter sido retornado na query
                if (!isset($spaces_by_id[$space_id])) {
                    continue;
                }

                $relations_by_owner_id[$owner_id] = $relations_by_owner_id[$owner_id] ?? [];
                $relation['space'] = $spaces_by_id[$space_id];

                $relations_by_owner_id[$owner_id][] = $relation;
            }

            foreach($entities as &$entity) {
                $entity_id = $entity[$this->pk];

                $entity['spaceRelations'] = $relations_by_owner_id[$entity_id] ?? (object)[]; 
            }

        }
    }

    protected function appendRelatedSpaces(array &$entities) {
        
        if (!$this->_selectingSpaceRelations || !$this->usesSpaceRelations) {
            return;
        }

        $relation_class_name = $this->spaceRelationClassName;
        
        $where = [];
        $all = false;
        foreach($this->_selectingRelatedSpaces as $select){
            if ($select == '*') {
                $where[] = '1 = 1';
                $all = true;
            } else {
                $where[] = "'{$select}'";
            }
        }

        $sub = $this->getSubqueryInIdentities($entities);

        if(!$sub) {
            return;
        }

        $where = implode(',', $where);

        if($where) {
            $where = " AND ($where)";
        }

        $dql = "
            SELECT
                sr.objectId as ownerId,
                s.id as spaceId

            FROM
                $relation_class_name sr
                JOIN sr.space s
            WHERE
                
                sr.objectId IN ({$sub}) {$where}
            ORDER BY 
                sr.createTimestamp ASC";
                        
        
        $this->logDql($dql, __FUNCTION__);
    
        $query = $this->em->createQuery($dql);
        if($this->__useDQLCache){
            $query->enableResultCache($this->__cacheTLS);
        }

        if($this->_usingSubquery){
            $query->setParameters($this->getDqlParams());
        }
        
        $relations = $query->getResult(Query::HYDRATE_ARRAY);

        if ($relations) {
            $relations_by_owner_id = [];
            $space_ids = implode(',', array_unique(array_map(function($item) {
                return $item['spaceId'];
            }, $relations)));

            $spaces_query = new ApiQuery(Space::class, ['@select' => 'id,type,name,shortDescription,files.avatar,terms,singleUrl', 'id' => "IN($space_ids)"]);
            $spaces = $spaces_query->find();
            $spaces_by_id = [];
            foreach($spaces as $space) {
                $spaces_by_id[$space['id']] = $space;
            }

            foreach($relations as $relation) {
                $owner_id = $relation['ownerId'];
                $space_id = $relation['spaceId'];

                // o espaço pode estar na lixeira e por isso não ter sido retornado na query
                if (!isset($spaces_by_id[$space_id])) {
                    continue;
                }

                $relations_by_owner_id[$owner_id] = $relations_by_owner_id[$owner_id] ?? [];

                $relations_by_owner_id[$owner_id][] =  $spaces_by_id[$space_id];
            }

            foreach($entities as &$entity) {
                $entity_id = $entity[$this->pk];

                $entity['relatedSpaces'] = $relations_by_owner_id[$entity_id] ?? (object)[]; 
            }

        }
    }

    protected function appendCurrentUserPermissions(array &$entities) {
        if (!$this->_selectingCurrentUserPermissions || !$this->usesPermissionCache) {
            return;
        }

        $app = App::i();
        $entity_class_name = $this->entityClassName;
        $permission_cache_class_name = $this->permissionCacheClassName;

        $user = $app->user;

        if ($user->is('guest')) {
            $user_id = -1;
        } else {
            $user_id = $user->id;
        }

        $permission_list = $entity_class_name::getPermissionsList();
        
        $dql_in = $this->getSubqueryInIdentities($entities);
        if($dql_in) {
            $where = [];
            $all = false;
            foreach($this->_selectingCurrentUserPermissions as $select){
                if ($select == '*') {
                    $all = true;
                    break;
                } else {
                    $where[] = "'{$select}'";
                }
            }

            $where_action = '';
            if (!$all) {
                $where_action = "pc.action IN (" . implode(',', $where) . ') AND';
                $permission_list = $this->_selectingCurrentUserPermissions;
            }

            $dql = "
            SELECT
                pc.action,
                IDENTITY(pc.owner) AS owner_id
            FROM 
                {$permission_cache_class_name} as pc
            WHERE 
                $where_action
                pc.owner IN ($dql_in) AND
                pc.userId = {$user_id}"; 

            $query = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $query->enableResultCache($this->__cacheTLS);
            }

            if($this->_usingSubquery){
                $query->setParameters($this->getDqlParams());
            }

            $this->logDql($dql, __FUNCTION__, $this->_usingSubquery ? $this->getDqlParams() : []);

            $result = $query->getResult(Query::HYDRATE_ARRAY);
            $permissions_by_entity = [];

            foreach ($result as $item) {
                $owner_id = $item['owner_id'];
                $action = (string)$item['action'];
                $permissions_by_entity[$owner_id] = $permissions_by_entity[$owner_id] ?? [];
                $permissions_by_entity[$owner_id][$action] = true;
            }
        }

        foreach ($entities as &$entity) {
            $entity_id = $entity[$this->pk];
            $entity['currentUserPermissions'] = [];

            foreach ($permission_list as $permission) {
                if(($this->usesOriginSubsite && $user->is('admin', $entity['_subsiteId'])) || (!$this->usesOriginSubsite && $user->is('saasAdmin'))){
                    $has_permission = true;
                } else {
                    if($permission == 'view' && !$entity_class_name::isPrivateEntity()){
                        // @todo verificar se status é maior que zero
                        $has_permission = true;
                    } else {
                        $has_permission = $permissions_by_entity[$entity_id][$permission] ?? false;
                    }
                }
                $entity['currentUserPermissions'][$permission] = $has_permission;
            }

            if ($this->_usingLegacyPermissionFormat) {
                $entity['permissionTo'] = $entity['currentUserPermissions'];
            }
        }
    }

    protected function appendTerms(array &$entities){
        $class = $this->rootEntityClassName;
        $term_relation_class_name = $this->termRelationClassName;
        if($term_relation_class_name && in_array('terms', $this->_selecting)){
            $app = App::i();
            
            $taxonomies = [];
            $skel = [];
            $terms_by_entity = [];

            foreach($app->getRegisteredTaxonomies($class) as $slug => $def){
                $taxonomies[$def->slug] = $slug;
                $skel[$slug] = [];
            }
            // --------------------

            $dql_in = $this->getSubqueryInIdentities($entities);
            
            if ($dql_in) {
                $dql = "
                    SELECT
                        t.term,
                        t.taxonomy,
                        IDENTITY(tr.owner) AS owner_id
                    FROM {$term_relation_class_name} tr
                        JOIN tr.term t
                    WHERE
                        tr.owner IN ($dql_in)";
                    
                $query = $this->em->createQuery($dql);
                if($this->__useDQLCache){
                    $query->enableResultCache($this->__cacheTLS);
                }
                
                if($this->_usingSubquery){
                    $query->setParameters($this->getDqlParams());
                }

                $this->logDql($dql, __FUNCTION__, $this->_usingSubquery ? $this->getDqlParams() : []);
                
                $result = $query->getResult(Query::HYDRATE_ARRAY);
                            
                foreach($result as $term){
                    if(!isset($taxonomies[$term['taxonomy']])){
                        continue;
                    }
                    
                    $owner_id = $term['owner_id'];
                    $taxonomy = $term['taxonomy'];
                    $term = $term['term'];
                    
                    if(!isset($terms_by_entity[$owner_id])){
                        $terms_by_entity[$owner_id] = $skel;
                    }
                    if(!in_array($term, $terms_by_entity[$owner_id][$taxonomy])){
                        $terms_by_entity[$owner_id][$taxonomy][] = $term;
                    }
                }
            }
            
            foreach($entities as &$entity){
                $id = $entity[$this->pk];
                
                $entity['terms'] = isset($terms_by_entity[$id]) ? $terms_by_entity[$id] : $skel;
            }
        }
    }

    protected $_relatedSeals = null;

    protected function _fetchRelatedSeals(array &$entities){
        if(is_null($this->_relatedSeals)){
            $app = App::i();
            $this->_relatedSeals = [];

            $dql_in = $this->getSubqueryInIdentities($entities);
            if(!$dql_in) {
                return;
            }

            $dql = "
                SELECT
                    IDENTITY(sr.owner) as entity_id,
                    sr.id as relation_id,
                    sr.createTimestamp as relation_create_timestamp,
                    s.id as seal_id,
                    s.name as seal_name
                FROM
                    {$this->sealRelationClassName} sr
                    JOIN sr.seal s
                WHERE
                    sr.owner IN ($dql_in) AND
                    sr.status >= 0 AND
                    s.status >= 0";

            $query = $this->em->createQuery($dql);
            if($this->__useDQLCache){
                $query->enableResultCache($this->__cacheTLS);
            }
            
            if($this->_usingSubquery){
                $query->setParameters($this->getDqlParams());
            }
            $this->logDql($dql, __FUNCTION__, $this->_usingSubquery ? $this->getDqlParams() : []);

            $relations = $query->getResult(Query::HYDRATE_ARRAY);

            $seal_ids = array_map(function($item) {
                return $item['seal_id'];
            }, $relations);


            $seals_api_query = new ApiQuery(Seal::class, ['@select' => 'files', 'id' => API::IN($seal_ids)]);
            $files = [];
            foreach($seals_api_query->find() as $seal) {
                $files[$seal['id']] = $seal['files'] ?? null; 
            }
            foreach($relations as $relation){
                $relation = (object) $relation;
                
                $entity_id = $relation->entity_id;

                if(!isset($this->_relatedSeals[$entity_id])){
                    $this->_relatedSeals[$entity_id] = [];
                }

                $this->_relatedSeals[$entity_id][] = [
                    'sealRelationId' => $relation->relation_id,
                    'sealId' => $relation->seal_id,
                    'name' => $relation->seal_name,
                    'files' => $files[$relation->seal_id] ?? null,
                    'singleUrl' => $app->createUrl('seal', 'sealRelation', [$relation->relation_id]),
                    'createTimestamp' => $relation->relation_create_timestamp,
                    'isVerificationSeal' => in_array($relation->seal_id, $app->config['app.verifiedSealsIds']),
                ];
            }
        }
    }

    protected function appendIsVerified(array &$entities){
        if($this->usesSealRelation && $this->_selectingIsVerfied){
            $this->_fetchRelatedSeals($entities);

            foreach($entities as &$entity){
                $entity['isVerified'] = false;
                if(isset($this->_relatedSeals[$entity[$this->pk]])){
                    foreach($this->_relatedSeals as $relations){
                        foreach($relations as $relation){
                            if($relation['isVerificationSeal']){
                                $entity['isVerified'] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    protected function appendVerifiedSeals(array &$entities){
        if($this->usesSealRelation && $this->_selectingVerfiedSeals){
            $this->_fetchRelatedSeals($entities);

            foreach($entities as &$entity){
                if(isset($this->_relatedSeals[$entity[$this->pk]])){
                    $entity['verifiedSeals'] = array_filter($this->_relatedSeals[$entity[$this->pk]], function($s){
                        if($s['isVerificationSeal']){
                            return $s;
                        }
                    });
                } else {
                    $entity['verifiedSeals'] = [];
                }
            }
        }
    }

    protected function appendSeals(array &$entities){
        if($this->usesSealRelation && $this->_selectingSeals){
            $this->_fetchRelatedSeals($entities);

            foreach($entities as &$entity){
                if(isset($this->_relatedSeals[$entity[$this->pk]])){
                    $entity['seals'] = $this->_relatedSeals[$entity[$this->pk]];
                } else {
                    $entity['seals'] = [];
                }
            }
        }
    }
    
    private $__viewPrivateDataPermissions = null;
    
    protected function getViewPrivateDataPermissions(array $entities){
        if(is_null($this->__viewPrivateDataPermissions) && count($entities) > 0){
            $this->__viewPrivateDataPermissions = [];
            
            $app = App::i();
            
            $is_admin = $app->user->is('admin') ;
            if($is_admin || $app->user->is('guest') || !$this->permissionCacheClassName){
                foreach($entities as $entity){
                    $this->__viewPrivateDataPermissions[$entity[$this->pk]] = $is_admin;
                }
            } else {
                $dql_in = $this->getSubqueryInIdentities($entities);
                
                if ($dql_in) {
                    $dql = "SELECT IDENTITY(pc.owner) as entity_id FROM {$this->permissionCacheClassName} pc WHERE pc.owner IN ($dql_in) AND pc.user = {$app->user->id}";
    
                    $query = $this->em->createQuery($dql);
                    if($this->__useDQLCache){
                        $query->enableResultCache($this->__cacheTLS);
                    }
    
                    if($this->_usingSubquery){
                        $query->setParameters($this->getDqlParams());
                    }
                    $this->logDql($dql, __FUNCTION__, $this->_usingSubquery ? $this->getDqlParams() : []);
    
                    $qr = $query->getResult(Query::HYDRATE_ARRAY);
                } else {
                    $qr = [];
                }

                
                foreach($entities as $entity){
                    $this->__viewPrivateDataPermissions[$entity[$this->pk]] = false;
                }
                
                foreach($qr as $r){
                    $this->__viewPrivateDataPermissions[$r['entity_id']] = true;
                }
            }
        }
        
        return $this->__viewPrivateDataPermissions;
    }

    protected function addMultipleParams(array $values) {
        $result = [];
        foreach ($values as $value) {
            $result[] = $this->addSingleParam($value);
        }

        return $result;
    }

    protected function addSingleParam($value) {
        $app = App::i();
        
        if(!is_array($value)){
            if (preg_match('#POINT\((\-?[0-9\.]+):(\-?[0-9\.]+)\)#', $value, $matches)) {
                $value = new GeoPoint($matches[1],$matches[2]);
                // $value = "({$matches[1]},{$matches[2]})";
            } else if (trim($value) === '@me') {
                $value = $app->user->is('guest') ? null : $app->user;
            } elseif (strpos($value, '@me.') === 0) {
                $v = str_replace('@me.', '', $value);
                $value = $app->user->$v;
            } elseif (trim($value) === '@profile') {
                $value = $app->user->profile ? $app->user->profile : null;
            } elseif (preg_match('#@(\w+)[ ]*:[ ]*(\d+)#i', trim($value), $matches)) {
                $_repo = $app->repo($matches[1]);
                $_id = $matches[2];

                $value = ($_repo && $_id) ? $_repo->find($_id) : null;
            } elseif (trim($value) != '@control' && strlen($value) && $value[0] == '@') {
                $value = null;
            }
        }
        
        $uid = uniqid('v');
        $this->_dqlParams[$uid] = $value;

        $result = ':' . $uid;

        return $result;
    }

    protected function parseParam($key, $expression) {
        
        if (is_string($expression) && !preg_match('#^[ ]*(!)?([a-z]+)[ ]*\((.*)\)$#i', $expression, $match)) {
            throw new Exceptions\Api\InvalidExpression($expression);
        } else {
            $dql = '';

            $not = $match[1];
            $operator = strtoupper($match[2]);
            $value = $match[3];
            
            if (preg_match('#[a-z0-9]+.location#i', $key)) {
                $value = preg_replace('#\[(\-?[0-9\.]+)\,(\-?[0-9\.]+)\]#', 'POINT($1:$2)', $value);
            }

            if ($operator == 'OR' || $operator == 'AND') {
                $expressions = $this->parseExpression($value);

                foreach ($expressions as $expression) {
                    $sub_dql = $this->parseParam($key, $expression);
                    $dql .= $dql ? " $operator $sub_dql" : "($sub_dql";
                }
                if ($dql) {
                    $dql .= ')';
                }
            } elseif ($operator == "IN") {
                $values = $this->splitParam($value);

                $values = $this->addMultipleParams($values);
                if (count($values) > 0) {
                    $dql = $not ? "$key NOT IN (" : "$key IN (";
                    $dql .= implode(', ', $values) . ')';
                } else if(!$not) {
                    $dql .= "$key IS NULL AND $key IS NOT NULL";
                }

                
            }elseif($operator == "IIN"){
                $values = $this->splitParam($value);

                $values = $this->addMultipleParams($values);

                $values = array_map(function($e) use ($key, $not) {
                    if($not){
                        return "unaccent(lower($key)) != unaccent(lower($e))";
                    }else{
                        return "unaccent(lower($key)) = unaccent(lower($e))";
                    }
                } , $values);

                if (is_array($values) && count($values) < 1){
                    throw new Exceptions\Api\InvalidArgument('expression IIN expects at last one value');
                }

                $dql = "\n(\n\t" . ($not ? implode("\n\t AND ", $values) : implode("\n\t OR ", $values) ) . "\n)";

                
            } elseif ($operator == "BET") {
                $values = $this->splitParam($value);

                if (count($values) !== 2) {
                    throw new Exceptions\Api\InvalidArgument('expression BET expects 2 arguments');
                } elseif ($values[0][0] === '@' || $values[1][0] === '@') {
                    throw new Exceptions\Api\InvalidArgument('expression BET expects 2 string or integer arguments');
                }

                $values = $this->addMultipleParams($values);

                $dql = $not ?
                        "$key NOT BETWEEN {$values[0]} AND {$values[1]}" :
                        "$key BETWEEN {$values[0]} AND {$values[1]}";
            } elseif ($operator == "LIKE") {
                $value = str_replace('*', '%', $value);
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "unaccent($key) NOT LIKE unaccent($value)" :
                        "unaccent($key) LIKE unaccent($value)";
            } elseif ($operator == "ILIKE") {
                $value = str_replace('*', '%', $value);
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "unaccent(lower($key)) NOT LIKE unaccent(lower($value))" :
                        "unaccent(lower($key)) LIKE unaccent(lower($value))";
            } elseif ($operator == "EQ") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key <> $value" :
                        "$key = $value";
            } elseif ($operator == "GT") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key <= $value" :
                        "$key > $value";
            } elseif ($operator == "GTE") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key < $value" :
                        "$key >= $value";
            } elseif ($operator == "LT") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key >= $value" :
                        "$key < $value";
            } elseif ($operator == "LTE") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key > $value" :
                        "$key <= $value";
            } elseif ($operator == 'NULL') {
                $dql = $not ?
                        "($key IS NOT NULL)" :
                        "($key IS NULL)";
            } elseif ($operator == 'GEONEAR') {
                $values = $this->splitParam($value);

                if (count($values) !== 3) {
                    throw new Exceptions\Api\InvalidArgument('expression GEONEAR expects 3 arguments: longitude, latitude and radius in meters');
                }

                list($longitude, $latitude, $radius) = $this->addMultipleParams($values);


                $dql = $not ?
                        "ST_DWithin($key, ST_MakePoint($longitude,$latitude), $radius) <> TRUE" :
                        "ST_DWithin($key, ST_MakePoint($longitude,$latitude), $radius) = TRUE";
                        
            } elseif ($operator == 'GEOBOUNDING') {
                $values = $this->splitParam($value);
                $rexp = '#POINT\((\-?[0-9\.]+):(\-?[0-9\.]+)\)#';
                if (count($values) !== 2 || !(preg_match($rexp, $values[0], $matches1)) || !(preg_match($rexp, $values[1], $matches2))) {
                    throw new Exceptions\Api\InvalidArgument('expression GEOBOX expects 2 arguments: point A, point B. ex: GEOBOX([-43.33,-1.55],[-40.33,-2.5])');
                }

                $lng1 = $matches1[1];
                $lat1 = $matches1[2];
                $lng2 = $matches2[1];
                $lat2 = $matches2[2];

                $line = $this->addSingleParam("LINESTRING($lng1 $lat1, $lng2 $lat2)");

                $dql = $not ?
                        "st_covers(st_envelope(st_geomfromtext({$line})), $key) <> TRUE" :
                        "st_covers(st_envelope(st_geomfromtext({$line})), $key) = TRUE";
            }
            
            return $dql;
        }
    }

    private function splitParam($val) {
        
        $result = explode("\n", str_replace('\\,', ',', preg_replace('#(^[ ]*|([^\\\]))\,#', "$1\n", $val)));

        if (count($result) === 1 && in_array($result[0], [null,'']) ) {
            return [];
        } else {
            $_result = [];
            foreach ($result as $r)
                if (!is_null($r) && $r !== '') {
                    $_result[] = $r;
                }
            return $_result;
        }
    }

    protected function parseExpression($val) {

        $open = false;
        $nopen = 0;
        $counter = 0;

        $results = [];
        $last_char = '';

        foreach (str_split($val) as $index => $char) {
            $next_char = strlen($val) > $index + 1 ? $val[$index + 1] : '';

            if (!key_exists($counter, $results))
                $results[$counter] = '';

            if ($char !== '\\' || $next_char === '(' || $next_char === ')')
                if ($open || $char !== ',' || $last_char === '\\')
                    $results[$counter] .= $char;

            if ($char === '(' && $last_char !== '\\') {
                $open = true;
                $nopen++;
            }

            if ($char === ')' && $last_char !== '\\' && $open) {
                $nopen--;
                if ($nopen === 0) {
                    $open = false;
                    $counter++;
                }
            }

            $last_char = $char;
        }

        return $results;
    }

    protected function parseQueryParams() {
        $app = App::i();
        $class = $this->entityClassName;
        foreach ($this->apiParams as $key => $value) {
            $value = trim($value);
            
            if (strtolower($key) == '@type') {
                continue;
            } elseif (strtolower($key) == '@select') {
                $this->_parseSelect($value);
            } elseif (strtolower($key) == '@order') {
                if(in_array('createTimestamp', $this->entityProperties)) {
                    $this->_order = $value . ',createTimestamp ASC';
                } else {
                    $this->_order = $value . ',id ASC';
                }
            } elseif (strtolower($key) == '@offset') {
                $this->_offset = $value;
            } elseif (strtolower($key) == '@page') {
                $this->_page = $value;
            } elseif (strtolower($key) == '@limit') {
                $this->_limit = $value;
            } elseif (strtolower($key) == '@keyword') {
                $this->_keyword = $value;
            } elseif (strtolower($key) == '@permissionsuser') {
                $this->_permissionsUser = $value;
            } elseif (strtolower($key) == '@permissions') {
                $this->_addFilterByPermissions($value);
            } elseif (strtolower($key) == '@seals') {
                $this->_addFilterBySeals(explode(',', $value));
            } elseif (strtolower($key) == '@verified') {
                $this->_addFilterBySeals($app->config['app.verifiedSealsIds']);
            } elseif (strtolower($key) == '@or') {
                $this->_op = ' OR ';
            } elseif (strtolower($key) == '@files') {
                $this->_usingLegacyImageSelectFormat = true;
                $this->_parseFiles($value);
            } elseif ($key === 'user' && $this->usesOwnerAgent) {
                $this->_addFilterByOwnerUser($value);
            } elseif (key_exists($key, $this->entityRelations) && $this->entityRelations[$key]['isOwningSide']) {
                $this->_addFilterByEntityProperty($key, $value);
            } elseif (in_array($key, $this->entityProperties)) {
                $this->_addFilterByEntityProperty($key, $value);
            } elseif ($this->usesTypes && $key === 'type') {
                $this->_addFilterByEntityProperty($key, $value, '_type');
            } elseif ($key[0] !== '_' && strpos($key, '.') > 0) {
                $this->_addFilterByEntityRelation($key, $value);
            } elseif ($this->usesTaxonomies && isset($this->registeredTaxonomies[$key])) {
                $this->_addFilterByTermTaxonomy($key, $value);
            } elseif ($this->usesMetadata && in_array($key, $this->registeredMetadata)) {
                $this->_addFilterByMetadata($key, $value);
            } elseif ($key[0] !== '_' && $key != 'callback') {
                throw new Exceptions\Api\PropertyDoesNotExists("property $key does not exists");
            }
        }
        
        if($class::isPrivateEntity() && !isset($this->apiParams['@permissions'])){
            $this->_addFilterByPermissions('view');
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.parseQueryParams");
    }
    
    protected function _addFilterBySeals($seals_ids){
        if(is_string($seals_ids)) {
            $seals_ids = explode(',', $seals_ids);
        }
        foreach($seals_ids as $seal){
            $s = intval($seal);
            if($s && !in_array($s, $this->_seals)){
                $this->_seals[] = $s;
            }
        }
    }
    
    protected $_filteringByPermissions = false;
            
    protected $_permissionsUser = null;

    protected function _setPermissionsUser($value) {
        $this->_permissionsUser = $value;
    }

    protected function _addFilterByPermissions($value) {
        $app = App::i();
        $user = $this->_permissionsUser ?
            $app->repo('User')->find($this->_permissionsUser) :
            $app->user;
        $this->_permission = trim($value);
        $class = $this->entityClassName;
        if($this->_accessControlEnabled && $this->_permission && !$user->is('saasAdmin') && $this->usesPermissionCache){
            $alias = $this->getAlias('pcache');
            
            $this->_filteringByPermissions = true;
            
            $pkey = $this->addSingleParam($this->_permission);
            $_uid = $user->id;
            if(($this->_permission != 'view' || $class::isPrivateEntity()) && (!$this->usesOriginSubsite || !$this->adminInSubsites)) {
                $this->joins .= " JOIN e.__permissionsCache $alias WITH $alias.action = $pkey AND $alias.userId = $_uid ";
                
            } else {
                $this->select =  $this->select ? ", $alias.action " : $this->select;
                
                $admin_where = '';
                
                $and = $this->where ? 'AND' : '';
                
                // if the logged in user is an admin in some site
                if($this->adminInSubsites){
                    $admin_where = [];

                    foreach($this->adminInSubsites as $subsite_id){
                        if($subsite_id){
                            $admin_where[] = "e._subsiteId = {$subsite_id}";
                        } else {
                            $admin_where[] = "e._subsiteId IS NULL";
                        }
                    }

                    $admin_where = implode(' OR ', $admin_where);
                    $admin_where = "OR ($admin_where)";
                }
                $view_where = '';
                if($this->usesStatus && $this->_permission == 'view' && !$class::isPrivateEntity()) {
                    $params = $this->apiParams;
                    if($this->entityClassName === Opportunity::class && (isset($params['id']) || isset($params['parent']) || isset($params['status']))) {
                        $view_where = 'OR e.status > 0 OR e.status = -1';    
                    } else {
                        $view_where = 'OR e.status > 0';
                    }
                }
                
                $this->where .= " $and ( e.{$this->pk} IN (SELECT IDENTITY($alias.owner) FROM {$this->permissionCacheClassName} $alias WHERE $alias.owner = e AND $alias.action = $pkey AND $alias.userId = $_uid) $admin_where $view_where) ";
            }
        }
    }

    protected function _addFilterByOwnerUser($value) {
        $alias = uniqid('user_agent__');
        $this->_keys['user'] = "{$alias}.user";

        $this->joins .= "\n\tLEFT JOIN e.owner {$alias}\n";
        
        $this->_whereDqls[] = $this->parseParam($this->_keys['user'], $value);
    }

    protected function _addFilterByEntityProperty($key, $value, $propery_name = null) {
        $this->_keys[$key] = $propery_name ? "e.{$propery_name}" : "e.{$key}";

        $this->_whereDqls[] = $this->parseParam($this->_keys[$key], $value);
    }

    protected function _addFilterByEntityRelation($key, $value) {
        // @TODO: implementar
    }

    protected function _addFilterByMetadata($key, $value) {
        $meta_alias = $this->getAlias('meta_' . $key);

        $this->_keys[$key] = "$meta_alias.value";

        $this->joins .= str_replace(['{ALIAS}', '{KEY}'], [$meta_alias, $key], $this->_templateJoinMetadata);

        $this->_whereDqls[] = $this->parseParam($this->_keys[$key], $value);
    }

    protected function _addFilterByTermTaxonomy($key, $value) {
        $tr_alias = $this->getAlias('tr');
        $t_alias = $this->getAlias('t');
        $taxonomy_slug = $this->registeredTaxonomies[$key];

        $this->_keys[$key] = "$t_alias.term";

        $this->joins .= str_replace(['{ALIAS_TR}', '{ALIAS_T}', '{TAXO}'], [$tr_alias, $t_alias, $taxonomy_slug], $this->_templateJoinTerm);

        $this->_whereDqls[] = $this->parseParam($this->_keys[$key], $value);
    }
    
    public function addFilterByApiQuery(ApiQuery $subquery, $subquery_property = 'id', $property = null){
        if(is_null($property)) {
            $property = $this->pk;
        }
        $this->_subqueryFilters[] = [
            'subquery' => $subquery,
            'subquery_property' => $subquery_property,
            'property' => $property
        ];
    }
    
    protected function _getAllPropertiesNames(){
        $remove_properties = [
            '_geoLocation'
        ];
        
        $defaults = [];

        if ($this->usesTaxonomies) {
            $defaults[] = 'terms';
        }

        if ($this->usesFiles) {
            $defaults[] = 'files';
        }

        if ($this->usesMetalists) {
            $defaults[] = 'metalists';
        }

        if ($this->usesMetalists) {
            $defaults[] = 'seals';
        }

        if ($this->usesAgentRelations) {
            $defaults[] = 'relatedAgents';
        }

        if ($this->usesSpaceRelations) {
            $defaults[] = 'relatedSpaces';
        }

        if ($this->usesPermissionCache){
            $defaults[] = 'currentUserPermissions';
        }

        if ($this->entityClassName == Opportunity::class) {
            $defaults[] = 'ownerEntity.{name,shortDescription,files.avatar,terms}';
        }

        $properties = array_merge(
                    $this->entityProperties,
                    $this->registeredMetadata,
                    $defaults,
                    array_keys($this->entityRelations)
                );
        
        $properties = array_filter($properties, function($e) use($remove_properties){
            if(!in_array($e, $remove_properties) && substr($e, 0, 2) != '__'){
                return $e;
            }
        });
        
        $properties = array_map(function($e){
            return $e[0] == '_' ? substr($e, 1) : $e;
        }, $properties);
                
        return $properties;
    }
    
    protected function _parseSelect($select) {
        if($select == '*' && $this->entityClassName == Opportunity::class) {
            $select .= ',ownerEntity';
        }
        $select = preg_replace('#ownerEntity([^\.]?)#','ownerEntity.{id,name}$1',$select);
        $select = str_replace(' ', '', $select);
        
        if(strpos($select, '*') === 0){
            $_select = preg_replace('#^\*,?#', '', $select);    
            $select = implode(',', $this->_getAllPropertiesNames()) . ($_select ? ",$_select" : '');
        }

        $replacer = function ($select, $prop, $_subquery_select, $_subquery_match){
            $replacement = $this->_preCreateSelectSubquery($prop, $_subquery_select, $_subquery_match);
            
            if(is_null($replacement)){
                $select = str_replace(["$_subquery_match,", ",$_subquery_match", ".$_subquery_match"], '', $select);

            }else{
                $select = str_replace($_subquery_match, $replacement, $select);
            }

            return $select;
        };

        // create subquery to format entity.* or entity.{id,name}
        while (preg_match('#([^,\.\{]+)\.(\{[^\{\}]+\})#', $select, $matches)) {
            $_subquery_match = $matches[0];
            $prop = $matches[1];
            $_subquery_select = substr($matches[2], 1, -1);
            $select = $replacer($select, $prop, $_subquery_select, $_subquery_match);
        }

        // create subquery to format entity.id or entity.name
        while (preg_match('#([^,\.]+)\.([^,]+)#', $select, $matches)) {
            $_subquery_match = $matches[0];
            $prop = $matches[1];
            $_subquery_select = $matches[2];
            $select = $replacer($select, $prop, $_subquery_select, $_subquery_match);
        }

        
        $this->_selecting = [];
        $selecting = array_unique(explode(',', $select));
        
        sort($selecting);

        foreach($this->_getAllPropertiesNames() as $prop) {
            if(in_array($prop, $selecting)) {
                $this->_selecting[] = $prop;
            }
        }

        foreach($selecting as $prop) {
            if(!in_array($prop, $this->_selecting)) {
                $this->_selecting[] = $prop;
            }
        }

        if($this->_selectAll){
            foreach($this->_getAllPropertiesNames() as $k){
                if(!in_array($k, $this->_selecting)){
                    $sub = false;
                    if($this->_subqueriesSelect){
                        foreach($this->_subqueriesSelect as $sq){
                            if($sq['property'] == $k){
                                $sub = true;
                            }
                        }
                    }
                    if(!$sub){
                        $this->_selecting[] = $k;
                    }
                }
            }
        }

        foreach ($this->_selecting as $i => $prop) {
            if(!$prop){
                continue;
            }
            if (in_array($prop, $this->entityProperties)) {
                $this->_selectingProperties[] = $prop;
            } elseif (in_array($prop, $this->registeredMetadata)) {
                $this->_selectingMetadata[] = $prop;
            } elseif ($prop[0] != '_' && isset($this->entityRelations[$prop])) {
                $this->_selecting[$i] = $this->_preCreateSelectSubquery($prop, $this->pk, $prop);
            } elseif ($prop[0] != '_' && isset($this->entityRelations["_{$prop}"])) {
                $this->_selecting[$i] = $this->_preCreateSelectSubquery("_{$prop}", $this->pk, $prop);
            } elseif ($prop === 'originSiteUrl' && $this->usesOriginSubsite) {
                $this->_selectingOriginSiteUrl = true;
            } elseif (preg_match('#^([a-z][a-zA-Z]*)Url#', $prop, $url_match) && method_exists($this->entityClassName, "get{$prop}")) {
                $this->_selectingUrls[] = $url_match[1];
            } elseif($prop === 'type' && $this->usesTypes){
                $this->_selectingProperties[] = '_type';
                $this->_selectingType = true;
            } elseif($prop === 'isVerified') {
                $this->_selectingIsVerfied = true;
            } elseif($prop === 'verifiedSeals') {
                $this->_selectingVerfiedSeals = true;
            } elseif($prop === 'seals') {
                $this->_selectingSeals = true;
            } elseif ($prop === 'files') {
                $this->_selectingFiles = ['*'];
            } elseif ($prop === 'agentRelations') {
                $this->_selectingAgentRelations = ['*'];
            } elseif ($prop === 'relatedAgents') {
                $this->_selectingRelatedAgents = ['*'];
            } elseif ($prop === 'spaceRelations') {
                $this->_selectingSpaceRelations = ['*'];
            } elseif ($prop === 'relatedSpaces') {
                $this->_selectingSpaceRelations = ['*'];
            } elseif ($prop === 'metalists') {
                $this->_selectingMetalists = ['*'];
            } elseif ($prop === 'currentUserPermissions') {
                $this->_selectingCurrentUserPermissions = ['*'];
            } elseif ($prop === 'permissionTo') {
                $this->_selectingCurrentUserPermissions = ['*'];
                $this->_usingLegacyPermissionFormat = true;
            }
        }
    }
    
    protected function _preCreateSelectSubquery($prop, $_select, $_match) {
                
        $_select_properties = explode(',', $_select);

        $_select_all = false;

        if(in_array('*', $_select_properties)){
            $_select_all = true;
            if(($k = array_search('*', $_select_properties)) !== false) {
                unset($_select_properties[$k]);
            }
        }
        
        $select = array_map(function($property) use(&$_match) {
            // if the property is a subsquery
            if (isset($this->_subqueriesSelect[$property])) {
                $sq = $this->_subqueriesSelect[$property]['match'];
                unset($this->_subqueriesSelect[$property]);
                $_match = str_replace($property, $sq, $_match);
                return $sq;
            } else {
                return $property;
            }
        }, $_select_properties);

        $uid = uniqid('#sq:');
        
        foreach($this->_subqueriesSelect as $_uid => $_cfg) {
            if ($_cfg['property'] == $prop) {
                return $_uid;
            }
        }

        $this->_subqueriesSelect[$uid] = [
            'selectAll' => $_select_all,
            'property' => $prop,
            'select' => array_unique($select),
            'match' => $_match,
        ];
        
        $result = $uid;
        
        if($prop === 'user' && !isset($this->entityRelations['user']) && $this->usesOwnerAgent){
            $user_alias = $this->getAlias('user');
            $owner_alias = $this->getAlias('owner');
            $this->select .=  $this->select ? ", IDENTITY({$owner_alias}.user)   AS   user" : "IDENTITY({$owner_alias}.user) AS user";
            $this->joins .= " JOIN e.owner {$owner_alias}";
        }
        
        return $result;
    }

    protected function _parseFiles($value) {
        if (preg_match('#^\(([\w\., ]+)\)[ ]*(:[ ]*([\w, ]+))?#i', $value, $imatch)) {
            $this->_selectingFiles = explode(',', str_replace(' ', '', $imatch[1]));
            
            if(isset($imatch[3])){
                $this->_selectingFilesProperties = explode(',', str_replace(' ', '', $imatch[3]));
            }
        }
    }

}
