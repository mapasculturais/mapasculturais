<?php

namespace MapasCulturais;

use Doctrine\ORM\Query;
use \MapasCulturais\Entities\File;

class ApiQuery {
    use Traits\MagicGetter;
    
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
     * The entity uses files?
     * @var bool
     */
    protected $usesFiles;
    
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
    protected $_dqlParams = [];

    /**
     * Fields that are being selected
     * @var array
     */
    protected $_selecting = ['id'];

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
     * Files that are being selected
     * @var array
     */
    protected $_selectingFiles = [];
    
    /**
     * Properties of files that are being selected
     * @var type
     */
    protected $_selectingFilesProperties = ['url'];

    protected $_selectingIsVerfied = false;
    
    protected $_selectingVerfiedSeals = false;
    
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
     * @var type
     */
    protected $_seals = [];
    
    /**
     *
     * @var type
     */
    protected $_permissions = [];
    
    protected $_subqueryFilters = [];
    protected $_status = '> 0';
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
     *
     * @var ApiQuery
     */
    protected $parentQuery;
    
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
        
        $this->__queryNum = self::$queryCounter++;
        
        $this->em = $app->em;
        
        krsort($api_params);
        $this->apiParams = $api_params;
        
        if(strpos($class, 'MapasCulturais\Entities\Opportunity') === 0 && $this->parentQuery){
            $parent_class = $this->parentQuery->entityClassName;
            if($parent_class != 'MapasCulturais\Entities\Opportunity') {
                $class = $parent_class::getOpportunityClassName();
            }
        }
        
        $this->entityProperties = array_keys($this->em->getClassMetadata($class)->fieldMappings);
        $this->entityRelations = $this->em->getClassMetadata($class)->associationMappings;
        
        $this->entityClassName = $class;
        $this->entityController = $app->getControllerByEntity($this->entityClassName);
        $this->entityRepository = $app->repo($this->entityClassName);
        
        $this->usesFiles = $class::usesFiles();
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

    public function findIds() {
        $result = $this->getFindResult('e.id');

        return array_map(function ($row) {
            return $row['id'];
        }, $result);
    }

    public function findOne(){
        return $this->getFindOneResult();
    }
    
    public function getFindOneResult() {
        $dql = $this->getFindDQL();

        $q = $this->em->createQuery($dql);

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

        return $result;
    }

    public function find(){
        return $this->getFindResult();
    }
    
    public function getFindResult(string $select = null) {
        $dql = $this->getFindDQL($select);

        $q = $this->em->createQuery($dql);

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
            if(!isset($ids[$r['id']])){
                $ids[$r['id']] = true;
                $result[] = $r;
            }
        }
        
        $this->processEntities($result);

