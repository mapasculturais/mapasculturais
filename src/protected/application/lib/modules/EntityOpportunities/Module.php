<?php
namespace EntityOpportunities;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    public function __construct(array $config = array()) {
        $config = $config + [
            'entities' => ['agent', 'space', 'project', 'event']
        ];
        parent::__construct($config);
    }
    
    public function _init() {
        $app = App::i();
        
        $entities = implode('|', $this->config['entities']);
        
        $app->hook("template(<<$entities>>.single.tabs):end", function(){
            $entity = $this->controller->requestedEntity;
            
            $this->part('entity-opportunities--tabs-single', ['entity' => $entity]);
        });
        
        $app->hook("template(<<$entities>>.edit.tabs):end", function(){
            $entity = $this->controller->requestedEntity;
            
            $this->part('entity-opportunities--tabs-edit', ['entity' => $entity]);
        });
        
        $app->hook("template(<<$entities>>.single.tabs-content):end", function(){
            $entity = $this->controller->requestedEntity;
            $this->part('entity-opportunities--content-single', ['entity' => $entity]);
        });
        
        $app->hook("template(<<$entities>>.edit.tabs-content):end", function(){
            $entity = $this->controller->requestedEntity;
            
            $this->part('entity-opportunities--content-edit', ['entity' => $entity]);
        });
        
        
    }
    
    public function register() {
        $app = App::i();
        
    }
    
    
}