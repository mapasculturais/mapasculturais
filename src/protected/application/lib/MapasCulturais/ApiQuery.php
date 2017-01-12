<?php

namespace MapasCulturais;

use Doctrine\ORM\Query;

class ApiQuery {

    use Traits\MagicGetter;

    /**
     * Global counter used to name DQL alias
     * @var int
     */
    protected $__counter = 0;
    
    protected $maxBeforeSubquery = 1024;
    protected $_usingSubquery = false;
    
    protected $name;

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
    
    protected $fileClassName;
    
    protected $termRelationClassName;
    
    protected $permissionCacheClassName;

    /**
     * List of the entity properties
     * 
     * @var array
     */
    protected $entityProperties = [];

    /**
     * List of entity ralations
     * 
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
     * 
     * @var array 
     */
    protected $registeredTaxonomies = [];

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
     * 
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
     * @var type 
     */
    protected $_keys = [];

    /**
     * List of parameters that will be used to run the DQL
     * @var array 
     */
    protected $_dqlParams = [];

    /**
     * Fields that are being selected
     * 
     * @var type 
     */
    protected $_selecting = ['id'];

    /**
     * Slice of the fields that are being selected that are properties of the entity
     * 
     * @var type 
     */
    protected $_selectingProperties = [];

    /**
     * Slice of the fields that are being selected that are metadata of the entity
     * 
     * @var type 
     */
    protected $_selectingMetadata = [];
    
    protected $_selectingRelations = [];
    
    protected $_selectingUrls = [];

    /**
     * Files that are being selected
     * 
     * @var type 
     */
    protected $_selectingFiles = [];
    protected $_selectingFilesProperties = ['url'];
    
    protected $_subqueriesSelect = [];
    protected $_order = 'id ASC';
    protected $_offset;
    protected $_page;
    protected $_limit;
    protected $_keyword;
    protected $_seals = [];
    protected $_permissions;
    protected $_status = '> 0';
    protected $_op = ' AND ';
    protected $_templateJoinMetadata = "\n\tLEFT JOIN e.__metadata {ALIAS} WITH {ALIAS}.key = '{KEY}'";
    protected $_templateJoinTerm = "\n\tLEFT JOIN e.__termRelations {ALIAS_TR} LEFT JOIN {ALIAS_TR}.term {ALIAS_T} WITH {ALIAS_T}.taxonomy = '{TAXO}'";

    protected $_appendOriginSubsiteUrl = false;
    
    public function __construct($entity_class_name, $api_params) {
        $this->initialize($entity_class_name, $api_params);

        $this->parseQueryParams();
    }

