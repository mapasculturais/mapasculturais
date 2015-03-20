<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

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
     * Array of the changed metadata keys
     * @var array
     */
    private $__changedMetadata = array();

    private $__createdMetadata = array();


    /**
     * This entity has metadata
     * @return bool true
     */
    public static function usesMetadata(){
        return true;
    }

    /**
     * Return the metadata entity class name for this Entity
     *
     * @return string
     */
    public static function getMetadataClassName(){
        $class = get_called_class();
        return $class::getClassName() . 'Meta';
    }

    /**
     * This magic getter returns the metadata value if the property name is a key of some metadata, or returns the
     * result of the MagicGetter trait otherwise.
     *
     * @return mixed The metadata value.
     */
    function __metadata__get($name){
        if($this->getRegisteredMetadata($name)){
            return $this->getMetadata($name);
        }

    }


    /**
     * This magic setter sets the metadata value if the property name is a key of some metadata, or pass to MagicSetter trait
     * otherwise.
     */
    function __metadata__set($name, $value){

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

        $app = App::i();

        if($this->usesTypes()){
            $metas = $app->getRegisteredMetadata($this, $this->getType());
        }else{
            $metas = $app->getRegisteredMetadata($this);
        }

        $can_view = $this->canUser('viewPrivateData');

        foreach($metas as $k => $v){
            $private = $v->private;
            if(is_callable($private)){
                $private = \Closure::bind($private, $this);
                if($private() && !$can_view){
                    unset($metas[$k]);
                }
            }else if($private && !$can_view){
                unset($metas[$k]);
            }
        }

        if($meta_key){
            return key_exists ($meta_key, $metas) ? $metas[$meta_key] : null;
        }else{
            return $metas;
        }
    }

    protected function canUserViewPrivateData($user){
        if($user->is('guest')){
            return false;
        }

        if($user->is('admin') || $this->getOwnerUser()->equals($user)){
            return true;
        }

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
        $app = App::i();

        $entity = isset($this) ? $this : self::getClassName();

        if(self::usesTypes() && is_object($entity)){
            $metas = $app->getRegisteredMetadata($entity, $entity->getType());
        }else{
            $metas = $app->getRegisteredMetadata($entity);
        }

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
        // @TODO estudar como verificar se o objecto $this e $this->__metadata estão completos para caso contrário dar refresh

        if($meta_key){
            if(isset($this->__createdMetadata[$meta_key])){
                $metadata_object = $this->__createdMetadata[$meta_key];
            }else{
                $metadata_object = null;
                foreach($this->__metadata as $_metadata_object){
                    if($_metadata_object->key == $meta_key){
                        $metadata_object = $_metadata_object;
                    }
                }
            }

            if($return_metadata_object){
                $result = is_object($metadata_object) ? $metadata_object : null;
            }else{
                $result = is_object($metadata_object) ? $metadata_object->value : null;
            }

            return $result;
        }else{
            $result = array();
            foreach (array_merge($this->__metadata->toArray(), $this->__createdMetadata) as $metadata_object){
                if($return_metadata_object){
                    $result[$metadata_object->key] = $metadata_object;
                }else{
                    $result[$metadata_object->key] = $metadata_object->value;
                }
            }
            return $result;
        }
    }

    function getChangedMetadata(){
        return $this->__changedMetadata;
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

        $metadata_entity_class = $this->getMetadataClassName();
        $metadata_object = $this->getMetadata($meta_key, true);

        $created = false;

        if(!$metadata_object){
            $created = true;
            $metadata_object = new $metadata_entity_class;
            $metadata_object->key = $meta_key;
            $metadata_object->owner = $this;

            $this->__createdMetadata[$meta_key] = $metadata_object;
        }

        if($metadata_object->value != $value){
            $this->__changedMetadata[$meta_key] = array('key'=> $meta_key, 'oldValue'=> $metadata_object->value, 'newValue'=> $value);
            $metadata_object->value = $value;
        }
    }

    /**
     * Returns the metadata validation errors.
     *
     * @return array Errors
     */
    function getMetadataValidationErrors(){
        $errors = array();

        $metas = $this->getRegisteredMetadata();

        foreach($metas as $meta_key => $metadata_definition){
            $metadata_object = $this->getMetadata($meta_key, true);


            if(!$metadata_definition->is_required && (is_null($metadata_object) || !$metadata_object->value))
                continue;

            $val = is_object($metadata_object) ? $metadata_object->value : null;

            $metadata_value_errors = $metadata_definition->validate($this, $val);

            if(is_array($metadata_value_errors))
                $errors[$meta_key] = $metadata_value_errors;

        }

        return $errors;
    }

    /**
     * Saves the metadata values to the database.
     *
     * @see \MapasCulturais\Entity::save()
     */
    public function saveMetadata(){
        foreach(array_keys($this->__changedMetadata) as $meta_key){
            $metadata_object = $this->getMetadata($meta_key, true);
            $metadata_object->save();
        }
    }
}