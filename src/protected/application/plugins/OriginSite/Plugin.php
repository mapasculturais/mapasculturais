<?php
namespace OriginSite;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;


class Plugin extends \MapasCulturais\Plugin {
    public function _init() {
        $site_id = $this->_config['siteId'];
        
        if(is_callable($site_id)){
            $site_id = $site_id();
        }
        
        
    }
    
    public function register() {
        $app = App::i();
        
        $metadata = [
            'MapasCulturais\Entities\Event' => [
                'origin_site' => [
                    'label' => $app->txt('Origin Site')
                ],
            ],

            'MapasCulturais\Entities\Project' => [
                'origin_site' => [
                    'label' => $app->txt('Origin Site')
                ],
            ],

            'MapasCulturais\Entities\Space' => [
                'origin_site' => [
                    'label' => $app->txt('Origin Site')
                ],
            ],

            'MapasCulturais\Entities\Agent' => [
                'origin_site' => [
                    'label' => $app->txt('Origin Site')
                ],
            ]
        ];
        
        foreach($metadata as $entity_class => $metas){
            foreach($metas as $key => $cfg){
                $def = new \MapasCulturais\Definitions\Metadata($key, $cfg);
                $app->registerMetadata($def, $entity_class);
            }
        }
    }
}