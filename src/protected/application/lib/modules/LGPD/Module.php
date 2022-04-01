<?php

namespace LGPD;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module{
    
    /** @var Module $instance */
    protected static $instance;
    
    function __construct($config = []) {
          
        $config += [
            'text_terms' => 'Estesss site utiliza agora cookies para armazenar info...',
        ];

        parent::__construct($config);
        self::$instance = $this;
    }

    public function _init(){
        $app = App::i();
        $app->view->enqueueScript('app','LGPD', 'js/script-lgpd.js', ['mapasculturais']);//assets
        $app->hook('template(site.index.home-developers):end', function() use ($app){
            
            /** @var MapasCulturais\Theme $this*/
           $this->part('lgpd/acept-lgpd');
    
        });
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

    /**
     */
    public static function getInstance()
    {
        return self::$instance;
    }

}

