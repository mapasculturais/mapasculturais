<?php
namespace MapasCulturais;

use Respect\Validation\Validator as v;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * The base class for all entities used in MapasCulturais.
 *
 * @property-read array $validationErrors Entity properties and metadata validation errors.
 * @property-read array $propertiesMetadata Properties Metadata
 * @property-read \MapasCulturais\Controller $controller The controller with the class with the same name of this entity class in the parent namespace.
 * @property-read \MapasCulturais\Entities\User $ownerUser The User owner of this entity
 * @property-read string $hookClassPath
 *
 *
 * @hook **entity.new** - Executed when the __construct method of any entity is called.
 * @hook **entity({$entity_class}).new** - Executed when the __construct method of the $entity_class is called.
 *
 * @hook **entity.load** - Executed after any entity is loaded.
 * @hook **entity({$entity_class}).load** - Executed after an entity of class $entity_class is loaded.
 *
 *
 * @hook **entity.save:before** - Executed before any entity is inserted or updated.
 * @hook **entity({$entity_class}).save:before** - Executed before an entity of class $entity_class is inserted or updated.
 * @hook **entity.save:after**  - Executed after any entity is inserted or updated.
 * @hook **entity({$entity_class}).save:after** - Executed before an entity of class $entity_class is inserted or updated.
 *
 * @hook **entity.insert:before** - Executed before any entity is inserted.
 * @hook **entity({$entity_class}).insert:before** - Executed before an entity of class $entity_class is inserted.
 *
 * @hook **entity.insert:after** - Executed after any entity is inserted.
 * @hook **entity({$entity_class}).insert:after** - Executed after an entity of class $entity_class is inserted.
 *
 * @hook **entity.remove:before** - Executed before any entity is inserted.
 * @hook **entity({$entity_class}).remove:before** - Executed before an entity of class $entity_class is removed.
 *
 * @hook **entity.remove:after** - Executed after any entity is inserted.
 * @hook **entity({$entity_class}).remove:after** - Executed after an entity of class $entity_class is removed.
 *
 * @hook **entity.update:before** - Executed before any entity is updated.
 * @hook **entity({$entity_class}).update:before** - Executed before an entity of class $entity_class is updated.
 *
 * @hook **entity.update:after** - Executed after any entity is updated.
 * @hook **entity({$entity_class}).update:after** - Executed after an entity of class $entity_class is updated.
 *
 */
abstract class Entity implements \JsonSerializable{
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers;

    const STATUS_ENABLED = 1;
    const STATUS_DRAFT = 0;
    const STATUS_DISABLED = -9;
    const STATUS_TRASH = -10;
    const STATUS_ARCHIVED = -2;

    /**
     * array of validation definition
     * @var array
     */
    protected static $validations = [];

    protected $_validationErrors = [];

    private static $_jsonSerializeNestedObjects = [];
    
    /**
     * enable or disable the usage of magic getter hook to filter properties values
     */
    protected $__enableMagicGetterHook = false;

    /**
     * Creates the new empty entity object adding an empty point to properties of type 'point' and,
     * if the createTimestamp property exists, a DateTime object with the current date and time.
     *
     * @hook **entity(<<Entity>>).new** - Executed when the __construct method of the $entity_class is called.
     */
    public function __construct() {
        $app = App::i();

        foreach($app->em->getClassMetadata($this->getClassName())->associationMappings as $field => $conf){
            if($conf['type'] === 4){
                $this->$field = new \Doctrine\Common\Collections\ArrayCollection;
            }
        }

        foreach($app->em->getClassMetadata($this->getClassName())->fieldMappings as $field => $conf){

            if($conf['type'] == 'point'){
                $this->$field = new Types\GeoPoint(0,0);
            }
        }

        if(property_exists($this, 'createTimestamp'))
                $this->createTimestamp = new \DateTime;

        if($this->usesOwnerAgent() && !$app->user->is('guest')){
            $this->setOwner($app->user->profile);
        }

        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.new");

    }


    function __toString() {
        return $this->getClassName() . ':' . $this->id;
    }

    static function isPrivateEntity(){
        return false;
    }

    function refresh(){
        App::i()->em->refresh($this);
    }

