<?php
namespace MapasCulturais\Controllers;

use \MapasCulturais\App;

/**
 * This is the base class to Entity Controllers
 *
 * @property-read \MapasCulturais\Entity $newEntity An empty new entity object of the class related to this controller
 * @property-read \Doctrine\ORM\EntityRepository $repository the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
 * @property-read array $fields the fields of the entity with the same name of the controller in the same parent namespace.
 */
abstract class EntityController extends \MapasCulturais\Controller{

    /**
     * Array with key => value of params for the API find DQL
     *
     * @var array
     */
    private $_apiFindParamList = array();

    /**
     * The class name of the entity with the same name of the controller in the same parent namespace.
     *
     * @example for the controller \MapasCulturais\Controllers\User the value will be \MapasCulturais\Entities\User
     * @example for the controller \MyPlugin\Controllers\User the value will be \MyPlugin\Entities\User
     *
     * @var string the entity class name
     */
    protected $entityClassName;



    /**
     * The controllers constructor.
     *
     * This method sets the controller entity class name with an class with the same name of the controller in the parent namespace.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     */
    protected function __construct() {
        $this->entityClassName = preg_replace("#Controllers\\\([^\\\]+)$#", 'Entities\\\$1', get_class($this));
    }


    /**
     * Is this an AJAX request?
     *
     * @return bool
     */
    public function isAjax(){
        return App::i()->request->isAjax();
    }


    /**
     * Creates and returns an empty new entity object of the entity class related with this controller.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     *
     * @return \MapasCulturais\entityClassName An empty new entity object.
     */
    public function getNewEntity(){
        return new $this->entityClassName();
    }


    /**
     * Returns the etity with the requested id.
     *
     * @example for the url http://mapasculturais/agent/33  or http://mapasculturais/agent/id:33 returns the agent with the id 33
     *
     * @return \MapasCulturais\Entity|null
     */
    public function getRequestedEntity(){
        if(!key_exists('id', $this->urlData))
            return null;

        return $this->repository->find($this->urlData['id']);
    }

    /**
     * Returns the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::repo()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(){
        return App::i()->repo($this->entityClassName);
    }



    /**
     * Alias to getRepository
     *
     * @see \MapasCulturais\Controller::getRepository()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return $this->getRepository();
    }


    /**
     * Returns the fields of the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::fields()
     *
     * @return array of fields
     */
    public function getFields(){
        return App::i()->fields($this->entityClassName);
    }

    /**
     * Alias to getFields()
     *
     * @see \MapasCulturais\Entities\EntityController::getFields()
     *
     * @return array of fields
     */
    public function fields(){
        return $this->getFields();
    }


    // ============= ACTIONS =============== //


    /**
     * Default action.
     *
     * This action renders the template 'index' of this controller.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId');
     * </code>
     *
     */
    function GET_index(){
        $this->render('index');
    }

    /**
     * Creates a new entity of the class with same name in the parent\Entities namespace
     *
     * This action requires authentication and outputs the json with the new entity or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId');
     * </code>
     */
    function POST_index(){
        $this->requireAuthentication();

        $entity = $this->newEntity;


        foreach($this->data as $field=>$value){
            $entity->$field = $value;
        }

        if($errors = $entity->validationErrors){
            $this->errorJson($errors);
        }else{
            $entity->save(true);
            $this->json($entity);
        }
    }

    /**
     * Render the create form..
     *
     * This method requires authentication and renders the template 'create' of this controller
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('thisControllerId', 'create');
     * </code>
     *
     */
    function GET_create(){
        $this->requireAuthentication();

        $entity = $this->newEntity;


        $this->render('create', array('entity' => $entity));
    }

    /**
     * Renders the single page of the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action renders the template 'single'
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', array('id' => $agent_id))
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', array($agent_id))
     * </code>
     *
     */
    function GET_single(){
        $app = App::i();

        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->repo()->find($this->urlData['id']);

        if(!$entity)
            $app->pass();

        if($entity->status > 0 || $app->user->is('admin') || $app->user->id === $entity->ownerUser->id)
            $this->render('single', array('entity' => $entity));
        else
            $app->pass();
    }

    /**
     * Renders the edit form for the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and permission to modify the requested entity.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'edit', array('id' => $agent_id))
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'edit', array($agent_id))
     * </code>
     */
    function GET_edit(){
        $this->requireAuthentication();
        $app = App::i();

        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->repo()->find($this->urlData['id']);

        if(!$entity)
            $app->pass();

        $entity->checkPermission('modify');

        $this->render('edit', array('entity' => $entity));
    }

