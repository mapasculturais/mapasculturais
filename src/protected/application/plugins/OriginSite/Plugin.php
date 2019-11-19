<?php

namespace OriginSite;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Plugin extends \MapasCulturais\Plugin {
    
    public function _init() {
        $app = App::i();
        
        $plugin = $this;
        
        $site_id = $this->_config['siteId'];

        if (is_callable($site_id)) {
            $site_id = $site_id();
        }


        $app->hook('entity(<<*>>).insert:before', function() use ($app, $site_id, $plugin) {
            $classes = $plugin->getEntitiesClasses();
            
            $this->origin_site = $site_id;
        });
    }

    public function register() {
        $app = App::i();
        
        $metadata = [];
        
        foreach($this->getEntitiesClasses() as $class){
            
            $def = new \MapasCulturais\Definitions\Metadata('origin_site', [
                'label' => \MapasCulturais\i::__('Origin Site')
            ]);
            
            $app->registerMetadata($def, $class);
            
        }
    }
    
    public function getEntitiesClasses(){
        $app = App::i();
        
        $controllers = $app->getRegisteredControllers(true);
        $entities_classes = [];
        
        foreach($controllers as $id => $controller) {
            if($controller instanceof \MapasCulturais\Controllers\EntityController){
                $controller = $app->controller($id);
                
                $entity_class = $controller->entityClassName;
                
                if($entity_class::usesMetadata()){
                    $entities_classes[] = $entity_class;
                }
            }
        }
        
        return $entities_classes;
    }
}