    function equals($entity){
        return is_object($entity) && $entity instanceof Entity && $entity->getClassName() === $this->getClassName() && $entity->id === $this->id;
    }

    function isNew(){
        return App::i()->em->getUnitOfWork()->getEntityState($this) === \Doctrine\ORM\UnitOfWork::STATE_NEW;
    }

    function isArchived(){
        return $this->status === self::STATUS_ARCHIVED;
    }

    function simplify($properties = 'id,name'){
        $e = new \stdClass;

        $properties = is_string($properties) ? explode(',',$properties) : $properties;
        if(is_array($properties)){
            foreach($properties as $prop){
                switch ($prop){
                    case 'className':
                        $e->className = $this->getClassName();
                    break;

                    case 'files':
                        $e->files = [];

                        foreach ($this->files as $group => $files){

                            if(is_array($files)){
                                if(!isset($e->files[$group])){
                                    $e->files[$group] = [];
                                }

                                foreach($files as $f){
                                    $e->files[$group][] = $f->simplify('id,url,files');
                                }
                            }else if(is_object($files)){
                                $e->files[$group] = $files->simplify('id,url,files');
                            }else{
                                $e->files[$group] = null;
                            }
                        }
                    break;

                    case 'avatar':
                        if($this->usesAvatar()){
                            $e->avatar = [];
                            if($avatar = $this->avatar){
                                foreach($avatar->files as $transformation => $f){
                                    $e->avatar[$transformation] = $f->simplify('id,url');
                                }
                            }
                        }
                    break;

                    case 'terms':
                        if($this->usesTaxonomies())
                            $e->terms = $this->getTerms();

                    break;

                    default:
                        $e->$prop = $this->__get($prop);
                    break;
                }
            }
        }

        return $e;
    }

    function dump(){
        echo '<pre>';
        \Doctrine\Common\Util\Debug::dump($this);
        echo '</pre>';
    }

    static function getClassName(){
        return App::i()->em->getClassMetadata(get_called_class())->name;
    }

    /**
     * Returns the owner User of this entity
     *
     * @return \MapasCulturais\Entities\User
     */
    function getOwnerUser(){
        $app = App::i();

        if(isset($this->user)){
            return $this->user;
        }

        $owner = $this->owner;

        if(!$owner){
            return $app->user;
        }

        if(!($owner instanceof Entity)){
            return $app->user;
        }

        $user = $owner->getOwnerUser();

        return $user;
    }

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */

    static function getStatusesNames() {
        $app = App::i();
        $class = get_called_class();
        
        $statuses = $class::_getStatusesNames();
        
        // hook: entity(EntityName).statusesNames
        $hook_prefix = $class::getHookPrefix();
        $app->applyHook("{$hook_prefix}.statusesNames", [&$statuses]);

        return $statuses;
    }

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */
    protected static function _getStatusesNames() {
        return [
            self::STATUS_ARCHIVED => i::__('Arquivado'),
            self::STATUS_DISABLED => i::__('Desabilitado'),
            self::STATUS_DRAFT => i::__('Rascunho'),
            self::STATUS_ENABLED => i::__('Ativado'),
            self::STATUS_TRASH => i::__('Lixeira'),
        ];
    }

    /**
     * Retorna o nome do número de status informado, ou null se não existir
     * 
     * @return string|null
     */
    static function getStatusNameById($status) {
        $class = get_called_class();
        $statuses = $class::getStatusesNames();

        return $statuses[$status] ?? null;
    }

    /**
     * Set entity status
     * 
     * @param int $status 
     * @return void 
     */    
    function setStatus(int $status){
        $app = App::i();

        if($status != $this->status){           
            
            switch($status){
                case self::STATUS_ARCHIVED:
                    if($this->usesArchive()){
                        $this->checkPermission('archive');
                    }
                    break;
                
                case self::STATUS_TRASH:
                    if($this->usesSoftDelete()){
                        $this->checkPermission('remove');
                    }
                    break;

                case self::STATUS_DRAFT:
                    if ($this->usesSoftDelete() && $this->status == self::STATUS_TRASH) {
                        $this->checkPermission('undelete');
                    } else if ($this->usesArchive() && $this->status == self::STATUS_ARCHIVED) {
                        $this->checkPermission('unarchive');
                    }
                    break;

                case self::STATUS_ENABLED:
                    if ($this->usesSoftDelete() && $this->status == self::STATUS_TRASH) {
                        $this->checkPermission('undelete');
                    } else if ($this->usesArchive() && $this->status == self::STATUS_ARCHIVED) {
                        $this->checkPermission('unarchive');
                    }
                    break;
            }
        }
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.setStatus({$status})", [&$status]);

        $this->status = $status;
    }

