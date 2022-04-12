<?php

namespace LGPD;

use MapasCulturais\App;
class Module extends \MapasCulturais\Module{
   
    function __construct($config = []) 
    {
          
        $config += [];

        parent::__construct($config);
    }

    public function _init() 
    {
        /** @var App $app */
        $app = App::i();
        
        $app->hook('GET(<<*>>):before,-GET(lgpd.<<*>>):before', function() use ($app){
            if($app->user->is('guest'))
                return;
            
            $user = $app->user;
            $config = $app->config['module.LGPD'];
            
            foreach($config as $key => $value){
                $term_hash = md5($value['text']);
                $accept_terms = $user->{"lgpd_{$key}"};
                if(!isset($accept_terms->$term_hash)){
                    $url =  $app->createUrl('lgpd', 'accept', [$key]);
                    $app->redirect($url);
                }
            }
        });
    }

    public function register() 
    {
        $app= App::i();
        $app->registerController('lgpd', Controller::class);
        $config = $app->config['module.LGPD'];
        foreach($config as $key => $value){
            $this->registerUserMetadata("lgpd_{$key}", [
                'label'=> $value['title'],
                'type'=>'json',
                'private'=> true,
                'default'=> '{}',
            ]);
        }
    }

}