    protected function initialize($entity_class_name, $api_params) {
        $app = App::i();
        $em = $app->em;

        $this->apiParams = $api_params;
        $this->entityClassName = $entity_class_name;
        

        if ($entity_class_name::usesFiles()) {
            $this->fileClassName = $entity_class_name::getFileClassName();
        }
        
        if ($entity_class_name::usesTaxonomies()) {
            $this->termRelationClassName = $entity_class_name::getTermRelationClassName();
        }
        
        if ($entity_class_name::usesPermissionCache()) {
            $this->permissionCacheClassName = $entity_class_name::getPermissionCacheClassName();
        }
        
        if ($entity_class_name::usesMetadata()) {
            $this->metadataClassName = $entity_class_name::getMetadataClassName();

            foreach ($app->getRegisteredMetadata($entity_class_name) as $meta) {
                $this->registeredMetadata[] = $meta->key;
            }
        }

        if ($entity_class_name::usesTaxonomies()) {
            foreach ($app->getRegisteredTaxonomies($entity_class_name) as $obj) {
                $this->registeredTaxonomies['term:' . $obj->slug] = $obj->slug;
            }
        }

        $this->entityProperties = array_keys($em->getClassMetadata($entity_class_name)->fieldMappings);
        $this->entityRelations = $em->getClassMetadata($entity_class_name)->associationMappings;
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

    public function getFindOneResult() {
        $em = App::i()->em;

        $dql = $this->getFindDQL();

        $q = $em->createQuery($dql);

        if ($offset = $this->getOffset()) {
            $q->setFirstResult($offset);
        }

        $q->setMaxResults(1);

        $q->setParameters($this->_dqlParams);

        $result = $q->getOneOrNullResult(Query::HYDRATE_ARRAY);
        
        $this->logDql($dql, __FUNCTION__, $this->_dqlParams);

        if ($result) {
            $_tmp = [&$result]; // php !!!!
            $this->processEntities($_tmp);

        }

        return $result;
    }

    public function getFindResult() {
        $em = App::i()->em;

        $dql = $this->getFindDQL();

        $q = $em->createQuery($dql);

        if ($offset = $this->getOffset()) {
            $q->setFirstResult($offset);
        }

        if ($limit = $this->getLimit()) {
            $q->setMaxResults($limit);
        }

        $q->setParameters($this->_dqlParams);
        
        $this->logDql($dql, __FUNCTION__, $this->_dqlParams);

        $result = $q->getResult(Query::HYDRATE_ARRAY);

        $this->processEntities($result);

        return $result;
    }

    public function getCountResult() {
        $em = App::i()->em;

        $dql = $this->getCountDQL();

        $q = $em->createQuery($dql);

        $q->setParameters($this->_dqlParams);
        
        $this->logDql($dql, __FUNCTION__, $this->_dqlParams);

        $result = $q->getSingleScalarResult();

        return $result;
    }

    public function getFindDQL() {
        $select = $this->generateSelect();
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

        $result = preg_replace('#([^a-z0-9_])e\.#i', "$1{$alias}.", $dql);
        return $result;
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

    protected function generateWhere() {
        $where = $this->where;
        $where_dqls = $this->_whereDqls;

        $where .= implode(" $this->_op \n\t", $where_dqls);

        $where = $where ? "($where) AND e.status {$this->_status}" : "e.status {$this->_status}";

        return $where;
    }

    protected function generateJoins() {
        $app = App::i();
        $joins = $this->joins;
        
        if($this->_appendOriginSubsiteUrl){
            $joins .= ' LEFT JOIN MapasCulturais\Entities\Subsite __subsite__ WITH __subsite__.id = e._subsiteId';
        }                

        return $joins;
    }

    protected function generateSelect() {
        $select = $this->select;
        
        if(!in_array('id', $this->_selectingProperties)){
            $this->_selectingProperties = array_merge(['id'], $this->_selectingProperties);
        }
        
        if (count($this->_selectingProperties) > 1 && in_array('publicLocation', $this->entityProperties) && !in_array('publicLocation', $this->_selectingProperties)) {
            $this->_selectingProperties[] = 'publicLocation';
        }
        

        $select .= implode(', ', array_map(function ($e) {
                    return "e.{$e}";
                }, $this->_selectingProperties));

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
        
        if($this->_appendOriginSubsiteUrl){
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
                    $meta_num = $this->__counter++;
                    $meta_alias = "m{$meta_num}";
                    $this->joins .= str_replace(['{ALIAS}', '{KEY}'], [$meta_alias, $key], $this->_templateJoinMetadata);

                    $order[] = str_replace($key, "$meta_alias.value", $prop);
                }
            }
            return implode(', ', $order);
        } else {
            return null;
        }
    }
    
    protected function processEntities(array &$result) {
        $this->appendMetadata($result);
        $this->appendRelations($result);
        $this->appendFiles($result);
        $this->appendTerms($result);
        $this->appendUrls($result);
        
        // @TODO: metalist (copiar o files)
        // @TODO: relatedAgents
        // @TODO: seals
        
        
    }

