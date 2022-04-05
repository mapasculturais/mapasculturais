<?php

namespace LGPD;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module{
   
    function __construct($config = []) {
          
        $config += [];

        parent::__construct($config);
    }

    public function _init(){
        /** @var App $app */
        $app = App::i();
    }

    public function register()
    {
        $app= App::i();
        $app->registerController('lgpd', Controller::class);
        $this->registerUserMetadata('lgpd_termsOfUsage', [
            'label'=> 'Aceite dos termos e condicoes da LGPD',
            'type'=>'array',
            'private'=> true,
            'default'=> null,
        ]);

        $this->registerUserMetadata('lgpd_privacyPolice', [
            'label'=> 'Aceite dos termos e condicoes da LGPD',
            'type'=>'array',
            'private'=> true,
            'default'=> null,
    
        ]);
        
    }
}