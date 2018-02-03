<?php
namespace MapasCulturais\Traits;

/**
 * Defines the magic callers that returns false if a method starts with **uses**
 */
trait MagicCallers{
    
    /**
     * This class uses magic callers
     * 
     * @return true
     */
    public static function usesMagicCallers(){
        return true;
    }

    /**
     * Magic Call
     *
     * Returns false to all methods that starts with **uses** (traits must define a method like usesTraitName() that returns true)
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
     * Returns false to all methods that starts with **uses** (traits must define a method like usesTraitName() that returns true)
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
}