    protected function appendMetadata(array &$entities) {
        $app = App::i();
        $metadata = [];
        $definitions = $app->getRegisteredMetadata($this->entityClassName);
        if ($this->_selectingMetadata && count($entities) > 0) {
            $em = $app->em;
            
            $permissions = $this->getViewPrivateDataPermissions($entities);

            $meta_keys = [];
            foreach ($this->_selectingMetadata as $meta) {
                $meta_keys[uniqid('p')] = $meta;
            }

            $keys = ':' . implode(',:', array_keys($meta_keys));

            $count = $this->__counter++;

            $in_entities_dql = $this->getSubqueryInIdentities($entities);

            $dql = "SELECT e.key, e.value, IDENTITY(e.owner) AS objectId FROM {$this->metadataClassName} e WHERE e.owner IN ({$in_entities_dql}) AND e.key IN({$keys}) ORDER BY e.id";

            $q = $em->createQuery($dql);

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
    
    protected function appendRelations(array &$entities) {
        if ($this->_subqueriesSelect) {
            $_subquery_where_id_in = $this->getSubqueryInIdentities($entities);
            if(!$_subquery_where_id_in){
                return;
            }
            foreach ($this->_subqueriesSelect as $k => &$cfg) {
                $prop = $cfg['property'];
                if(isset($this->entityRelations[$prop])){
                    $mapping = $this->entityRelations[$prop];
                } else {
                    $mapping = $this->entityRelations['_' . $prop];                
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
                    
                    if ($mtype === 2) {
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
                    
                    $query = new ApiQuery($target_class, ['@select' => $select]);
                    $query->name = "{$this->name}->$prop";

                    $query->where = "e.{$_target_property} IN ({$_subquery_where_id_in})";

                    $cfg['query'] = $query;
                    $cfg['query_result'] = [];
                    $subquery_result = $query->getFindResult();
                    if($mtype == 2) {
                        foreach ($subquery_result as &$r) {
                            if($original_select === 'id'){
                                $subquery_result_index[$r[$_target_property]] = $r['id'];

                            } else {
                                $subquery_result_index[$r[$_target_property]] = &$r;
                                unset($r[$_target_property]);
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
                                unset($r[$_target_property]);
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
        
        $em = $app->em;
        
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
                fp.group as parent_group,
                IDENTITY(f.owner) AS owner_id
            FROM 
                {$this->fileClassName} f 
                    LEFT JOIN f.parent fp
            WHERE
                f.owner IN ({$sub}) AND ({$where})
            ORDER BY f.id ASC";
                
                
        $query = $em->createQuery($dql);

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
            
            $f['url'] = $app->storage->getUrlFromRelativePath($f['_path']);
            
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
            $em = $app->em;
            $dql_in = $this->getSubqueryInIdentities($entities);
            
            // @TODO: refatorar com o merge da refatoração dos slugs das taxonomias
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
                
            $query = $em->createQuery($dql);
            
            if($this->_usingSubquery){
                $query->setParameters($this->_dqlParams);
            }
            
            $result = $query->getResult(Query::HYDRATE_ARRAY);
            
            $terms_by_entity = [];
            
            foreach($result as $term){
                // @TODO: refatorar com o merge da refatoração dos slugs das taxonomias
                if(!isset($taxonomies[$term['taxonomy']])){
                    continue;
                }
                // --------------------
                
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
    
    protected function appendUrls(array &$entities){
        if(!$this->_selectingUrls && !$this->_appendOriginSubsiteUrl){
            return;
        }
        
        $app = app::i();
        
        $controller = $app->getControllerByEntity($this->entityClassName);
        
        // @TODO: não existe hoje uma forma de conseguir a url do site principal
        
        $main_site_url = $app->config['base.url'];
                
        foreach ($entities as &$entity){
            foreach($this->_selectingUrls as $action){
                $entity["{$action}Url"] = $controller->createUrl($action, [$entity['id']]);
            }
            
            if($this->_appendOriginSubsiteUrl && empty($entity['originSiteUrl'])){
                $entity['originSiteUrl'] = $main_site_url;
            } 
        }
    }
    
    private $__viewPrivateDataPermissions = null;
    
    protected function getViewPrivateDataPermissions(array $entities){
        if(is_null($this->__viewPrivateDataPermissions)){
            $this->__viewPrivateDataPermissions = [];
            
            $app = App::i();
            $em = $app->em;
            $is_admin = $app->user->is('admin') ;
            if($is_admin || $app->user->is('guest')){
                foreach($entities as $entity){
                    $this->__viewPrivateDataPermissions[$entity['id']] = $is_admin;
                }
            } else {
                $dql_in = $this->getSubqueryInIdentities($entities);
                $dql = "SELECT IDENTITY(pc.owner) as entity_id FROM {$this->permissionCacheClassName} pc WHERE pc.owner IN ($dql_in) AND pc.user = {$app->user->id}";

                $query = $em->createQuery($dql);

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
            if (strtolower($key) == '@select') {
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
            } elseif (strtolower($key) == '@permissions') {
                $this->_addFilterByPermissions($value);
            } elseif (strtolower($key) == '@seals') {
                $this->_seals = explode(',', $value);
            } elseif (strtolower($key) == '@verified') {
                $this->_seals = $app->config['app.verifiedSealsIds'];
            } elseif (strtolower($key) == '@or') {
                $this->_op = ' OR ';
            } elseif (strtolower($key) == '@files') {
                $this->_parseFiles($value);
            } elseif ($key === 'user' && $class::usesOwnerAgent()) {
                $this->_addFilterByOwnerUser($value);
            } elseif (key_exists($key, $this->entityRelations) && $this->entityRelations[$key]['isOwningSide']) {
                $this->_addFilterByEntityProperty($key, $value);
            } elseif (in_array($key, $this->entityProperties)) {
                $this->_addFilterByEntityProperty($key, $value);
            } elseif ($class::usesTypes() && $key === 'type') {
                $this->_addFilterByEntityProperty($key, $value, '_type');
            } elseif ($key[0] !== '_' && strpos($key, '.') > 0) {
                $this->_addFilterByEntityRelation($key, $value);
            } elseif ($class::usesTaxonomies() && isset($this->registeredTaxonomies[$key])) {
                $this->_addFilterByTermTaxonomy($key, $value);
            } elseif ($class::usesMetadata() && in_array($key, $this->registeredMetadata)) {
                $this->_addFilterByMetadata($key, $value);
            } elseif ($key[0] !== '_' && $key != 'callback') {
                $this->apiErrorResponse("property $key does not exists");
            }
        }
    }
    
    protected function _addFilterByPermissions($value) { 
        $app = App::i();
        $this->_permissions = explode(',', $value);
        
        if($this->_permissions && !$app->user->is('admin')){
            $pkey = implode(',', $this->addMultipleParams($this->_permissions));
            $_uid = $app->user->id;
            $this->joins .= "JOIN e.__permissionsCache __pcache WITH __pcache.action IN($pkey) AND __pcache.userId = $_uid";
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
        $count = $this->__counter++;
        $meta_alias = "m{$count}";

        $this->_keys[$key] = "$meta_alias.value";

        $this->joins .= str_replace(['{ALIAS}', '{KEY}'], [$meta_alias, $key], $this->_templateJoinMetadata);

        $this->_whereDqls[] = $this->parseParam($this->_keys[$key], $value);
    }

    protected function _addFilterByTermTaxonomy($key, $value) {
        $count = $this->__counter++;
        $tr_alias = "tr{$count}";
        $t_alias = "t{$count}";
        $taxonomy_slug = $this->registeredTaxonomies[$key];

        $this->_keys[$key] = "$t_alias.term";

        $this->joins .= str_replace(['{ALIAS_TR}', '{ALIAS_T}', '{TAXO}'], [$tr_alias, $t_alias, $taxonomy_slug], $this->_templateJoinTerm);

        $this->_whereDqls[] = $this->parseParam($this->_keys[$key], $value);
    }
    
    protected function _getAllPropertiesNames(){
        $remove_properties = [
            '_geoLocation',
            'isVerified', // deprecated,
        ];
        
        $properties = array_merge(
                    ['terms'],
                    $this->entityProperties,
                    $this->registeredMetadata,
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
        
        $replacer = function ($select, $_subquery_entity_class, $_subquery_select, $_subquery_match){
            $replacement = $this->_preCreateSelectSubquery($_subquery_entity_class, $_subquery_select, $_subquery_match);

            if(is_null($replacement)){
                $select = str_replace(["$_subquery_match,", ",$_subquery_match"], '', $select);

            }else{
                $select = str_replace($_subquery_match, $replacement, $select);
            }

            return $select;
        };

        // create subquery to format entity.* or entity.{id,name}
        while (preg_match('#([^,\.]+)\.(\{[^\{\}]+\})#', $select, $matches)) {
            $_subquery_match = $matches[0];
            $_subquery_entity_class = $matches[1];
            $_subquery_select = substr($matches[2], 1, -1);
            $select = $replacer($select, $_subquery_entity_class, $_subquery_select, $_subquery_match);
        }

        // create subquery to format entity.id or entity.name        
        while (preg_match('#([^,\.]+)\.([^,\.]+)#', $select, $matches)) {
            $_subquery_match = $matches[0];
            $_subquery_entity_class = $matches[1];
            $_subquery_select = $matches[2];

            $select = $replacer($select, $_subquery_entity_class, $_subquery_select, $_subquery_match);
        }

        $this->_selecting = array_unique(explode(',', $select));
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
            } elseif ($prop === 'originSiteUrl' && $entity_class::usesOriginSubsite()) {
                $this->_appendOriginSubsiteUrl = true;
            } elseif (preg_match('#^([a-z][a-zA-Z]*)Url#', $prop, $url_match) && method_exists($this->entityClassName, "get{$prop}")) {
                $this->_selectingUrls[] = $url_match[1];
            } elseif($prop === 'type' && $entity_class::usesTypes()){
                $this->_selectingProperties[] = '_type';
                
            }
        }
    }
    
    protected function _preCreateSelectSubquery($prop, $_select, $_match) {
        if(!isset($this->entityRelations[$prop]) && !isset($this->entityRelations['_' . $prop])){
            return false;
        }
        
        $_select_properties = explode(',', $_select);
        
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
        }, explode(',', $_select));

        
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
                'property' => $prop,
                'select' => array_unique($select),
                'match' => $_match,
            ];
            
            $result = $uid;
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
