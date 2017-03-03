<?php
namespace MapasCulturais;

abstract class EvaluationMethod extends Plugin implements \JsonSerializable{
    abstract protected function _register();
    
    abstract function enqueueScriptsAndStyles();
    
    abstract function getSlug();
    abstract function getName();
    abstract function getDescription();
    
    abstract function getConfigurationFormPartName();
    
    function getEvaluationFormPartName(){
        $slug = $this->getSlug();
        
        return "$slug--evaluation-form";
    }
    
//    abstract function getPartialResult(Entities\Registration $registration);
    
    function register(){
        $app = App::i();
        
        $def = new Definitions\EvaluationMethod($this);
        
        $app->registerEvaluationMethod($def);
        
        $type = new Definitions\EntityType('MapasCulturais\Entities\EvaluationMethodConfiguration', $this->getSlug(), $this->getName());
        
        $app->registerEntityType($type);
         
        $this->_register();
        
        $self = $this;
        
        $app->hook('view.includeAngularEntityAssets:after', function() use($self){
            $self->enqueueScriptsAndStyles();
        });
        
    }
    
    function registerMetadata($key, array $config){
        $app = App::i();
        
        $metadata = new Definitions\Metadata($key, $config);
        
        $app->registerMetadata($metadata, 'MapasCulturais\Entities\EvaluationMethod', $this->getSlug());
    }
    
    function usesEvaluationCommittee(){
        return true;
    }
    
    public function jsonSerialize() {
        return null;
    }
}