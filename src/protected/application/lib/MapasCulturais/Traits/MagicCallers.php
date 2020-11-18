<?php
namespace MapasCulturais\Traits;

use Error;
use MapasCulturais\App;

/**
 * Defines the magic callers that returns false if a method starts with **uses**
 */
trait MagicCallers {
    
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
        $app = App::i();
        
        if(method_exists($this, 'getHookClassPath')) {
            $class = $this->getHookClassPath();
        } else if(method_exists($this,'getClassName')){
            $class = $this->getClassName();
        } else {
            $class = get_called_class();
        }

        $hook = "{$class}::$name";

        $hooks = $app->_getHookCallables($hook);

        if (method_exists($this, $name)) {
            return $this->$name();
        } else if (substr($name, 0, 4) === 'uses') {
            return false;
        } else if ($hooks) {
            $result = null;
            foreach ($hooks as $callable) {
                $callable = \Closure::bind($callable, $this);
                $args = [&$result];
                foreach($arguments as $arg){
                    $args[] = $arg;
                }
                call_user_func_array($callable, $args);
            }

            return $result;
        } else {
            $class = self::class;
            $trace = debug_backtrace();
            $l = $trace[0];
            throw new Error("Call to undefined method {$class}::{$name}() in {$l['file']} on line {$l['line']}");
        }
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