    /**
     * Updates the entity with the id specified in the URL with the values sent by POST.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and perission to modify the requested entity.
     *
     * This action outputs a json with the entity data or with an array of errors.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', array('id' => $agent_id))
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', array($agent_id))
     * </code>
     */
    function POST_single(){
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->repo()->find($this->urlData['id']);

        if(!$entity)
            $app->pass();

        //Atribui a propriedade editada
        foreach($this->postData as $field=>$value){
            $entity->$field = $value;
        }

        if($errors = $entity->validationErrors){
            $this->errorJson($errors);
        }else{
            $entity->save(true);
            $this->json($entity);
        }
    }

    /**
     * Alias to DELETE_single
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'delete', array('id' => $agent_id))
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'delete', array($agent_id))
     * </code>
     */
    function GET_delete(){
        $this->DELETE_single();
    }

    /**
     * Delete the entity with the id specified in the URL.
     *
     * If the entity with the given id not exists, call $app->pass()
     *
     * This action requires authentication and permission to delete the requested entity.
     *
     * If the request is an ajax request, outputs an json with value true, otherwise redirects back to referer.
     *
     * <code>
     * // creates the url with explicit id
     * $url = $app->createUrl('agent', 'single', array('id' => $agent_id))
     *
     * // creates the url with implicit id
     * $url = $app->createUrl('agent', 'single', array($agent_id))
     * </code>
     */
    function DELETE_single(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->repo()->find($this->urlData['id']);

        if(!$entity)
            $app->pass();

        $entity->delete(true);

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }

    /*
     * Prints a json with the properties metadata of the entiry related to this controller.
     *
     * @see \MapasCulturais\Entity::getPropertiesMetadata()
     */
    public function GET_propertiesMetadata(){
        $class = $this->entityClassName;
        echo json_encode($class::getPropertiesMetadata());
    }



    // ========== API ============ //

    /**
     * Returns the ApiOutput Object.
     *
     * @return \MapasCulturais\ApiOutput
     */
    protected function getApiOutput(){
        $app = App::i();
        $type = key_exists('@type',$this->data) ? $this->data['@type'] : $app->config['app.defaultApiOutput'];
        $responder = $app->getRegisteredApiOutputById($type);

        if(!$responder){
            echo sprintf(App::txt("type %s is not registered."), $type);
            App::i()->stop();
        }else{
            return $responder;
        }
    }

    /*
     * Outputs an API Error Response
     *
     */
    protected function apiErrorResponse($error_message){
        $responder = $this->getApiOutput();
        $responder->outputError($error_message);
        App::i()->stop();
    }

    protected function apiResponse($data){
        if(is_array($data))
            $this->apiArrayResponse($data);
        else
            $this->apiItemResponse($data);
    }

    /**
     * Outputs an API Array Respose
     *
     * @param array $data
     * @param string $singular_name the singular name of the response itens
     * @param string $plural_name the plural name of the response itens
     */
    protected function apiArrayResponse(array $data, $singular_name = 'Entity', $plural_name = 'Entities'){
        $responder = $this->getApiOutput();
        $responder->outputArray($data, $singular_name, $plural_name);
        App::i()->stop();
    }

    /**
     * Ouputs an API Item Reponse
     *
     * @param mixed $data
     * @param string $singular_name the singular name of the response itens
     * @param string $plural_name the plural name of the response itens
     */
    protected function apiItemResponse($data, $singular_name = 'Entity', $plural_name = 'Entities'){
        $responder = $this->getApiOutput();
        $responder->outputItem($data, $singular_name, $plural_name);
        App::i()->stop();
    }

    /**
     * A generic API findOne method.
     *
     * This action finds one entity by the requested params and send the result to the API Responder.
     *
     * @see \MapasCulturais\ApiOutput::outputItem()
     */
    public function API_findOne(){
        $entity = $this->apiQuery($this->getData, array('findOne' => true));
        $this->apiItemResponse($entity);
    }

    public function API_find(){
        $data = $this->apiQuery($this->getData);
        $this->apiResponse($data);
    }

    public function API_describe(){
        $class = $this->entityClassName;
        $this->apiResponse($class::getPropertiesMetadata());
    }

