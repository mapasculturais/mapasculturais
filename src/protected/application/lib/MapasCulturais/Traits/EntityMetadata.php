<?php
namespace MapasCulturais\Traits;
use \MapasCulturais\App;

/**
 * Defines that the entity has metadata.
 *
 * If there is a class with the same of the entity class appended with Metadata this trait will use it,
 * otherwise this trait will use the \MapasCulturais\Entities\Metadata entity.
 * For example: If the class name of the entity is \Namespace\SomeEntity and there is a \Namespace\SomeEntityMetadata class,
 * this trait will use the \Namespace\SomeEntityMetadata as the metadata entity of the \Namespace\SomeEntity class
 *
 * This trait will make all metadata behave like real properties of the entity. So to set a metadata value you just need to
 * set the entity property with the name of the metadata key. Array or object values will be serializated in database.
 *
 * To save changed metadata you can save the entity $entity->save() or just save the metadata: $entity->saveMetadata()
 *
 * @example To set the metadata with the key 'site' you can do $entity->site = 'http://foo.bar/'; or $entity->setMetadata('site', 'http://foo.bar/');
 * @example To print the metadata with the key 'site' you do: echo $entity->site;
 * @example you can access all metadatas in the metadata property of the entity: foreach($entity->metadata as $metakey => $meta_value)
 *
 */
trait EntityMetadata{
    use MagicGetter, MagicSetter;

    /**
     * Array with the metadata entities.
     * @var array
     */
    protected static $_metadata = array();

    /**
     * Array of the changed metadata keys
     * @var array
     */
    protected static $_changedMetadata = array();

    private $__metadata__tmpId = null;


    /**
     * This entity has metadata
     * @return bool true
     */
    public static function usesMetadata(){
        return true;
    }


    protected function _initMetadataArrays(){
        if(!$this->__metadata__tmpId)
            $this->__metadata__tmpId = $this->getClassName() . ':' . uniqid ();

        if(!key_exists($this->__metadata__tmpId, self::$_metadata)){

            self::$_metadata[$this->__metadata__tmpId] = array();
            self::$_changedMetadata[$this->__metadata__tmpId] = array();
        }
    }

    /**
     * This magic getter returns the metadata value if the property name is a key of some metadata, or returns the
     * result of the MagicGetter trait otherwise.
     *
     * @return mixed The metadata value.
     */
    function __metadata__get($name){
        $this->_initMetadataArrays();

        if($this->getRegisteredMetadata($name)){
            return $this->getMetadata($name);
        }

    }


    /**
     * This magic setter sets the metadata value if the property name is a key of some metadata, or pass to MagicSetter trait
     * otherwise.
     */
    function __metadata__set($name, $value){
        $this->_initMetadataArrays();

        if($this->getRegisteredMetadata($name)){
            $this->setMetadata($name, $value);
            return true;
        }
    }

    /**
     * Returns an array with the registered metadata for this entity.
     *
     * @param string $meta_key
     *
     * @return \MapasCulturais\MetadataDefinition|\MapasCulturais\MetadataDefinition[]
     */
    function getRegisteredMetadata($meta_key = null){
        $this->_initMetadataArrays();

        $app = App::i();

        if($this->usesTypes())
            $metas = App::i()->getRegisteredMetadata($this, $this->getType());
        else
            $metas = App::i()->getRegisteredMetadata($this);

        $can_view = $this->canUser('viewPrivateData');

        foreach($metas as $k => $v)
            if($v->private && !$can_view)
                unset($metas[$k]);


        if($meta_key)
            return key_exists ($meta_key, $metas) ? $metas[$meta_key] : null;
        else
            return $metas;
    }

    protected function canUserViewPrivateData($user){
        # TODO: verify if superAdmin shouldn't be checked
        if(
                $user->is('admin') ||
                $user->id ==
                $this->getOwnerUser()->id)
            return true;
        else
            return false;
    }

    /**
     * Returns the metadata of the registered metadata for this entity.
     *
     * @see \MapasCulturais\Definitions\Metadata::getMetadata()
     *
     * @return array The metadata for the registered metadata.
     */
    static function getMetadataMetadata(){
        $entity = isset($this) ? $this : self::getClassName();


        if(self::usesTypes() && is_object($entity))
            $metas = App::i()->getRegisteredMetadata($entity, $entity->getType());
        else
            $metas = App::i()->getRegisteredMetadata($entity);

        $result = array();
        foreach($metas as $metadata){
            $result[$metadata->key] = $metadata->getMetadata();
            $result[$metadata->key]['isMetadata'] = true;
            $result[$metadata->key]['isEntityRelation'] = false;
        }
        return $result;
    }

