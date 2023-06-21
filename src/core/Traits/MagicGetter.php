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

    // @todo dynamic property

    public $__magicGetterCache;
    public $__enabledMagicGetterCaches;

    /**
    * If a getter method with the same name of the property exists, for example **set*PropertyName***, then returns it,
    * Else if the property name doesn't start with an undercore returns the value of the property directly.
    * Otherwise returns null.
     */
    public function __get($name){
        if(in_array($name, ['__enableMagicGetterHook', '__magicGetterCache', '__enabledMagicGetterCaches', '__enableMagicSetterHook'])){
            return null;
        }

        if ($this->__enabledMagicGetterCaches->$name ?? false) {
            if (property_exists($this->__magicGetterCache, $name)) {
                return $this->__magicGetterCache->$name;
            }
        }

        if(property_exists($this, 'container') && $val = $this->container[$name]){
            $value =  $val;
        }elseif(method_exists($this, 'get' . $name)){
            $getter = 'get' . $name;
            $result = $this->$getter();
            $value =  $result;

        }else if($name[0] !== '_' && property_exists($this, $name)){
            $value =  $this->$name;

        }else if($this->usesMagicSetter() && isset($this->__dynamicProperties[$name])) {
            $value = &$this->__dynamicProperties[$name];

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

        if ($this->__enabledMagicGetterCaches->$name ?? false) {
            $this->__magicGetterCache->$name = $value;
        }

        return $value;
    }


    /**
     * Habilita o cache do magic getter para a propriedade especificada
     * @param mixed $property_name propriedade que deve ser cacheada
     * @return void 
     */
    public function enableCacheGetterResult($property_name) {
        $this->__magicGetterCache = $this->__magicGetterCache ?? (object) [];
        $this->__enabledMagicGetterCaches = $this->__enabledMagicGetterCaches ?? (object) [];
        $this->__enabledMagicGetterCaches->$property_name = true;
    }

    /**
     * Desabilita o cache do magic getter para a propriedade especificada
     * @param mixed $property_name propriedade que não deve ser cacheada
     * @return void 
     */
    public function disableCacheGetterResult($property_name) {
        $this->__enabledMagicGetterCaches = $this->__enabledMagicGetterCaches ?? (object) [];
        $this->__enabledMagicGetterCaches->$property_name = false;
        unset($this->__magicGetterCache->$property_name);
    }

    /**
     * This class uses MagicGetter
     * @return true
     */
    public static function usesMagicGetter (){
        return true;
    }
}