    public function getApiCacheId($qdata, $options = array()){
        return $this->id . '::' . md5(serialize($qdata + array('__OPTIONS__' => $options)));
    }

    public function apiCacheExists($cache_id){
        $app = App::i();

        if(!$app->config['app.useApiCache'])
            return false;

        return $app->cache->contains($cache_id);
    }

    public function apiCacheResponse($cache_id){
        if($this->apiCacheExists($cache_id)){
            $app = App::i();
                $cache = $app->cache->fetch($cache_id);

            $app->contentType($cache['contentType']);
            echo $cache['output'];

            $app->stop();
            return true;
        }else{
            return false;
        }
    }

    public function apiQuery($qdata, $options = array()){
        $this->_apiFindParamList = array();
        $app = App::i();

        $findOne =  key_exists('findOne', $options) ? $options['findOne'] : false;

        $counting = key_exists('@count', $qdata);

        if($counting)
            unset($qdata['@count']);

        if(class_exists($this->entityClassName)){
            if(!$qdata && !$counting)
                $this->apiErrorResponse('no data');

            $class = $this->entityClassName;

            $entity_properties = array_keys($app->em->getClassMetadata($this->entityClassName)->fieldMappings);
            $entity_associations = $app->em->getClassMetadata($this->entityClassName)->associationMappings;

            $entity_metadata = array();
            $metadata_class = "";

            $meta_num = 0;
            $taxo_num = 0;
            $dql_joins = "";

            if($class::usesMetadata()){
                if(class_exists($class).'Meta'){
                    $metadata_class = $class.'Meta';
                    $dql_join_template = "\n\tLEFT JOIN $metadata_class {ALIAS} WITH {ALIAS}.owner = e AND {ALIAS}.key = '{KEY}'\n";
                }else{
                    $metadata_class = '\MapasCulturais\Entities\Metadata';
                    $dql_join_template = "\n\tLEFT JOIN $metadata_class {ALIAS} WITH {ALIAS}.objectType = '$class' AND {ALIAS}.objectId = e.id AND {ALIAS}.key = '{KEY}'\n";
                }

                foreach($app->getRegisteredMetadata($this->entityClassName) as $meta)
                    $entity_metadata[] = $meta->key;
            }

            if($class::usesTaxonomies()){
                $taxonomies = array();
                $taxonomies_ids = array();
                foreach($app->getRegisteredTaxonomies($class) as $obj){
                    $taxonomies[] = 'term:' . $obj->slug;
                    $taxonomies_ids['term:' . $obj->slug] = $obj->id;
                }

                $dql_join_term_template = "\n\tLEFT JOIN MapasCulturais\Entities\TermRelation {ALIAS_TR} WITH {ALIAS_TR}.objectType = '$class' AND {ALIAS_TR}.objectId = e.id LEFT JOIN {ALIAS_TR}.term {ALIAS_T} WITH {ALIAS_T}.taxonomy = {TAXO}\n";
            }

            $keys = array();

            $append_files_cb = function(){};

            $select = array('id');
            $order = null;
            $op = ' AND ';
            $offset = null;
            $limit = null;
            $page = null;
            $keyword = null;
            $permissions = null;

            $dqls = array();
            foreach($qdata as $key => $val){
                $val = trim($val);
                if(strtolower($key) == '@select'){
                    $select = explode(',', $val);
                    continue;
                }elseif(strtolower($key) == '@keyword'){
                    $keyword = $val;
                    continue;
                }elseif(strtolower($key) == '@permissions'){
                    $permissions = explode(',', $val);
                    continue;
                }elseif(strtolower($key) == '@order'){
                    $order = $val;
                    continue;
                }elseif(strtolower($key) == '@or'){
                    $op = ' OR ';
                    continue;
                }elseif(strtolower($key) == '@offset'){
                    $offset = $val;
                    continue;
                }elseif(strtolower($key) == '@page'){
                    $page = $val;
                    continue;
                }elseif(strtolower($key) == '@limit'){
                    $limit = $val;
                    continue;
                }elseif(strtolower($key) == '@type'){
                    continue;

                }elseif(strtolower($key) == '@files' && preg_match('#^\(([\w\., ]+)\)[ ]*(:[ ]*([\w, ]+))?#i', $val, $imatch)){

                    if($counting)
                        continue;
                    // example:
                    // @files=(avatar.smallAvatar,header.header):name,url

                    $cfg = array(
                        'files' => explode(',', $imatch[1]),
                        'props' => key_exists(3, $imatch) ? explode(',', $imatch[3]) : array('url')
                    );


                    $extract_data_cb = function($file, $ipath, $props){
                        $result = array();
                        if($ipath){
                            $path = explode('.', $ipath);
                            foreach($path as $transformation){
                                $file = $file->transform($transformation);
                            }
                        }
                        if($file)
                            foreach($props as $prop)
                                $result[$prop] = $file->$prop;

                        return $result;
                    };

                    $append_files_cb = function(&$result, $entity) use($cfg, $extract_data_cb){

                        $files = $entity->files;

                        foreach($cfg['files'] as $im){
                            $im = trim($im);

                            list($igroup, $ipath) = explode('.',$im, 2) + array(null,null);

                            if(!key_exists($igroup, $files))
                                continue;

                            if(is_array($files[$igroup])){
                                $result["@files:$im"] = array();
                                foreach($files[$igroup] as $file)
                                    $result["@files:$im"][] = $extract_data_cb($file,$ipath,$cfg['props']);
                            }else{
                                $result["@files:$im"] = $extract_data_cb($files[$igroup],$ipath,$cfg['props']);
                            }

                        }
                    };
                    continue;
                }

                if(key_exists($key, $entity_associations) && $entity_associations[$key]['isOwningSide']){
                    $keys[$key] = 'e.'.$key;

                }elseif(in_array($key, $entity_properties)){
                    $keys[$key] = 'e.'.$key;

                }elseif($class::usesTypes() && $key === 'type'){
                    $keys[$key] = 'e._type';

                }elseif($class::usesTaxonomies() && in_array($key, $taxonomies)){
                    $taxo_num++;
                    $tr_alias = "tr{$taxo_num}";
                    $t_alias = "t{$taxo_num}";
                    $taxonomy_id = $taxonomies_ids[$key];

                    $keys[$key] = "$t_alias.term";
                    $dql_joins .= str_replace('{ALIAS_TR}', $tr_alias, str_replace('{ALIAS_T}', $t_alias, str_replace('{TAXO}', $taxonomy_id, $dql_join_term_template)));

                }elseif($class::usesMetadata() && in_array($key, $entity_metadata)){
                    $meta_num++;
                    $meta_alias = "m{$meta_num}";
                    $keys[$key] = "$meta_alias.value";
                    $dql_joins .= str_replace('{ALIAS}', $meta_alias, str_replace('{KEY}', $key, $dql_join_template));

                }elseif($key[0] != '_' && $key != 'callback'){
                    $this->apiErrorResponse ("property $key does not exists");
                }else{
                    continue;
                }
                $dqls[] = $this->_API_find_parseParam($keys[$key], $val);
            }

            if($order){
                $new_order = array();
                foreach(explode(',',$order) as $prop){
                    $key = trim(preg_replace('#asc|desc#i', '', $prop));
                    if(key_exists($key, $keys)){
                        $new_order[] = str_ireplace($key, $keys[$key], $prop);
                    }elseif(in_array($key, $entity_properties)){
                        $new_order[] = str_ireplace($key, 'e.'.$key, $prop);

                    }elseif(in_array($key, $entity_metadata)){
                        $meta_num++;
                        $meta_alias = "m{$meta_num}";
                        $dql_joins .= str_replace('{ALIAS}', $meta_alias, str_replace('{KEY}', $key, $dql_join_template));

                        $new_order[] = str_replace($key, "$meta_alias.value", $prop);
                    }
                }
                $order = "ORDER BY " . implode(', ', $new_order);
            }

            $dql_where = implode($op, $dqls);
            
                

            if($metadata_class)
                $metadata_class = ", $metadata_class m";

            $dql_where = $dql_where ? "WHERE $dql_where" : "";

            if(in_array('status', $entity_properties)){
                $dql_where = $dql_where ? $dql_where . ' AND e.status > 0' : 'WHERE e.status > 0';
            }

            if($keyword){
                $repo = $this->repo();
                if($repo->usesKeyword()){
                    $ids = implode(',',$repo->getIdsByKeyword($keyword));
                    $dql_where .= $ids ? "AND e.id IN($ids)" : 'AND e.id < 0';
                }
            }

            $final_dql = "
                SELECT
                    e
                FROM
                    $class e
                    $dql_joins

                $dql_where

               $order";

            $result[] = "$final_dql";

            if($app->config['app.log.apiDql'])
                $app->log->debug("API DQL: ".$final_dql);
            
            $query = $app->em->createQuery($final_dql);
            
            // cache
            if($app->config['app.useApiCache']){
                $query->useResultCache(true, $app->config['app.apiCache.lifetime']);
            }
            
            $query->setParameters($this->_apiFindParamList);
            
            $processEntity = function($r) use($append_files_cb, $select){
                
                $entity = array();
                $append_files_cb($entity, $r);
                foreach($select as $i=> $prop){
                    $prop = trim($prop);
                    try{
                        if(strpos($prop, '.')){
                            
                            $props = explode('.',$prop);
                            $current_object = $r;
                            foreach($props as $p){
                                $current_object = $current_object->$p;
                                
                                if(!is_object($current_object))
                                    break;
                            }
                            
                            $prop_value = $current_object;
                        }else{
                            $prop_value = $r->$prop;
                        }
                        if(is_object($prop_value) && $prop_value instanceof \Doctrine\Common\Collections\Collection)
                            $prop_value = $prop_value->toArray();

                        if(strpos($prop, '.')){
                            $props = explode('.',$prop);
                            $carray =& $entity;
                            for($i = 0; $i < count($props) -1; $i++){
                                $p = $props[$i];
                                if(!isset($carray[$p]))
                                    $carray[$p] = array();
                                $carray =& $carray[$p];
                            }
                            $carray[array_pop($props)] = $prop_value;
                        }else{
                            $entity[$prop] = $prop_value;
                        }                        
                    }  catch (\Exception $e){ }
                }
                return $entity;
            };

            if($findOne){
                $query->setMaxResults(1);
                
                if($r = $query->getOneOrNullResult()){
                    
                    if($permissions){
                        foreach($permissions as $perm){
                            if(!$r->canUser(trim($perm))){
                                $r = null;
                                break;
                            }
                        }
                    }
                    
                    if($r)
                        $entity = $processEntity($r);
                }else{
                    $entity = null;
                }
                return $entity;
            }else{
                
                $rs = $query->getResult();
                
                
                $result = array();
                
                if(is_array($permissions)){
                    $rs = array_values(array_filter($rs, function($entity) use($permissions){
                        foreach($permissions as $perm)
                            if(!$entity->canUser($perm))
                                return false;
                            
                        return true;
                    }));
                }
                
                if($counting)
                    return count($rs);
                
                
                if($page && $limit){
                    $offset = (($page - 1) * $limit);
                    $rs = array_slice($rs, $offset, $limit);
                }
                $result = array_map(function($entity) use ($processEntity){
                    return $processEntity($entity);
                }, $rs);
                
                return $result;
            }
        }
    }


