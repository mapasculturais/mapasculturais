<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

abstract class Plugin {
    use Traits\Singleton,
        Tratis\MagicGetter,
        Traits\MagicSetter;
    
    protected $_config;
    
    final protected function __construct(array $config = []) {
        $this->_config = $config;
    }
    
    
    function init(){
        $app = App::i();
        $active_theme = $app->view;
        
        $class = get_called_class();
        while($class != __CLASS__){
            $reflaction = new \ReflectionClass($class);
            $dir = dirname($reflaction->getFileName());
            $active_theme->addPath($dir);
            
            $class = $reflaction->getParentClass();
        }
        
        $app->applyHookBoundTo($this, "plugin({$class}).init:before");
        $this->_init();
        $app->applyHookBoundTo($this, "plugin({$class}).init:after");
    }
    
    abstract function _init();
    
    abstract function _register();
}