    protected function fetchByStatus($collection, $status, $order = null){
        if(!is_object($collection) || !method_exists($collection, 'matching'))
                return [];

        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", $status));
        if(is_array($order)){
            $criteria = $criteria->orderBy($order);
        }
        return $collection->matching($criteria);
    }

    protected function genericPermissionVerification($user){
        
        if($user->is('guest'))
            return false;

        if($this->isUserAdmin($user)){
            return true;
        }

        if($this->getOwnerUser()->id == $user->id)
            return true;

        if($this->usesAgentRelation() && $this->userHasControl($user))
            return true;


        $class_parts = explode('\\', $this->getClassName());
        $permission = end($class_parts);

        $entity_user = $this->getOwnerUser();
        if($user->isAttorney("manage{$permission}", $entity_user)){
            return true;
        }

        return false;
    }

    protected function canUserView($user){
        if($this->status > 0){
            return true;
        }else{
            return $this->canUser('@control', $user);
        }
    }


    protected function canUserCreate($user){
        $result = $this->genericPermissionVerification($user);
        if($result && $this->usesOwnerAgent()){
            $owner = $this->getOwner();
            if(!$owner || $owner->status < 1){
                $result = false;
            }
        }

        if($result && $this->usesNested()){
            $parent = $this->getParent();
            if($parent && $parent->status < 1){
                $result = false;
            }
        }

        return $result;
    }


    protected function canUserModify($user) {
        return $this->genericPermissionVerification($user);
    }

    protected function canUserRemove($user){
        if($user->is('guest'))
            return false;

        if($this->isUserAdmin($user) || $this->getOwnerUser()->id == $user->id) {
            return true;

        }

        return false;
    }

    public function canUser($action, $userOrAgent = null){
        $app = App::i();
        if(!$app->isAccessControlEnabled()){
            return true;
        }

        if(is_null($userOrAgent)){
            $user = $app->user;
        } else if($userOrAgent instanceof UserInterface) {
            $user = $userOrAgent;
        } else {
            $user = $userOrAgent->getOwnerUser();
        }

        $result = false;

        if (!empty($user)) {
            $class_parts = explode('\\', $this->getClassName());
            $permission = end($class_parts);

            $entity_user = $this->getOwnerUser();
            if($user->isAttorney("{$action}{$permission}", $entity_user) || $user->isAttorney("manage{$permission}", $entity_user)){
                $result = true;
            } else {
                if(strtolower($action) === '@control' && $this->usesAgentRelation()) {
                    if($this->userHasControl($user)){
                        $result = true;
                    } else if($this->isUserAdmin($user)){
                        $result = true;
                    }
                }
    
                if(method_exists($this, 'canUser' . $action)){
                    $method = 'canUser' . $action;
                    $result = $this->$method($user);
                }elseif($action != '@control'){
                    $result = $this->genericPermissionVerification($user);
                }
            }

            $app->applyHookBoundTo($this, 'can(' . $this->getHookClassPath() . '.' . $action . ')', ['user' => $user, 'result' => &$result]);
            $app->applyHookBoundTo($this, $this->getHookPrefix() . '.canUser(' . $action . ')', ['user' => $user, 'result' => &$result]);

        }

        return $result;
    }
    
    /**
     * Wether a user can access the private files owned by this entity
     * 
     * Default is to 'view', as it is used to protect the files attached to the registrations
     * 
     * Other entities can extend this method and change the verification
     * 
     */ 
    protected function canUserViewPrivateFiles($user) {
        return $this->canUser('view', $user);
    }

