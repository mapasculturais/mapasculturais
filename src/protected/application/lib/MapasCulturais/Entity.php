<?php
namespace MapasCulturais;

use Respect\Validation\Validator as v;

/**
 * The base class for all entities used in MapasCulturais.
 *
 * @property-read array $validationErrors Entity properties and metadata validation errors.
 * @property-read array $propertiesMetadata Properties Metadata
 * @property-read \MapasCulturais\Controller $controller The controller with the class with the same name of this entity class in the parent namespace.
 * @property-read \MapasCulturais\Entities\User $ownerUser The User owner of this entity
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
        Traits\MagicSetter;

    const STATUS_ENABLED = 1;
    const STATUS_DRAFT = 0;
    const STATUS_DISABLED = -9;
    const STATUS_TRASH = -10;

    /**
     * array of validation definition
     * @var array
     */
    protected static $validations = array();

    protected $_validationErrors = array();

    private static $_jsonSerializeNestedObjects = array();

    /**
     * Creates the new empty entity object adding an empty point to properties of type 'point' and,
     * if the createTimestamp property exists, a DateTime object with the current date and time.
     *
     * @hook **entity.new** - Executed when the __construct method of any entity is called.
     * @hook **entity({$entity_class}).new** - Executed when the __construct method of the $entity_class is called.
     */
    public function __construct() {
        $app = App::i();

        foreach($app->em->getClassMetadata($this->getClassName())->fieldMappings as $field => $conf){
            if($conf['type'] == 'point'){
                $this->$field = new Types\GeoPoint(0,0);
            }
        }
        if(property_exists($this, 'createTimestamp'))
                $this->createTimestamp = new \DateTime;

        if($this->usesTaxonomies())
            $this->populateTermsProperty();

        $hook_class_path = $this->getHookClassPath();

        App::i()->applyHookBoundTo($this, 'entity.new');
        App::i()->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').new');

    }

    function __toString() {
        return $this->getClassName() . ':' . $this->id;
    }

    /**
     * Magic Call
     *
     * Returns false to all methods that starts with uses (traits must define a method like usesTraitName() that returns true)
     *
     * @param string $name the name of the method that was called.
     * @param array $arguments the params passed to the method
     * @return mixed
     */
    public function __call($name, $arguments) {
        if(method_exists($this, $name))
            return $this->$name();
        if(substr($name, 0, 4) === 'uses')
            return false;
    }

    /**
     * Magic Static Call
     *
     * Returns false to all methods that starts with uses (traits must define a method like usesTraitName() that returns true)
     *
     * @param string $name the name of the method that was called.
     * @param array $arguments the params passed to the method
     * @return mixed
     */
    static public function __callStatic($name, $arguments){
        $class = get_called_class();

        if(method_exists($class, $name))
            return $class::$name();
        if(substr($name, 0, 4) === 'uses')
            return false;
    }

    function dump(){
        echo '<pre>';
        \Doctrine\Common\Util\Debug::dump($this);
        echo '</pre>';
    }

    static function getClassName(){
        return App::i()->em->getClassMetadata(get_called_class())->name;
    }

    function getOwnerUser(){
        $app = App::i();

        if(!$this->owner)
            return $app->user;

        $cache_id = "{$this}:ownerUserId";

        if($app->cache->contains($cache_id))
            return $app->repo('User')->find($app->cache->fetch($cache_id));

        $owner = $this->owner;
        if(method_exists($owner, '__load') && !$owner->id)
            $owner->__load();

        $user = $owner->getOwnerUser();
        if(method_exists($user, '__load') && !$user->id)
            $user->__load();

        $app->cache->save($cache_id, $user->id);

        return $user;
    }

    protected function genericPermissionVerification($user){
        if(is_null($user))
            return false;

        if($user->is('admin'))
            return true;

        if($this->getOwnerUser()->id == $user->id)
            return true;

        if($this->usesAgentRelation()){
            $users_with_control = $this->getUsersWithControl();

            foreach($users_with_control as $u){
                if($user->id == $u->id)
                    return true;
            }
        }
        return false;
    }

    protected function canUserRemove($user){
        if($user && $user->is('admin') || $this->getOwnerUser()->id == $user->id)
            return true;

        return false;
    }

    public function canUser($action, $userOrAgent = null){
        if(App::i()->isRunningUpdates())
            return true;

        if(App::i()->repo('User')->isCreating())
            return true;

        $user = is_null($userOrAgent) ? App::i()->user : $userOrAgent->getOwnerUser();

        if($user && $user->is('superAdmin'))
            return true;

        if(is_null($user))
            $user = new GuestUser;

        if(method_exists($this, 'canUser' . $action)){
//            \MapasCulturais\App::i()->log->info(get_called_class() . ': '.__METHOD__ . "( $action ) --> EXISTS");
            $method = 'canUser' . $action;
            return $this->$method($user);
        }else{
//            \MapasCulturais\App::i()->log->info(get_called_class() . ': '.__METHOD__ . "( $action ) --> ELSE");
            return $this->genericPermissionVerification($user);
        }
    }

    public function checkPermission($action){
        if(!$this->canUser($action))
            throw new Exceptions\PermissionDenied(App::i()->user, $this, $action);
    }

    /**
     * Returns the metadata of this entity properties.
     *
     * The metadata is composed of a required key, type key (Doctrine Map type) and a length key.
     *
     * <code>
     * /**
     *  * Example
     *  {@*}
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
    public static function getPropertiesMetadata(){
        $class_metadata = App::i()->em->getClassMetadata(get_called_class())->fieldMappings;
        $data_array = array();
        foreach ($class_metadata as $key => $value){
            if($key[0] == '_')
                continue;

            $data_array[$key] = array(
                'required'  => !$value['nullable'],
                'type' => $value['type'],
                'length' => $value['length']
            );
        }

        $class = get_called_class();
        if($class::usesMetadata()){
            $data_array = $data_array + self::getMetadataMetadata();
        }
        return $data_array;
    }

    /**
     * Returns this entity as an array.
     *
     * @return \MapasCulturais\Entity
     */
    public function getEntity(){
        $data = array();
        foreach ($this as $key => $value){
            if($key[0] == '_')
                continue;
            $data[$key] = $value;
        }
        return $data;
    }

    public function getSingleUrl(){
        return App::i()->createUrl($this->controllerId, 'single', array($this->id));
    }

    public function getEditUrl(){
        return App::i()->createUrl($this->controllerId, 'edit', array($this->id));
    }

    public function getDeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'delete', array($this->id));
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


    public function getTitle(){
        return App::i()->getTitle($this);
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
    public function getHookClassPath($class = null){
        if(!$class)
            $class = $this->getClassName();

        return preg_replace('#^MapasCulturais\.Entities\.#','',str_replace('\\','.',$class));
    }

    public function getEntityType(){
		return App::i()->txt(str_replace('MapasCulturais\Entities\\','',$this->getClassName()));
	}

    /**
     * Persist the Entity optionally flushing
     *
     * @param boolean $flush Flushes to the Database
     */
    public function save($flush = false){
        App::i()->em->persist($this);

        if($this->usesMetadata())
            $this->saveMetadata();

        if($this->usesTaxonomies())
            $this->saveTerms();

        if($flush)
            App::i()->em->flush();
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
            $nval = array();
            foreach($val as $k => $v){
                try{
                    $nval[$k] = $this->_isPropertySerializable($v, $allowed_classes);
                }  catch (\Exception $e) {}
            }
            $val = $nval;
        }elseif(is_object($val) && !is_subclass_of($val, __CLASS__) && !in_array($val, $allowed_classes)){
            throw new \Exception();
        }elseif(is_object($val)){
            if(in_array($val, Entity::$_jsonSerializeNestedObjects))
                throw new \Exception();

        }
        return $val;
    }

    /**
     *
     * @return type
     */
    public function jsonSerialize() {
        $result = array();
        $allowed_classes = array(
            'DateTime',
            'MapasCulturais\Types\GeoPoint',
            'stdClass'
        );
        $_uid = uniqid();

        Entity::$_jsonSerializeNestedObjects[$_uid] = $this;

        foreach($this as $prop => $val){
            if($prop[0] == '_' && method_exists($this, 'get' . substr($prop, 1)))
                $prop = substr ($prop, 1);

            try{
                $val = $this->$prop;
                $val = $this->_isPropertySerializable($val, $allowed_classes);
                $result[$prop] = $val;
            }  catch (\Exception $e){}
        }

        if($this->usesMetadata()){
            foreach($this->metadata as $meta_key => $meta_value)
                $result[$meta_key] = $meta_value;
        }

        $result['deleteUrl'] = $this->getDeleteUrl();

        $result['editUrl'] = $this->getEditUrl();

        $result['singleUrl'] = $this->getSingleUrl();

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
        $params = array('val' => $this->$property_name);
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
     *  {@*}
     * array(
     *     'name' => array( 'The name is required' ),
     *     'email' => array( 'The first error message', 'The second error message' )
     * )
     * </code>
     *
     * @see \MapasCulturais\App::txt() The MapasCulturais GetText method
     * @see \MapasCulturais\Traits\Metadata::getMetadataValidationErrors() Metadata Validation Errors
     *
     * @return array
     */
    public function getValidationErrors(){
        $errors = $this->_validationErrors;
        $class = get_called_class();
        foreach($class::$validations as $property => $validations){
            if(!$this->$property && !key_exists('required', $validations))
                continue;

            foreach($validations as $validation => $error_message){
                $validation = trim($validation);

                $ok = true;

                if($validation == 'required'){
                    $ok = (bool) $this->$property;

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
                        $errors[$property] = array();

                    $errors[$property][] = App::txt($error_message);
                }
            }
        }

        if($this->usesTypes() && !$this->_type)
            $errors['type'] = array(App::txt('The type is required'));
        elseif($this->usesTypes() && !$this->validateType())
            $errors['type'] = array(App::txt('Invalid type'));

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
        return App::i()->repo(get_called_class());
    }

    /**
     * Executed after the entity is loaded.
     *
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#lifecycle-events
     * @see http://docs.doctrine-project.org/en/latest/reference/events.html#postload
     *
     * @hook **entity.load**
     * @hook **entity({$entity_class}).load**
     */
    public function postLoad($args = null){
        $hook_class_path = $this->getHookClassPath();
        App::i()->applyHookBoundTo($this, 'entity.load', $args);
        App::i()->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').load', $args);
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
        $this->checkPermission('create');

        $hook_class_path = $this->getHookClassPath();
        App::i()->applyHookBoundTo($this, 'entity.insert:before', $args);
        App::i()->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').insert:before', $args);

        App::i()->applyHookBoundTo($this, 'entity.save:before', $args);
        App::i()->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').save:before', $args);
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
        $hook_class_path = $this->getHookClassPath();
        $app = App::i();

        $app->applyHookBoundTo($this, 'entity.insert:after', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').insert:after', $args);
        $app->applyHookBoundTo($this, 'entity.save:after', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').save:after', $args);
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
        $hook_class_path = $this->getHookClassPath();

        $app = App::i();

        if($this->usesFiles()){
            foreach($this->files as $files){
                if(is_array($files)){
                    foreach($files as $f){
                        $f->delete();
                    }
                }elseif(is_object($files)){
                    $files->delete();
                }
            }
        }

        if($this->usesMetadata()){
            foreach($this->getMetadata(null,true) as $m)
                $m->delete();
        }

        if($this->usesTaxonomies()){
            $relations = $app->repo('TermRelation')->findBy(array('objectType' => $this->getClassName(), 'objectId' => $this->id));
            foreach($relations as $tr)
                $tr->delete();
        }

        $app->applyHookBoundTo($this, 'entity.remove:before', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').remove:before', $args);
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
        $hook_class_path = $this->getHookClassPath();
        $app = App::i();

        $app->applyHookBoundTo($this, 'entity.remove:after', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').remove:after', $args);
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
        $this->checkPermission('modify');

        $app = App::i();

        $hook_class_path = $this->getHookClassPath();
        $app->applyHookBoundTo($this, 'entity.update:before', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').update:before', $args);
        $app->applyHookBoundTo($this, 'entity.save:before', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').save:before', $args);


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
        $hook_class_path = $this->getHookClassPath();
        $app = App::i();

        $app->applyHookBoundTo($this, 'entity.update:after', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').update:after', $args);
        $app->applyHookBoundTo($this, 'entity.save:after', $args);
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').save:after', $args);

    }

}
