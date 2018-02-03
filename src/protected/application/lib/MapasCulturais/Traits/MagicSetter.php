<?php
namespace MapasCulturais\Traits;

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
        if(method_exists($this, 'set' . $name)){
            $setter = 'set' . $name;
            $this->$setter($value);
            return true;

        }elseif($name[0] !== '_' && property_exists($this, $name)){
            $this->$name = $value;
            return true;

        }else if(method_exists($this,'usesMetadata') && $this->usesMetadata() && $this->getRegisteredMetadata($name)){
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