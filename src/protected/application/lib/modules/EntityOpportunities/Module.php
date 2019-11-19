<?php
namespace EntityOpportunities;

use MapasCulturais\App;
use MapasCulturais\i;

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
            
            $this->enqueueStyle('app', 'entity-opportunities', "css/entity-opportunities.css");
            
            $this->part('entity-opportunities--content-single', ['entity' => $entity]);
        });
        
        $app->hook("template(<<$entities>>.edit.tabs-content):end", function(){
            $entity = $this->controller->requestedEntity;
            
            $this->enqueueStyle('app', 'entity-opportunities', "css/entity-opportunities.css");
            
            $this->part('entity-opportunities--content-edit', ['entity' => $entity]);
        });
        
        
    }
    
    public function register() {
        $app = App::i();
        
        foreach ($this->config['entities'] as $entity){
            $method = "register{$entity}Metadata"; 
            
            $this->$method('opportunityTabName', [
                'label' => i::__('Nome da aba oportunidade')
            ]);
            
            $this->$method('useOpportunityTab', [
                'label' => i::__('Usar a aba oportunidades?'),
                'type' => 'select',
                'options' => (object) [
                    'true' => \MapasCulturais\i::__('Sim'),
                    'false' => \MapasCulturais\i::__('Não')
                ]
            ]);
        }
    }
    
    
}