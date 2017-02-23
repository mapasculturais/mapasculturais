<?php
namespace MapasCulturais;

abstract class EvaluationMethod extends Plugin{
    abstract function register();
    
    abstract function getSlug();
    abstract function getName();
    abstract function getDescription();
    
    function _register(){
        $app = App::i();
        
        $def = new Definitions\EvaluationMethod($this);
        
        $app->registerEvaluationMethod($def);
        
        $type = new Definitions\EntityType('MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug(), $this->getName());
        
        $app->registerEntityType($type);
        
        $this->register();
    }
    
    function registerMetadata($key, array $config){
        $app = App::i();
        
        $metadata = new Definitions\Metadata($key, $config);
        
        $app->registerMetadata($metadata, 'MapasCulturais\Entities\EvaluationMethod', $this->getSlug());
    }
}