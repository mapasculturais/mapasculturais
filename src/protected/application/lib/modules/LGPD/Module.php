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
        $app->view->enqueueScript('app','LGPD', 'js/script-lgpd.js', ['mapasculturais']);//assets
        $app->hook('template(site.index.home-developers):end', function() use ($app){
       
        $app->view->enqueueStyle('app', 'lgpd-style', 'css/lgpd.css');  
      
        //  $app->hook('template(site.index.home-developers):end', function() use ($app){
            
        // //     /** @var MapasCulturais\Theme $this*/
        // //        // $this->part('lgpd/acept-lgpd');
                
       
        // });
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
   

}

