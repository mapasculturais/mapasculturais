<?php
namespace MapasCulturais\Traits;

use Exception;
use MapasCulturais\App;

/**
 * Defines that the entity has metadata.
 *
 * Use this trait only in subclasses of **\MapasCulturais\Entity**. A class with the same name suffixed with **Meta** is required.
 * 
 * For example: For a class named **Name\Space\EntityClass** a class named **Name\Space\EntityClassMeta** is required.
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
 * @property-read \MapasCulturais\MetadataDefinition[] $registeredMetadata array of registered metadata.
 * @property-read string $metadataClassName the metadata class name.
 * @property-read string $metadataMetadata metadata of the registered metadata for this entity.
 * @property-read array $metadata all metadata of this entity.
 * @property-read array $changedMetadata changed metadata. 
 * 
 * <code>
 * [$meta_key => ['key'=> $meta_key, 'oldValue'=> $metadata_object->value, 'newValue'=> $value]]
 * </code>
 * 
 * @property-read array $metadataValidationErrors Description
 *
 */
trait EntityMetadata{
    use MagicGetter, MagicSetter;

    /**
     * Changed metadata.
     * 
     * The items of this array have the below format:
     * <code>
     *  ['key'=> $meta_key, 'oldValue'=> $metadata_object->value, 'newValue'=> $value]
     * </code>
     * 
     * @var array
     */
    private $__changedMetadata = [];

    /**
     * Created metadata.
     * 
     * @var \MapasCulturais\Entity[]
     */
    private $__createdMetadata = [];


    /**
     * This entity has metadata.
     * 
     * @return true
     */
    public static function usesMetadata(){
        return true;
    }

