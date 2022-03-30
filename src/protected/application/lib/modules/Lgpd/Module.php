<?php

namespace Lgpd;

class Module extends \MapasCulturais\Module{
    public function _init(){
       

    }
    public function register()
    {
        $this->registerMetadata( 'MapasCulturais\\Entities\\Agent', 'acept_lgpd', [
            'label'=> 'Aceite dos termos e condicoes da LGPD',
            'type'=>'array',
            'private'=> true,
            'default'=> 0,
            
        ]);
    }

}
