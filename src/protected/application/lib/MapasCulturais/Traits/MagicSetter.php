<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;

/**
 * Defines the magic setter method the be used when trying to set a protected or private property.
 *
 * If a setter method with the same name of the property exists, for example **set*PropertyName***, use it,
 * else if the property name doesn't starts with an undercore set the value of the property directly.
 */
trait MagicSetter{
    /**
     * If a setter method with the same name of the property exists, for example **set*PropertyName***, use it,
     * else if the property name doesn't starts with an undercore set the value of the property directly.
     */
    public function __set($name, $value){
        if($this instanceof Entity && $this->usesSealRelation()) {
            $app = App::i();
            if(in_array($name, $this->lockedFields) && $value != $this->$name) {
                throw new \MapasCulturais\Exceptions\PermissionDenied($app->user, $this, "modify locked field: $name");
            }
        }

        if(method_exists($this, 'set' . $name)){
            $setter = 'set' . $name;
            $this->$setter($value);
            return true;

        }elseif($name[0] !== '_' && property_exists($this, $name)){
            $this->$name = $value;
            return true;

        }else if($this instanceof Entity && $this->usesMetadata() && $this->getRegisteredMetadata($name)){
            return $this->__metadata__set($name, $value);

        }elseif($name[0] !== '_'){
            $this->$name = $value;
            return true;
        }
    }

    /**
     * This class uses MagicSetter
     * @return true
     */
    public static function usesMagicSetter (){
        return true;
    }
}