    /**
     * Returns the metadata entity class name for this entity.
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
        if($def = $this->getRegisteredMetadata($name)){
            $value = $this->getMetadata($name);
            
            if(is_callable($def->unserialize)){
                $cb = $def->unserialize;
                $value = $cb($value, $this, $def);
            }
            return $value;
        }

    }


    /**
     * This magic setter sets the metadata value if the property name is a key of some metadata, or pass to MagicSetter trait
     * otherwise.
     */
    function __metadata__set($name, $value){

        if($def = $this->getRegisteredMetadata($name)){
            if(is_callable($def->serialize)){
                $cb = $def->serialize;
                $value = $cb($value, $this, $def);
            }
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
    function getRegisteredMetadata($meta_key = null, $include_private = false){

        $app = App::i();

        if($this->usesTypes()){
            $metas = $app->getRegisteredMetadata($this, $this->getType());
        }else{
            $metas = $app->getRegisteredMetadata($this);
        }

        if (!$include_private) {
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
        }

        if($meta_key){
            return key_exists ($meta_key, $metas) ? $metas[$meta_key] : null;
        }else{
            return $metas;
        }
    }

    /**
     * Virifies if the user can view private metadata of this entity.
     * 
     * @param \MapasCulturais\Entities\User $user
     * 
     * @return boolean
     */
    protected function canUserViewPrivateData($user){
        if($user->is('guest')){
            return false;
        }

        if($this->isUserAdmin($user) || $this->canUser('@control', $user) || $this->equals($user)){
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

        $result = [];

        foreach($metas as $metadata){
            $result[$metadata->key] = $metadata->getMetadata();
            $result[$metadata->key]['isMetadata'] = true;
            $result[$metadata->key]['isEntityRelation'] = false;
        }
        return $result;
    }

    /**
     * Returns the value of the given metadata key. If no key is passad, returns an [key => value] with all values.
     *
     * @param string $meta_key the key of the metadata
     * @param boolean $return_metadata_object return the metadata object instead of mentadata value. default is false.
     *
     * @return array|mixed The value of the given metadata key or an array of values for all metadatas for this entity.
     */
    function getMetadata($meta_key = null, $return_metadata_object = false){
        // @TODO estudar como verificar se o objecto $this e $this->__metadata estão completos para caso contrário dar refresh
        if($meta_key){
            $def = $this->getRegisteredMetadata($meta_key, true);
            if(!$def){
                return null;
            }
            
            $default_value = $def->default_value;

            if(isset($this->__createdMetadata[$meta_key])){
                $metadata_object = $this->__createdMetadata[$meta_key];
            }else{
                $metadata_object = null;
                foreach($this->__metadata as $_metadata_object){
                    if($_metadata_object->key == $meta_key){
                        $metadata_object = $_metadata_object;
                    }
                }

                if (is_null($metadata_object)) {
                    $metadata_entity_class = $this->getMetadataClassName();

                    $metadata_object = new $metadata_entity_class;
                    $metadata_object->key = $meta_key;
                    $metadata_object->owner = $this;
                    
                    if (isset($default_value)) {
                        if($default_value instanceof \Closure) {
                            $callable = \Closure::bind($default_value, $this);
                            $metadata_object->value = $callable($def); 
                        } else {
                            $metadata_object->value = $default_value;
                        }
                    }
        
                    $this->__createdMetadata[$meta_key] = $metadata_object;
                }
            }

            if($return_metadata_object){
                $result = $metadata_object;
            }else{
                $result = $metadata_object->value; 
                
                if (!isset($result) && isset($default_value)) {
                    if($default_value instanceof \Closure) {
                        $callable = \Closure::bind($default_value, $this);
                        $result = $callable($def); 
                    } else {
                        $result = $default_value;
                    }
                }
            }

            return $result;
        }else{
            $result = [];
            
            foreach($this->getRegisteredMetadata(null, true) as $key => $def) {
                $result[$key] = $this->getMetadata($key, $return_metadata_object);
            }

            return $result;
        }
    }

    /**
     * Returns the changed matadata keys
     * @return type
     */
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
    	$app = App::i();
        $metadata_definition = $this->getRegisteredMetadata($meta_key);
        if (is_null($metadata_definition)) {
            $class = $this->getClassName();
            throw new Exception("The '{$class}::{$meta_key}' metadata is not registered");
        }

        $metadata_entity_class = $this->getMetadataClassName();
        $metadata_object =  $this->__createdMetadata[$meta_key] ?? 
                            $app->repo($metadata_entity_class)->findOneBy(['owner' => $this, 'key' => $meta_key], ['id' => 'ASC']);        

        if(!$metadata_object){
            $metadata_object = new $metadata_entity_class;
            $metadata_object->key = $meta_key;
            $metadata_object->owner = $this;

            $this->__createdMetadata[$meta_key] = $metadata_object;
        }

        if($metadata_object->value != $value){
            $this->__changedMetadata[$meta_key] = ['key'=> $meta_key, 'oldValue'=> $metadata_object->value, 'newValue'=> $value];
            $metadata_object->value = $value;
            if (property_exists($this, 'updateTimestamp')) {
                $this->updateTimestamp = new \DateTime;
            }
        }
    }

    /**
     * Returns the metadata validation errors.
     *
     * @return array Errors
     */
    function getMetadataValidationErrors(){
        $errors = [];

        $metas = $this->getRegisteredMetadata();

        foreach($metas as $meta_key => $metadata_definition){
            $metadata_object = $this->getMetadata($meta_key, true);


            if(!$metadata_definition->is_required && (is_null($metadata_object) || !$metadata_object->value))
                continue;

            $val = is_object($metadata_object) ? $metadata_object->value : null;

            $unserialize = $metadata_definition->unserialize;
            if (is_callable($unserialize)) {
                $val = $unserialize($val, $this);
            }

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
            $metadata_object->save(true);
        }
    }

    /**
     * Return Instagram URL if available
     *
     * @return string
     */
    public function getInstagramUrl(){
        if($this->instagram){
            $user = str_replace('@', '', $this->instagram);
            $url = 'http://www.instagram.com/' . $user;

            return $url;
        }else{
            return '#';
        }
    }
}