        return $result;
    }

    
    public function count(){
        return $this->getCountResult();
    }
    
    public function getCountResult() {
        $dql = $this->getCountDQL();

        $q = $this->em->createQuery($dql);
        
        $params = $this->getDqlParams();

        $q->setParameters($params);
        
        $this->logDql($dql, __FUNCTION__, $params);

        $result = $q->getSingleScalarResult();

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
        $select = $select ?: $this->generateSelect();
        $where = $this->generateWhere();
        $joins = $this->generateJoins();
        $order = $this->generateOrder();

        $dql = "SELECT\n\t{$select}\nFROM \n\t{$this->entityClassName} e {$joins}";
        if ($where) {
            $dql .= "\nWHERE\n\t{$where}";
        }

        if ($order) {
            $dql .= "\n\nORDER BY {$order}";
        }

        return $dql;
    }

    public function getCountDQL() {
        $where = $this->generateWhere();
        $joins = $this->generateJoins();

        $dql = "SELECT\n\tCOUNT(e.id)\nFROM \n\t{$this->entityClassName} e {$joins}";
        if ($where) {
            $dql .= "\nWHERE\n\t{$where}";
        }

        return $dql;
    }

    public function getSubDQL($prop = 'id') {
        $where = $this->generateWhere();
        $joins = $this->generateJoins();

        $alias = 'e_' . uniqid();
        
        if(isset($this->entityRelations[$prop])){
            $identity = "IDENTITY({$alias}.{$prop})";
        } else {
            $identity = "{$alias}.{$prop}";
        }
        
        $dql = " SELECT $identity FROM {$this->entityClassName} {$alias} {$joins} ";
        if ($where) {
            $dql .= " WHERE {$where} ";
        }

        $result = preg_replace('#([^a-z0-9_])e([\. ])#i', "$1{$alias}$2", $dql);
        return $result;
    }
    
    protected function getSelecting(){
        return $this->_selecting;
    }

    protected function getSubqueryInIdentities(array $entities, $property = 'id') {
        if (count($entities) > $this->maxBeforeSubquery && !$this->getOffset() && !$this->getLimit()) {
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
                $result = "'-1'";
            } else {
                $result = implode(',', $identity);
            }
        }
        
        return $result;
    }
    
    function getKeywordSubDQL(){
        $dql = '';
        if($this->_keyword){
            $alias = $this->getAlias('kword');
            
            $_keyword_dql = $this->entityRepository->getIdsByKeywordDQL($this->_keyword, $alias);
            $_keyword_dql = preg_replace('#([^a-z0-9_])e\.#i', "$1{$alias}.", $_keyword_dql);
            $_keyword_dql = str_replace("{$this->entityClassName} e", "{$this->entityClassName} {$alias}", $_keyword_dql);
            
            $dql = "e.id IN ($_keyword_dql)";
            $this->_dqlParams['keyword'] = "%{$this->_keyword}%";
        }
        
        return $dql;
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

                if($subsite_query){
                    $filters[] = ['subquery' => $subsite_query, 'subquery_property' => 'id', 'property' => 'id'];
                }
            }
        }
        
        return $filters;
    }

    protected function generateWhere() {
        
        $where = $this->where;
        $where_dqls = implode(" $this->_op \n\t", $this->_whereDqls);
        
        if($where){
            $where = $where_dqls ? "$where AND $where_dqls" : $where;
        } else {
            $where = $where_dqls;
        }
        
        if($this->usesStatus && (!$this->_subsiteId && !isset($this->apiParams['status']) || $this->_permission != 'view')){
            $where = $where ? "($where) AND e.status {$this->_status}" : "e.status {$this->_status}";
        }
        
        if($keyword_where = $this->getKeywordSubDQL()){
            $where .= " AND $keyword_where";
        }
        
        $filters = $this->getSubqueryFilters();
        
        foreach($filters as $filter){
            $subquery = $filter['subquery'];
            $subquery_property = $filter['subquery_property'];
            $property = $filter['property'];
            
            $sub_dql = $subquery->getSubDQL($subquery_property);
            
            $where .= " AND e.{$property} IN ({$sub_dql})";
        }
        
        if(!$where) {
            $where = 'e.id > 0';
        }
        
        if($this->_subsiteId){
            $where = "($where) OR e._subsiteId = {$this->_subsiteId}";

            if($this->entityClassName == "MapasCulturais\Entities\Agent" && App::i()->auth->isUserAuthenticated()) { // entidade é agent e o usuário esta logado?
                $userID = App::i()->user->id;
                $where = "$where OR e.userId = {$userID}"; //Adiciona todos os agentes pertecentes ao usuário a resposta.
            }
        }

        return $where;
    }

    protected function generateJoins() {
        $app = App::i();
        $joins = $this->joins;
        $class = $this->entityClassName;
        
        if($this->_selectingOriginSiteUrl && $this->usesOriginSubsite){
            $joins .= ' LEFT JOIN MapasCulturais\Entities\Subsite __subsite__ WITH __subsite__.id = e._subsiteId';
        }
        
        if($this->usesSealRelation && $this->_seals){
            $sl = $this->getAlias('sl');
            $slv = implode(',', $this->_seals);
            $joins .= " JOIN e.__sealRelations {$sl} WITH {$sl}.seal IN ($slv)";
        }

        return $joins;
    }
    
    protected $_removeFromResult = [];

    protected function generateSelect() {
        $select = $this->select;
        $class = $this->entityClassName;
        
        if(!in_array('id', $this->_selectingProperties)){
            $this->_selectingProperties = array_merge(['id'], $this->_selectingProperties);
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
            if ($mapping && $mapping['type'] === 2 && $mapping['isOwningSide']) {
                $select .= ", IDENTITY(e.{$prop}) AS $prop";
                $cfg['selected'] = true;

                if ($cfg['select'] === 'id') {
                    $cfg['skip'] = true;
                }
            }
        }
        
        if($this->_selectingOriginSiteUrl){
            $select .= ', __subsite__.url AS originSiteUrl';
        }
        
        return $select;
    }

    protected function generateOrder() {
        if ($this->_order) {
            $order = [];
            foreach (explode(',', $this->_order) as $prop) {
                $key = trim(preg_replace('#asc|desc#i', '', $prop));
                if (key_exists($key, $this->_keys)) {
                    $order[] = str_ireplace($key, $this->_keys[$key], $prop);
                } elseif (in_array($key, $this->entityProperties)) {
                    $order[] = str_ireplace($key, 'e.' . $key, $prop);
                } elseif (in_array($key, $this->registeredMetadata)) {
                    
                    $meta_alias = $this->getAlias('meta_'.$key);
                    
                    $this->joins .= str_replace(['{ALIAS}', '{KEY}'], [$meta_alias, $key], $this->_templateJoinMetadata);

                    $order[] = str_replace($key, "$meta_alias.value", $prop);
                }
            }
            return implode(', ', $order);
        } else {
            return null;
        }
    }
    
    protected function processEntities(array &$entities) {
        $this->appendMetadata($entities);
        $this->appendRelations($entities);
        $this->appendFiles($entities);
        $this->appendTerms($entities);
        $this->appendIsVerified($entities);
        $this->appendVerifiedSeals($entities);
        $this->appendSeals($entities);
        
        // @TODO: metalist (copiar o files)
        // @TODO: relatedAgents
        // @TODO: seals
        
        $app = app::i();
        
        // @TODO: não existe hoje uma forma de conseguir a url do site principal
        $main_site_url = $app->config['base.url'];
        
        if($this->_selectingType){
            $types = $app->getRegisteredEntityTypes($this->entityClassName);
        }

        if($this->permissionCacheClassName){
            $permissions = $this->getViewPrivateDataPermissions($entities);
        }
        
        foreach ($entities as &$entity){
            // remove location if the location is not public
            if($this->permissionCacheClassName && isset($entity['location']) && isset($entity['publicLocation']) && !$entity['publicLocation']){
                if(!$permissions[$entity['id']]){
                    $entity['location']->latitude = 0;
                    $entity['location']->longitude = 0;
                }
            }

            foreach($this->_selectingUrls as $action){
                $entity["{$action}Url"] = $this->entityController->createUrl($action, [$entity['id']]);
            }
            
            // convert Occurrences rules to object
            if (key_exists('rule', $entity) && !empty($entity['rule'])) {
                $entity['rule'] = json_decode($entity['rule']);
            }
            
            if($this->_selectingOriginSiteUrl && empty($entity['originSiteUrl'])){
                $entity['originSiteUrl'] = $main_site_url;
            }
            
            if($this->_selectingType){
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
        }
    }

    protected function appendMetadata(array &$entities) {
        $app = App::i();
        $metadata = [];
        $definitions = $app->getRegisteredMetadata($this->entityClassName);

        if ($this->_selectingMetadata && count($entities) > 0) {
            
            $permissions = $this->getViewPrivateDataPermissions($entities);

            $meta_keys = [];
            foreach ($this->_selectingMetadata as $meta) {
                $meta_keys[uniqid('p')] = $meta;
            }

            $keys = ':' . implode(',:', array_keys($meta_keys));

            $in_entities_dql = $this->getSubqueryInIdentities($entities);

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
                ORDER BY e.id";

            $q = $this->em->createQuery($dql);

            if($this->_usingSubquery){
                $q->setParameters($meta_keys + $this->_dqlParams);
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
            $entity_id = $entity['id'];
            
            if (isset($metadata[$entity_id])) {
                $can_view = $permissions[$entity_id];
                
                $meta = $metadata[$entity_id];
                foreach($meta as $k => $v){
                    $private = $definitions[$k]->private;
                    if(is_callable($private)){
                        $private = \Closure::bind($private, (object) $entity);
                        if($private() && !$can_view){
                            unset($meta[$k]);
                        }
                    }else if($private && !$can_view){
                        unset($meta[$k]);
                    }
                }
                
                $entity += $meta;
            }
        }
    }
    
    protected function appendPermissions(array &$entities, array $select){
        $skel = [];
        $admin_skel = [];
        $class = $this->entityClassName;
        $user = App::i()->user;
        
        $dql_in = $this->getSubqueryInIdentities($entities);
        
        foreach($select as $permission){
            $admin_skel[$permission] = true;
            if($permission == 'view' && !$class::isPrivateEntity()){
                $skel['view'] = true;
            } else {
                $skel[$permission] = false;
            }
        }
        
        $dql = "
            SELECT
                IDENTITY(pc.owner) AS ownerId,
                pc.action
            FROM
                {$this->permissionCacheClassName} pc
            WHERE
                pc.action IN (:pcache_select) AND
                pc.owner IN ($dql_in) AND
                pc.user = {$user->id}";
                
        
                
             
        $query = $this->em->createQuery($dql);
        
        if($this->_usingSubquery){
            $params = $this->getDqlParams();
            $params['pcache_select'] = $select;
            
        } else {
            $params = ['pcache_select' => $select];
        }
        
        $query->setParameters($params);
        
        $result = $query->getResult();
        
        $permissions = [];
                
        foreach($result as $r){
            $owner_id = $r['ownerId'];
            $action = $r['action']->getValue();
            if(!isset($permissions[$owner_id])){
                $permissions[$owner_id] = [];
            }
            
            $permissions[$owner_id][$action] = true;
        }
        
        foreach($entities as &$entity){
            if(($this->usesOriginSubsite && $user->is('admin', $entity['_subsiteId'])) || (!$this->usesOriginSubsite && $user->is('saasAdmin'))){
                $entity['permissionTo'] = $admin_skel;
            } else {
                $entity_id = $entity['id'];
                $entity['permissionTo'] = isset($permissions[$entity_id]) ? $permissions[$entity_id] + $skel : $skel;
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
                $prop = $cfg['property'];
                
                // do usuário só permite id e profile
                if($prop == 'user') {
                    $cfg['select'] = array_filter($cfg['select'], function($field) {
                        if ($field == 'id' || substr($field, 0, 7) == 'profile') {
                            return $field;
                        }
                    });
                }
                
                if($prop == 'permissionTo'){
                    $this->appendPermissions($entities, $cfg['select']);
                    continue;
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
                        $_target_property = 'id';
                    }else if ($mtype === 2) {
                        if ($selected) {
                            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities, $prop);
                        } else {
                            $_subquery_where_id_in = $this->getSubDQL($prop);
                        }
                        $_target_property = $mapping['joinColumns'][0]['referencedColumnName'];
                        
                    } else {
                        $_subquery_where_id_in = $this->getSubqueryInIdentities($entities, 'id');

                        $_target_property = $mapping['mappedBy'];
                    }
                    
                    if(!$_subquery_where_id_in){
                        continue;
                    }
                    
                    if($select != '*'){
                        $select = "$_target_property,$select";
                    }
                    
                    $query = new ApiQuery($target_class, ['@select' => $select], false, $cfg['selectAll'], !$this->_accessControlEnabled, $this);
                    
                    $query->name = "{$this->name}->$prop";

                    $query->where = (empty($query->where)) ? "e.{$_target_property} IN ({$_subquery_where_id_in})" : $query->where. " AND e.{$_target_property} IN ({$_subquery_where_id_in})";
                    
                    if($this->_usingSubquery){
                        foreach($this->_dqlParams as $k => $v){
                            $query->_dqlParams[$k] = $v;
                        }
                    }

                    $cfg['query'] = $query;
                    $cfg['query_result'] = [];
                    $subquery_result = $query->getFindResult();
                    
                    if($mtype == 2) {
                        foreach ($subquery_result as &$r) {
                            if($original_select === 'id'){
                                $subquery_result_index[$r[$_target_property]] = $r['id'];

                            } else {
                                $subquery_result_index[$r[$_target_property]] = &$r;
                                if(!in_array($_target_property, $query->_selecting)){
                                    unset($r[$_target_property]);
                                }
                            }
                        }
                    } else {
                        foreach ($subquery_result as &$r) {
                            if(!isset($subquery_result_index[$r[$_target_property]])){
                                $subquery_result_index[$r[$_target_property]] = [];
                            }
                            if($original_select === 'id'){
                                $subquery_result_index[$r[$_target_property]][] = $r['id'];

                            } else {
                                $subquery_result_index[$r[$_target_property]][] = &$r;
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
                            if($k == $entity['id'] || $k == $entity){
                                $entity[$prop] = $relation;
                            }
                        }
                    }
                }
                

            }
        }
    }
    
    protected function appendFiles(array &$entities){
        if(!$this->_selectingFiles){
            return;
        }
        
        $app = App::i();
        
        $file_groups = $app->getRegisteredFileGroupsByEntity($this->entityClassName);
        
        $where = [];
        foreach($this->_selectingFiles as $select){
            if(strpos($select, '.') > 0){
                list($group, $transformation) = explode('.', $select);
                $where[] = "(f.group = 'img:{$transformation}' AND fp.group = '{$group}')";
            } else {
                $where[] = "(f.group = '{$select}')";
            }
        }
        $sub = $this->getSubqueryInIdentities($entities);
        
        $where = implode(' OR ', $where);
        
        $dql = "
            SELECT
                f.id,               
                f.name,
                f.description,
                f._path,
                f.group as file_group,
                f.private,
                fp.group as parent_group,
                IDENTITY(f.owner) AS owner_id,
                f.private
            FROM
                {$this->fileClassName} f
                    LEFT JOIN f.parent fp
            WHERE
                f.owner IN ({$sub}) AND ({$where})
            ORDER BY f.id ASC";
                
                
        $query = $this->em->createQuery($dql);

        if($this->_usingSubquery){
            $query->setParameters($this->_dqlParams);
        }
        
        $restul = $query->getResult(Query::HYDRATE_ARRAY);
        
        $files = [];
        
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
        
        foreach($entities as &$entity){
            $id = $entity['id'];
            if(isset($files[$id])){
                foreach($files[$id] as $f){
                    if(!isset($file_groups[$f['mainGroup']])){
                        continue;
                    }
                    
                    if(true) { // método para compatibilidade da v1 da api
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
                    } else {
                        // @TODO: implementar o novo método de retorno de imagens
                    }
                    
                }
            }
        }
        
    }
    
    protected function appendTerms(array &$entities){
        $class = $this->entityClassName;
        $term_relation_class_name = $this->termRelationClassName;
        if($term_relation_class_name && in_array('terms', $this->_selecting)){
            $app = App::i();
            $dql_in = $this->getSubqueryInIdentities($entities);
            
            $taxonomies = [];
            $skel = [];
            foreach($app->getRegisteredTaxonomies($class) as $slug => $def){
                $taxonomies[$def->slug] = $slug;
                $skel[$slug] = [];
            }
            // --------------------
            
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
            
            if($this->_usingSubquery){
                $query->setParameters($this->_dqlParams);
            }
            
            $result = $query->getResult(Query::HYDRATE_ARRAY);
            
            $terms_by_entity = [];
            
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
            
            foreach($entities as &$entity){
                $id = $entity['id'];
                
                $entity['terms'] = isset($terms_by_entity[$id]) ? $terms_by_entity[$id] : $skel;
            }
        }
    }

    protected $_relatedSeals = null;

    protected function _fetchRelatedSeals(array &$entities){
        if(is_null($this->_relatedSeals)){
            $app = App::i();

            $dql_in = $this->getSubqueryInIdentities($entities);
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
            
            if($this->_usingSubquery){
                $query->setParameters($this->_dqlParams);
            }

            $relations = $query->getResult(Query::HYDRATE_ARRAY);

            $this->_relatedSeals = [];
            foreach($relations as $relation){
                $relation = (object) $relation;
                
                $entity_id = $relation->entity_id;

                if(!isset($this->_relatedSeals[$entity_id])){
                    $this->_relatedSeals[$entity_id] = [];
                }

                $this->_relatedSeals[$entity_id][] = [
                    'name' => $relation->seal_name,
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
                if(isset($this->_relatedSeals[$entity['id']])){
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
                if(isset($this->_relatedSeals[$entity['id']])){
                    $entity['verifiedSeals'] = array_filter($this->_relatedSeals[$entity['id']], function($s){
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
                if(isset($this->_relatedSeals[$entity['id']])){
                    $entity['seals'] = $this->_relatedSeals[$entity['id']];
                } else {
                    $entity['seals'] = [];
                }
            }
        }
    }

    protected function _appendSeals(array &$entities, $prop_name, array $seals){

    }

    
    private $__viewPrivateDataPermissions = null;
    
    protected function getViewPrivateDataPermissions(array $entities){
        if(is_null($this->__viewPrivateDataPermissions)){
            $this->__viewPrivateDataPermissions = [];
            
            $app = App::i();
            
            $is_admin = $app->user->is('admin') ;
            if($is_admin || $app->user->is('guest')){
                foreach($entities as $entity){
                    $this->__viewPrivateDataPermissions[$entity['id']] = $is_admin;
                }
            } else {
                $dql_in = $this->getSubqueryInIdentities($entities);
                
                $dql = "SELECT IDENTITY(pc.owner) as entity_id FROM {$this->permissionCacheClassName} pc WHERE pc.owner IN ($dql_in) AND pc.user = {$app->user->id}";

                $query = $this->em->createQuery($dql);

                if($this->_usingSubquery){
                    $query->setParameters($this->_dqlParams);
                }

                $qr = $query->getResult(Query::HYDRATE_ARRAY);

                
                foreach($entities as $entity){
                    $this->__viewPrivateDataPermissions[$entity['id']] = false;
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
            if (trim($value) === '@me') {
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

                if (count($values) < 1) {
                    throw new Exceptions\Api\InvalidArgument('expression IN expects at last one value');
                }

                $dql = $not ? "$key NOT IN (" : "$key IN (";
                $dql .= implode(', ', $values) . ')';
                
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
            }

            /*
             * location=GEO_NEAR([long,lat]) //
             */
            return $dql;
        }
    }

    private function splitParam($val) {
        $result = explode("\n", str_replace('\\,', ',', preg_replace('#(^[ ]*|([^\\\]))\,#', "$1\n", $val)));

        if (count($result) === 1 && !$result[0]) {
            return [];
        } else {
            $_result = [];
            foreach ($result as $r)
                if ($r)
                    $_result[] = $r;
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
                $this->_order = $value;
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
    }
    
    protected function _addFilterBySeals($seals_ids){
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
        if($this->_accessControlEnabled && $this->_permission && !$user->is('saasAdmin')){
            $alias = $this->getAlias('pcache');
            
            $this->_filteringByPermissions = true;
            
            $pkey = $this->addSingleParam($this->_permission);
            $_uid = $user->id;
            
            if(($this->_permission != 'view' || $class::isPrivateEntity()) && (!$this->usesOriginSubsite || !$this->adminInSubsites)) {
                $this->joins .= " JOIN e.__permissionsCache $alias WITH $alias.action = $pkey AND $alias.userId = $_uid ";
                
            } else {
                $this->select =  $this->select ? ", $alias.action " : $this->select;
                
                $admin_where = '';
                $view_where = '';
                
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
                
                if($this->usesStatus && $this->_permission == 'view' && !$class::isPrivateEntity()) {
                    $view_where = 'OR e.status > 0';
                }
                
                $this->where .= " $and ( e.id IN (SELECT IDENTITY($alias.owner) FROM {$this->permissionCacheClassName} $alias WHERE $alias.owner = e AND $alias.action = $pkey AND $alias.userId = $_uid) $admin_where $view_where) ";
            }
        }
    }

    protected function _addFilterByOwnerUser($value) {
        
        $this->_keys['user'] = '__user_agent__.user';

        $this->joins .= "\n\tLEFT JOIN e.owner __user_agent__\n";
        
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
    
    public function addFilterByApiQuery(ApiQuery $subquery, $subquery_property = 'id', $property = 'id'){
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
        
        $properties = array_merge(
                    $this->entityProperties,
                    $this->registeredMetadata,
                    ['terms'],
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
        $select = str_replace(' ', '', $select);
        
        if($select === '*'){
            $select = implode(',', $this->_getAllPropertiesNames());
        }
        $replacer = function ($select, $prop, $_subquery_select, $_subquery_match){
            $replacement = $this->_preCreateSelectSubquery($prop, $_subquery_select, $_subquery_match);
            if(is_null($replacement)){
                $select = str_replace(["$_subquery_match,", ",$_subquery_match"], '', $select);

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

        $entity_class = $this->entityClassName;
        foreach ($this->_selecting as $i => $prop) {
            if(!$prop){
                continue;
            }
            if (in_array($prop, $this->entityProperties)) {
                $this->_selectingProperties[] = $prop;
            } elseif (in_array($prop, $this->registeredMetadata)) {
                $this->_selectingMetadata[] = $prop;
            } elseif ($prop[0] != '_' && isset($this->entityRelations[$prop])) {
                $this->_selecting[$i] = $this->_preCreateSelectSubquery($prop, 'id', $prop);
            } elseif ($prop[0] != '_' && isset($this->entityRelations["_{$prop}"])) {
                $this->_selecting[$i] = $this->_preCreateSelectSubquery("_{$prop}", 'id', $prop);
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

        $first_time = !isset($this->_selectingRelations[$prop]);
        
        if(isset($this->_selectingRelations[$prop])){
            $uid = $this->_selectingRelations[$prop];
            $cfg = &$this->_subqueriesSelect[$uid];
            
            $cfg['select'] = array_merge($cfg['select'],array_diff($select,$cfg['select']));
            $cfg['match'] = "{$prop}.\{" . implode(',', $cfg['select']) . "\}";
            
            $result = null;
            
        } else {
            $uid = uniqid('#sq:');
            
            $this->_selectingRelations[$prop] = $uid;
            
            $this->_subqueriesSelect[$uid] = [
                'selectAll' => $_select_all,
                'property' => $prop,
                'select' => array_unique($select),
                'match' => $_match,
            ];
            
            $result = $uid;
        }
        
        if($first_time && $prop === 'user' && !isset($this->entityRelations['user']) && $this->usesOwnerAgent){
            $user_alias = $this->getAlias('user');
            $owner_alias = $this->getAlias('owner');
            $this->select .=  $this->select ? ", IDENTITY({$owner_alias}.user) AS user" : "IDENTITY({$owner_alias}.user) AS user";
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