    /**
     * Returns the value of the given metadata key. If no key is passad, returns an array (key => value) with all values.
     *
     * @param string $meta_key the key of the metadata
     *
     * @return array|mixed The value of the given metadata key or an array of values for all metadatas for this entity.
     */
    function getMetadata($meta_key = null, $return_metadata_object = false){
        $this->_initMetadataArrays();

        if(!$this->id)
            return $meta_key ? null : array();


        if(!self::$_metadata[$this->__metadata__tmpId]){
            $app = App::i();

            $class = $this->getClassName();

            if(class_exists($class.'Meta')){
                // @TODO replace this lines by "$result = $this->__metadata;"
                $metadata_entity_class = $class.'Meta';
                $repo = $app->repo($metadata_entity_class);
                $result = $repo->findBy(array('owner' => $this));
            }else{
                $metadata_entity_class = '\MapasCulturais\Entities\Metadata';
                $repo = $app->repo($metadata_entity_class);
                $result = $repo->findBy(array('ownerId' => $this->id, 'ownerType' => $class));
            }

            foreach($result as $meta)
                self::$_metadata[$this->__metadata__tmpId][trim($meta->key)] = $meta;


        }

        if($meta_key){
            if($return_metadata_object)
                $result = key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]) ? self::$_metadata[$this->__metadata__tmpId][$meta_key] : null;
            else
                $result = key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]) ? self::$_metadata[$this->__metadata__tmpId][$meta_key]->value : null;
            return $result;
        }else{
            $result = array();
            foreach (self::$_metadata[$this->__metadata__tmpId] as $key => $obj)
                if($return_metadata_object)
                    $result[$key] = $obj;
                else
                    $result[$key] = $obj->value;
            return $result;
        }
    }

    function getChangedMetadata(){
        return self::$_changedMetadata[$this->__metadata__tmpId];
    }

    /**
     * Sets the value of the metadata with the given key.
     *
     * If a metadata with the given key exists for this entity, updates the value, otherwise creates a new metadata with the given key.
     *
     * This changes will be saved when the method save or saveMetadata are called.
     *
     * @param string $meta_key The key of the metadata to set the value.
     * @param mixed the value of the metadata.
     */
    function setMetadata($meta_key, $value){
        $this->_initMetadataArrays();

        $app = App::i();
        $meta = null;

        $class = $this->getClassName();

        if(class_exists($class.'Meta')){
            $metadata_entity_class = $class.'Meta';
            $repo = $app->repo($metadata_entity_class);
            if(key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]))
                $meta = self::$_metadata[$this->__metadata__tmpId][$meta_key];
            else if($this->id)
                $meta = $repo->findOneBy(array('owner' => $this, 'key' => $meta_key));

        }else{
            $metadata_entity_class = '\MapasCulturais\Entities\Metadata';
            $repo = $app->repo($metadata_entity_class);
            if(key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]))
                $meta = self::$_metadata[$this->__metadata__tmpId][$meta_key];
            else if($this->id)
                $meta = $repo->findOneBy(array('ownerId' => $this->id, 'ownerType' => $class, 'key' => $meta_key));
        }


        if(!$meta){
            $meta = new $metadata_entity_class;
            $meta->key = $meta_key;
            $meta->owner = $this;
        }
        //var_dump(array($meta_key, $value, $meta->value));
        if($meta->value != $value){
            self::$_changedMetadata[$this->__metadata__tmpId][$meta_key] = array('key'=> $meta_key, 'oldValue'=> $meta->value, 'newValue'=> $value);
           $meta->value = $value;
        }

        self::$_metadata[$this->__metadata__tmpId][$meta_key] = $meta;
    }

    /**
     * Returns the metadata validation errors.
     *
     * @return array Errors
     */
    function getMetadataValidationErrors(){
        $this->_initMetadataArrays();

        $this->getMetadata();
        $errors = array();

        $metas = $this->getRegisteredMetadata();

        foreach($metas as $meta_key => $meta){
            if(!$meta->is_required && (!key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]) || !self::$_metadata[$this->__metadata__tmpId][$meta_key]->value))
                continue;


            $metadata_definition = $this->getRegisteredMetadata($meta_key);
            $val = key_exists($meta_key, self::$_metadata[$this->__metadata__tmpId]) ? self::$_metadata[$this->__metadata__tmpId][$meta_key]->value : null;

            $metadata_value_errors = $metadata_definition->validate($this, $val);

            if(is_array($metadata_value_errors))
                $errors[$meta_key] = $metadata_value_errors;

        }

        return $errors;
    }

    /**
     * Saves the metadata values to the database.
     *
     * This method calls the save method of the metadata object passing true to the flush param.
     *
     * @see \MapasCulturais\Entity::save()
     */
    public function saveMetadata(){
        $this->_initMetadataArrays();

        $saved = false;
        foreach(self::$_changedMetadata[$this->__metadata__tmpId] as $meta_key=>$meta_value){
            $saved = true;
            self::$_metadata[$this->__metadata__tmpId][$meta_key]->save();
        }
    }
}