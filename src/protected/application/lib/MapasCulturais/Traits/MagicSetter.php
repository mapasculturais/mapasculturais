<?php
namespace MapasCulturais\Traits;

/**
 * Defines the magic setter method the be used when trying to set a protected or private property.
 *
 * If a setter method with the same name of the property exists, for example setPropertyName, use it,
 * Else if the property name not starts with an undercore set the value of the property directly.
 * Otherwise throw an Exception.
 */
trait MagicSetter{
    /**
     * If a setter method with the same name of the property exists, for example setPropertyName, use it,
     * else if the property name not starts with an undercore set the value of the property directly.
     * @throws \Exception
     */
    public function __set($name, $value){
        if(method_exists($this, 'set' . $name)){
            $setter = 'set' . $name;
            $this->$setter($value);
            return true;

        }elseif($name[0] !== '_' && property_exists($this, $name)){
            $this->$name = $value;
            return true;

        }
    }

    /**
     * This class uses MagicSetter
     * @return bool true
     */
    public static function usesMagicSetter (){
        return true;
    }
}