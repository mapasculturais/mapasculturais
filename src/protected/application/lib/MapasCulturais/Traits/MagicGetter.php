<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

/**
 * Defines the magic getter method the be used when trying to get a protected or private property.
 *
 * If a getter method with the same name of the property exists, for example **set*PropertyName***, then returns it,
 * else if the property name doesn't start with an undercore returns the value of the property directly.
 * Otherwise returns null.
 */
trait MagicGetter{
    /**
    * If a getter method with the same name of the property exists, for example **set*PropertyName***, then returns it,
    * Else if the property name doesn't start with an undercore returns the value of the property directly.
    * Otherwise returns null.
     */
    public function __get($name){
        if($name === '__enableMagicGetterHook'){
            return null;
        }

        if(property_exists($this, 'container') && $val = $this->container[$name]){
            $value =  $val;
        }elseif(method_exists($this, 'get' . $name)){
            $getter = 'get' . $name;
            $result = $this->$getter();
            $value =  $result;

        }else if($name[0] !== '_' && property_exists($this, $name)){
            $value =  $this->$name;

        }else if(method_exists($this,'usesMetadata') && $this->usesMetadata()){
            $value =  $this->__metadata__get($name);

        }else{
            $value = null;

        }

        if ($name != 'hookClassPath' && $name != 'hookPrefix' && $this->__enableMagicGetterHook ?? false) {
            $app = App::i();
            $hookPrefix = self::getHookPrefix();
            $hook_name =  "{$hookPrefix}.get({$name})";
            $app->applyHookBoundTo($this, $hook_name, [&$value, $name]);
        }

        return $value;
    }

    /**
     * This class uses MagicGetter
     * @return true
     */
    public static function usesMagicGetter (){
        return true;
    }
}