    private function _API_find_parseParam($key, $expression){
        if(is_string($expression) && !preg_match('#^[ ]*(!)?([a-z]+)[ ]*\((.*)\)$#i', $expression, $match)){
            $this->apiErrorResponse('invalid expression: '. ">>$expression<<");
        }else{
            $dql = '';

            $not = $match[1];
            $operator = strtoupper($match[2]);
            $value = $match[3];

            if($operator == 'OR' || $operator == 'AND'){
                $expressions = $this->_API_find_parseExpression($value);

                foreach($expressions as $expression){
                    $sub_dql = $this->_API_find_parseParam($key, $expression);
                    $dql .= $dql ? " $operator $sub_dql" : "($sub_dql";
                }
                if($dql) $dql .= ')';

            }elseif($operator == "IN"){
                $values = $this->_API_find_splitParam($value);

                $values = $this->_API_find_addValueToParamList($values);

                if(count($values) < 1)
                    $this->apiErrorResponse ('expression IN expects at last one value');

                $dql = $not ? "$key NOT IN (" : "$key IN (";
                $dql .= implode(', ', $values) . ')';


            }elseif($operator == "BET"){
                $values = $this->_API_find_splitParam($value);

                if(count($values) !== 2)
                    $this->apiErrorResponse ('expression BET expects 2 arguments');

                elseif($values[0][0] === '@' || $values[1][0] === '@')
                    $this->apiErrorResponse ('expression BET expects 2 string or integer arguments');

                $values = $this->_API_find_addValueToParamList($values);

                $dql = $not ?
                        "$key NOT BETWEEN {$values[0]} AND {$values[1]}" :
                        "$key BETWEEN {$values[0]} AND {$values[1]}";

            }elseif($operator == "LIKE"){
                $value = str_replace('*', '%', $value);
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key NOT LIKE $value" :
                        "$key LIKE $value";

            }elseif($operator == "ILIKE"){
                $value = str_replace('*', '%', $value);
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "lower($key) NOT LIKE lower($value)" :
                        "lower($key) LIKE lower($value)";

            }elseif($operator == "EQ"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key <> $value" :
                        "$key = $value";

            }elseif($operator == "GT"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key <= $value" :
                        "$key > $value";

            }elseif($operator == "GTE"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key < $value" :
                        "$key >= $value";

            }elseif($operator == "LT"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key >= $value" :
                        "$key < $value";

            }elseif($operator == "LTE"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key > $value" :
                        "$key <= $value";

            }elseif($operator == 'NULL'){
                $dql = $not ?
                        "($key IS NOT NULL OR $key <> '')" :
                        "($key IS NULL OR $key = '')";

            }elseif($operator == 'GEONEAR'){
                $values = $this->_API_find_splitParam($value);

                if(count($values) !== 3)
                    $this->apiErrorResponse ('expression GEONEAR expects 3 arguments: longitude, latitude and radius in meters');

                list($longitude, $latitude, $radius) = $this->_API_find_addValueToParamList($values);


                $dql = $not ?
                        "ST_DWithin($key, ST_MakePoint('$longitude','$latitude'), $radius) <> TRUE" :
                        "ST_DWithin($key, ST_MakePoint('$longitude','$latitude'), $radius) = TRUE";
            }

            /*
             * location=GEO_NEAR([long,lat]) //
             */
            return $dql;
        }
    }