    public function isUserAdmin(UserInterface $user, $role = 'admin'){
        $result = false;
        if($this->usesOriginSubsite()){
            if($user->is($role, $this->_subsiteId)){
                $result = true;
            }
        } else if($user->is($role)) {
            $result = true;
        }

        $app = App::i();

        $hook_prefix = $this->getHookPrefix();
        $app->applyHookBoundTo($this, "{$hook_prefix}.isUserAdmin({$role})", ['user' => $user, 'role' => $role, 'result' => &$result]);

        return $result;
    }

    public function checkPermission($action){
        if(!$this->canUser($action))
            throw new Exceptions\PermissionDenied(App::i()->user, $this, $action);
    }

    public static function getPropertiesLabels(){
        $result = [];
        foreach(self::getPropertiesMetadata() as $key => $metadata){
            if(isset($metadata['@select'])){
                $key = $metadata['@select'];
            }
            $result[$key] = $metadata['label'];
        }
        return $result;
    }

    public static function getPropertyLabel($property_name){
        $labels = self::getPropertiesLabels();

        return isset($labels[$property_name]) ? $labels[$property_name] : '';
    }

    public static function _getConfiguredPropertyLabel($property_name){
        $app = App::i();
        $label = '';

        $prop_labels = $app->config['app.entityPropertiesLabels'];

        if(isset($prop_labels [self::getClassName()][$property_name])){
            $label = $prop_labels[self::getClassName()][$property_name];
        }elseif(isset($prop_labels ['@default'][$property_name])){
            $label = $prop_labels ['@default'][$property_name];
        }

        return $label;
    }

    /**
     * Returns the metadata of this entity properties.
     *
     * The metadata is composed of a required key, type key (Doctrine Map type) and a length key.
     *
     * <code>
     * /**
     *  * Example
     *  array(
     *     'name' => array(
     *         'required' => true,
     *         'type' => 'string',
     *         'length' => 255
     *      )
     *      ...
     * </code>
     *
     * If the entity uses metadada the metadata of the metadata (metameta??) will be included in the result.
     *
     * @see \MapasCulturais\Definitions\Metadata::getMetadata()
     * @see \MapasCulturais\Traits\EntityMetadata
     *
     * @return array the metadata of this entity properties.
     */
    public static function getPropertiesMetadata($include_column_name = false){
        $__class = get_called_class();
        $class = $__class::getClassName();

        $class_metadata = App::i()->em->getClassMetadata($class)->fieldMappings;
        $class_relations = App::i()->em->getClassMetadata($class)->getAssociationMappings();

        $data_array = [];

        foreach ($class_metadata as $key => $value){
            $metadata = [
                'isMetadata' => false,
                'isEntityRelation' => false,

                'required'  => !$value['nullable'],
                'type' => $value['type'],
                'length' => $value['length'],
                'label' => $class::_getConfiguredPropertyLabel($key),
            ];

            if ($include_column_name && isset($value['columnName'])) {
                $metadata['columnName'] = $value['columnName'];
            }

            if($key[0] == '_'){
                $prop = substr($key, 1);
                if(method_exists($class, 'get' . $prop)){
                     $metadata['@select'] = $prop;
                }else{
                    continue;
                }
            }
            $data_array[$key] = $metadata;
        }

        foreach ($class_relations as $key => $value){
            $data_array[$key] = [
                'isMetadata' => false,
                'isEntityRelation' => true,

                'targetEntity' => str_replace('MapasCulturais\Entities\\','',$value['targetEntity']),
                'isOwningSide' => $value['isOwningSide'],
                'label' => $class::_getConfiguredPropertyLabel($key)
            ];
        }

        if($class::usesMetadata()){
            $data_array = $data_array + $class::getMetadataMetadata();
        }

        if(isset($data_array['location']) && isset($data_array['publicLocation'])){
            $data_array['location']['private'] = function(){ return (bool) ! $this->publicLocation; };
        }


        return $data_array;
    }

    static public function getValidations(){
        return [];
    }

