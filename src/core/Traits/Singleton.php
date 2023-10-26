<?php
namespace MapasCulturais\Traits;

/**
 * Defines that the class uses the Singleton Patern.
 *
 * Use this trait in classes that needs just one instance.
 *
 */
trait Singleton{
    /**
     * Array of instances of this class and all subclasses.
     * @var array
     */
    protected static $_singletonInstances = [];

    /**
     * Returns the singleton instance. This method creates the instance when called for the first time.
     * @return self
     */
    static public function i($config = null){
        $class = get_called_class();

        if(!key_exists($class, self::$_singletonInstances))
            self::$_singletonInstances[$class] = $config ? new $class($config) : new $class;

        return self::$_singletonInstances[$class];
    }

    /**
     * This class use Singleton
     * @return true
     */
    public static function usesSingleton(){
        return true;
    }
}