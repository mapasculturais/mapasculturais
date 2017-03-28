<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

abstract class Module {
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\RegisterFunctions;
    
    protected $_config;
    
    function __construct(array $config = []) {
        $this->_config = $config;
        
        $app = App::i();
        $active_theme = $app->view;
        $class = get_called_class();
        $reflaction = new \ReflectionClass($class);
        
        while($reflaction->getName() != __CLASS__){
            $dir = dirname($reflaction->getFileName());
            $active_theme->addPath($dir);
            
            $reflaction = $reflaction->getParentClass();
        }
        
        $app->applyHookBoundTo($this, "module({$class}).init:before");
        $this->_init();
        $app->applyHookBoundTo($this, "module({$class}).init:after");
    }
    
    function getConfig(){
        return $this->_config;
    }
    
    abstract function _init();
    
    abstract function register();
}