    private function _API_find_addValueToParamList($value){
        if(is_array($value)){
            $result = array();
            foreach($value as $val)
                $result[] = $this->_API_find_addSigleValueToParamList($val);
        }else{
            $result = $this->_API_find_addSigleValueToParamList($value);
        }

        return $result;
    }

    private function _API_find_addSigleValueToParamList($value){
        if(is_numeric($value)){
            $result = $value;
        }else{
            $app = App::i();
            if(trim($value) === '@me'){
                $value = $app->user;
            }elseif(strpos($value,'@me.') === 0){
                $v = str_replace('@me.', '', $value);
                $value = $app->user->$v;
                //foreach($value as $p)
                    //$app->log->debug(">>>>>>>>>>> >>>>>>>>>>>>>> " . print_r(array($p->id, $p->name),true) . "<<<<<<<<< <<<<<<<<<<<<<");
            }elseif(trim($value) === '@profile'){
                $value = $app->user->profile ? $app->user->profile : null;

            }elseif(preg_match('#@(\w+)[ ]*:[ ]*(\d+)#i', trim($value), $matches)){
                $_repo = $app->repo($matches[1]);
                $_id = $matches[2];

                $value = ($_repo && $_id) ? $_repo->find($_id) : null;

            }elseif($value[0] == '@'){
                $value = null;
            }

            $uid = uniqid('v');
            $this->_apiFindParamList[$uid] = $value;
            $result = ':' . $uid;
        }
        return $result;
    }

    private function _API_find_splitParam($val){
        $result = explode("\n",str_replace('\\,', ',', preg_replace('#(^[ ]*|([^\\\]))\,#',"$1\n", $val)));

        if(count($result) === 1 && !$result[0]){
            return array();
        }else{
            $_result = array();
            foreach($result as $r)
                if($r)
                    $_result[] = $r;
            return $_result;
        }
    }

    private function _API_find_parseExpression($val){

        $open = false;
        $nopen = 0;
        $counter = 0;

        $results = array();
        $last_char = '';

        foreach(str_split($val) as $index => $char){
            $next_char = strlen($val) > $index + 1 ? $val[$index + 1] : '';

            if(!key_exists($counter, $results))
                $results[$counter] = '';

            if($char !== '\\' ||  $next_char === '(' || $next_char === ')')
                if($open || $char !== ',' || $last_char === '\\')
                    $results[$counter] .= $char;

            if($char === '(' && $last_char !== '\\'){
                $open = true;
                $nopen++;
            }

            if($char === ')' && $last_char !== '\\' && $open){
                $nopen--;
                if($nopen === 0){
                    $open = false;
                    $counter++;
                }
            }

            $last_char = $char;
        }

        return $results;
    }
}