    public function isPropertyRequired($entity,$property) {
        $app = App::i();
        $return = false;

        $__class = get_called_class();
        $class = $__class::getClassName();

        $metadata = $class::getPropertiesMetadata();
        if(array_key_exists($property,$metadata) && array_key_exists('required',$metadata[$property])) {
            $return = $metadata[$property]['required'];
        }

        $v = $class::getValidations();
        if(!$return && array_key_exists($property,$v) && array_key_exists('required',$v[$property])) {
            $return = true;
        }

        return $return;
    }

    /**
     * Returns this entity as an array.
     *
     * @return \MapasCulturais\Entity
     */
    public function getEntity(){
        $data = [];
        foreach ($this as $key => $value){
            if($key[0] == '_')
                continue;
            $data[$key] = $value;
        }
        return $data;
    }

    public function getSingleUrl(){
        return App::i()->createUrl($this->controllerId, 'single', [$this->id]);
    }

    public function getEditUrl(){
        return App::i()->createUrl($this->controllerId, 'edit', [$this->id]);
    }

    public function getDeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'delete', [$this->id]);
    }

    /**
     * Returns the controller with the same name in the parent namespace if it exists.
     *
     * @return \MapasCulturais\Controller The controller
     */
    public function getController(){
        return App::i()->getControllerByEntity($this);
    }

    /**
     * Returns the controller with the same name in the parent namespace if it exists.
     *
     * @return \MapasCulturais\Controller The controller
     */
    public function getControllerId(){
        return App::i()->getControllerIdByEntity($this);
    }


    /**
     * Return the class path to be used in hook names.
     *
     * If the Entity is in the MapasCulturais\Entities namespace, the namespace will be removed.
     *
     * @example for the entity MapasCulturais\Entities\Agent, this method returns "Agent".
     * @example for the entity Foo\Boo\SomeEntity, this method returns "Foo.Boo.SomeEntity".
     *
     * @return string
     */
    public static function getHookClassPath($class = null){
        if(!$class){
            $called_class = get_called_class();
            $class = $called_class::getClassName();
        }
        return preg_replace('#^MapasCulturais\.Entities\.#','',str_replace('\\','.',$class));
    }

    public static function getHookPrefix($class = null) {
        $hook_class_path = self::getHookClassPath($class);
        return "entity({$hook_class_path})";
    }

    public function getEntityType(){
        return str_replace('MapasCulturais\Entities\\','',$this->getClassName());
    }

    public static function getEntityTypeLabel($plural = false) {}

    function getEntityState() {
        return App::i()->em->getUnitOfWork()->getEntityState($this);
    }

    /**
     * Persist the Entity optionally flushing
     *
     * @param boolean $flush Flushes to the Database
     */
    public function save($flush = false){
        $app = App::i();

        $requests = [];
        
        try {
            $hook_prefix = $this->getHookPrefix();
            $app->applyHookBoundTo($this, "{$hook_prefix}.save:requests", [&$requests]);
            $app->applyHookBoundTo($this, "entity({$this}).save:requests", [&$requests]);
        } catch (Exceptions\WorkflowRequestTransport $e) {
            $requests[] = $e->request;
        }
        
        if (method_exists($this, '_saveNested')) {
           try {
                $this->_saveNested();
            } catch (Exceptions\WorkflowRequestTransport $e) {
                $requests[] = $e->request;
            }
        }

        if (method_exists($this, '_saveOwnerAgent')) {
            try {
               $this->_saveOwnerAgent();                
            } catch (Exceptions\WorkflowRequestTransport $e) {
                $requests[] = $e->request;
            }
        }
        try{
            if($this->isNew()){
                $this->checkPermission('create');
                $is_new = true;

                if($this->usesOriginSubsite() && $app->getCurrentSubsiteId()){
                    $subsite = $app->repo('Subsite')->find($app->getCurrentSubsiteId());
                    $this->setSubsite($subsite);
                }

            }else{
                $this->checkPermission('modify');
                $is_new = false;

            }
            $app->em->persist($this);

            if($flush){
                $app->em->flush();
            }

            if($this->usesMetadata()){
                $this->saveMetadata();
                if($flush){
                    $app->em->flush();
                }
            }

            if($this->usesTaxonomies()){
                $this->saveTerms();
                if($flush){
                    $app->em->flush();
                }
            }
            $this->refresh();

            if($this->usesRevision()) {
                if($is_new){
                    $this->_newCreatedRevision();
                } else {
                    $this->_newModifiedRevision();
                }
            }

        }catch(Exceptions\PermissionDenied $e){
            if(!$requests)
                throw $e;
        }
        
        if($requests){
            foreach($requests as $request)
                $request->save($flush);
            $e = new Exceptions\WorkflowRequest($requests);
            throw $e;
        }

        if ($is_new) {
            $app->applyHookBoundTo($this, "{$hook_prefix}.insert:finish");
        } else {
            $app->applyHookBoundTo($this, "{$hook_prefix}.update:finish");
        }

        $app->applyHookBoundTo($this, "{$hook_prefix}.save:finish");
        
    }

    /**
     * Remove this entity.
     *
     * @param boolean $flush Flushes to the database
     */
    public function delete($flush = false){
        $this->checkPermission('remove');

        App::i()->em->remove($this);
        if($flush)
            App::i()->em->flush();
    }

    private function _isPropertySerializable($val, array $allowed_classes){
        if(is_array($val)){
            $nval = [];
            foreach($val as $k => $v){
                try{
                    $nval[$k] = $this->_isPropertySerializable($v, $allowed_classes);
                }  catch (\Exception $e) {}
            }
            $val = $nval;
        }elseif(is_object($val) && !is_subclass_of($val, __CLASS__) && !in_array(\get_class($val), $allowed_classes)){
            throw new \Exception();
        }elseif(is_object($val)){

            if(in_array($val, Entity::$_jsonSerializeNestedObjects))
                throw new \Exception();
        }

        return $val;
    }

    /**
     * Retorna um array com a estrutura que será serializada pela função json_encode
     * 
     * @return array
     */
    public function jsonSerialize() {
        $result = [];
        $allowed_classes = [
            'DateTime',
            'MapasCulturais\Types\GeoPoint',
            'stdClass'
        ];
        $_uid = uniqid();

        Entity::$_jsonSerializeNestedObjects[$_uid] = $this;

        foreach($this as $prop => $val){
            if($prop[0] == '_')
                continue;

            if($prop[0] == '_' && method_exists($this, 'get' . substr($prop, 1)))
                $prop = substr ($prop, 1);

            try{
                $val = $this->__get($prop);
                $val = $this->_isPropertySerializable($val, $allowed_classes);
                $result[$prop] = $val;
            }  catch (\Exception $e){}
        }

        if($this->usesMetadata()){
            $registered_metadata = $this->getRegisteredMetadata();
            foreach($registered_metadata as $def){
                $meta_key = $def->key;
                $meta_value = $this->$meta_key;
                if ($meta_value instanceof Entity) {
                    $result[$meta_key] = $meta_value->id;
                } else {
                    $result[$meta_key] = $meta_value;
                }
            }
        }

        if ($this->usesTypes()) {
            $result['type'] = $this->type->id ?? null;
        }

        if ($this->usesTaxonomies()) {
            $result['terms'] = $this->terms;
        }

        if($controller_id = $this->getControllerId()){
            $result['controllerId'] = $controller_id;
            $result['deleteUrl'] = $this->getDeleteUrl();
            $result['editUrl'] = $this->getEditUrl();
            $result['singleUrl'] = $this->getSingleUrl();
        }
        
        unset(Entity::$_jsonSerializeNestedObjects[$_uid]);

        return $result;
    }


    /**
     * Validate that this property is unique in database
     *
     * @param string $property_name
     *
     * @return boolean
     */
    protected function validateUniquePropertyValue($property_name){
        $class = get_called_class();
        $dql = "SELECT COUNT(e.$property_name) FROM $class e WHERE e.$property_name = :val";
        $params = ['val' => $this->$property_name];
        if($this->id){
            $dql .= ' AND e.id != :id';
            $params['id'] = $this->id;
        }

        $ok = App::i()->em->createQuery($dql)->setParameters($params)->getSingleScalarResult() == 0;
        return $ok;
    }

    /**
     * Validates the entity properties and returns the errors messages.
     *
     * The entity errors messages uses php gettext.
     *
     * If this entity uses metadata, this method will call getMetadataValidationErrors() method
     *
     * <code>
     * /**
     *  * Example of the array of errors:
     * array(
     *     'name' => [ 'The name is required' ],
     *     'email' => [ 'The first error message', 'The second error message' ]
     * )
     * </code>
     *
     * @see \MapasCulturais\Traits\Metadata::getMetadataValidationErrors() Metadata Validation Errors
     *
     * @return array
     */
    public function getValidationErrors(){
        $app = App::i();

        $errors = $this->_validationErrors;
        $class = get_called_class();

        if(!method_exists($class, 'getValidations')) {
            return $errors;
        }

        $properties_validations = $class::getValidations();

        $tags = ['style','script','embed','object','iframe','img','link'];
        $validation = implode(',', array_map(function($tag){
            return "v::contains('<{$tag}')";
        }, $tags));
        $htmltags_validation = "v::noneOf($validation)";

        foreach($this->getPropertiesMetadata() as $prop => $metadata){
            if(!$metadata['isEntityRelation']){
                if(!isset($properties_validations[$prop])){
                    $properties_validations[$prop] = [];
                }

                $properties_validations[$prop]['htmlentities'] = i::__('Não é permitido a inclusão de scripts');
            }
        }

        $hook_prefix = $this->getHookPrefix();
        
        $app->applyHookBoundTo($this, "{$hook_prefix}.validations", [&$properties_validations]);

        foreach($properties_validations as $property => $validations){

            if(!$this->$property && !key_exists('required', $validations))
                continue;



            foreach($validations as $validation => $error_message){
                $validation = trim($validation);

                $ok = true;

                if($validation == 'htmlentities'){
                    if(!is_string($this->$property)){
                        continue;
                    }
                    
                    $validation = $htmltags_validation;
                }

                if($validation == 'required'){
                    if (is_string($this->$property)) {
                        $ok = trim($this->$property) !== '';
                    } else {
                        $ok = (bool) $this->$property || $this->$property === 0;
                    }

                }elseif($validation == 'unique'){
                    $ok = $this->validateUniquePropertyValue($property);

                }elseif(strpos($validation,'v::') === 0){
                    $validation = str_replace('v::', 'MapasCulturais\Validator::', $validation);
                    eval('$ok = ' . $validation . '->validate($this->' . $property . ');');
                }else{
                    $value = $this->$property;
                    eval('$ok = ' . $validation . ';');
                }
                if(!$ok){
                    if (!key_exists($property, $errors))
                        $errors[$property] = [];

                    $errors[$property][] = $error_message;

                }
            }
        }

        if($this->usesTypes() && !$this->_type)
            $errors['type'] = [\MapasCulturais\i::__('O Tipo é obrigatório')];
        elseif($this->usesTypes() && !$this->validateType())
            $errors['type'] = [\MapasCulturais\i::__('Tipo inválido')];

        if($this->usesMetadata())
            $errors = $errors + $this->getMetadataValidationErrors();

        if($this->usesTaxonomies())
            $errors = $errors + $this->getTaxonomiesValidationErrors();

        return $errors;
    }


    /**
     * Returns the Doctrine Repository for this entity.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return App::i()->repo($this->getClassName());
    }

    /**
     * Executed before the entity is inserted.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#prepersist
     *
     * @hook **entity.insert:before**
     * @hook **entity({$entity_class}).insert:before**
     * @hook **entity.save:before**
     * @hook **entity({$entity_class}).save:before**
     */
    public function prePersist($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.insert:before", $args);
        $app->applyHookBoundTo($this, "{$hook_prefix}.save:before", $args);


        if($this->usesPermissionCache() && !$this->__skipQueuingPCacheRecreation){
            if($this->usesAgentRelation()){
                $this->deleteUsersWithControlCache();
            }
            $this->enqueueToPCacheRecreation();
        }
    }

    /**
     * Executed after the entity is inserted.
     *
     * If the entity uses Metadata, saves the entity metadatas.
     *
     * If the entity uses Taxonomies, saves the terms.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     * @see \MapasCulturais\Traits\EntityMetadata::saveMetadata()
     * @see \MapasCulturais\Traits\EntityTaxonomies::saveTerms()
     *
     * @hook **entity.insert:after**
     * @hook **entity({$entity_class}).insert:after**
     * @hook **entity.save:after**
     * @hook **entity({$entity_class}).save:after**
     */
    public function postPersist($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $repo = $app->repo($this->className);
        if($repo->usesCache()){
            $repo->deleteEntityCache($this->id);
        }

        $app->applyHookBoundTo($this, "{$hook_prefix}.insert:after", $args);
        $app->applyHookBoundTo($this, "{$hook_prefix}.save:after", $args);
    }

    /**
     * Executed before the entity is removed.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#preremove
     *
     * @hook **entity.remove:before**
     * @hook **entity({$entity_class}).remove:before**
     */
    public function preRemove($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.remove:before", $args);


        if($this->usesPermissionCache() && !$this->__skipQueuingPCacheRecreation){
            if($this->usesAgentRelation()){
                $this->deleteUsersWithControlCache();
            }
            $this->enqueueToPCacheRecreation();
        }
    }

    /**
     * Executed after the entity is removed.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     *
     * @hook **entity.remove:after**
     * @hook **entity({$entity_class}).remove:after**
     */
    public function postRemove($args = null){
        $app = App::i();
        $repo = $app->repo($this->className);

        if ($repo->usesCache()) {
            $repo->deleteEntityCache($this->id);
        }
        
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.remove:after", $args);

        if($this->usesRevision()) {
            //$this->_newDeletedRevision();
        }

        if($this->usesPermissionCache()){
            $this->deletePermissionsCache();
        }

        if($this->usesRevision()) {
            //$this->_newDeletedRevision();
        }
    }

    /**
     * Executed before the entity is updated.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#preupdate
     *
     * @hook **entity.update:before**
     * @hook **entity({$entity_class}).update:before**
     * @hook **entity.save:before**
     * @hook **entity({$entity_class}).save:before**
     */
    public function preUpdate($args = null){
        $app = App::i();
        
        $hook_prefix = $this->getHookPrefix();
        $app->applyHookBoundTo($this, "{$hook_prefix}.update:before", $args);
        $app->applyHookBoundTo($this, "{$hook_prefix}.save:before", $args);

        if (property_exists($this, 'updateTimestamp')) {
            $this->updateTimestamp = new \DateTime;
            /* @TODO: verificar o pq do código abaixo:
            if($this->sentNotification){
                $entity = $this;
                $nid = $this->sentNotification;
                $app->hook('entity(' . $hook_class_path . ').update:after', function() use($app, $entity, $nid) {
                    if($this->equals($entity) && $app->user->equals($this->getOwnerUser())){
                        // $app->log->debug("notification id: $nid");
                        $notification = $app->repo('Notification')->find($nid);
                        if($notification){
                            $notification->delete();
                            $this->sentNotification = 0;

                            // as duas linhas abaixo não devem ficar aqui:
                            // $this->save();
                            // $app->em->flush();
                        }
                    }
                });
            }
             */
        }

        if($this->usesPermissionCache() && !$this->__skipQueuingPCacheRecreation){
            if($this->usesAgentRelation()){
                $this->deleteUsersWithControlCache();
            }
            $this->enqueueToPCacheRecreation();
        }
    }

    /**
     * Executed after the entity is updated.
     *
     * If the entity uses Metadata, saves the entity metadatas.
     *
     * If the entity uses Taxonomies, saves the terms.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postupdate-postremove-postpersist
     * @see \MapasCulturais\Traits\EntityMetadata::saveMetadata()
     * @see \MapasCulturais\Traits\EntityTaxonomies::saveTerms()
     *
     * @hook **entity.update:after**
     * @hook **entity({$entity_class}).update:after**
     * @hook **entity.save:after**
     * @hook **entity({$entity_class}).save:after**
     *
     * @ORM\PostUpdate
     */
    public function postUpdate($args = null){
        $app = App::i();
        $hook_prefix = $this->getHookPrefix();

        $app->applyHookBoundTo($this, "{$hook_prefix}.update:after", $args);
        $app->applyHookBoundTo($this, "{$hook_prefix}.save:after", $args);
    }
}
