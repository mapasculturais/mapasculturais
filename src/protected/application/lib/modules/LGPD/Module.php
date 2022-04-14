<?php

namespace LGPD;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module{
   
    const key = "lgpd_redirect_referer";

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
            
            $skip_routes = [
                ["lgpd", "accept"],
                ["site", "search"]
            ];

            $route = [$this->id, $this->action];

            if(!in_array($route, $skip_routes) && !$app->request()->isAjax()){
                $_SESSION[self::key] = $_SERVER['REQUEST_URI'] ?? "";
            }
      
            $user = $app->user;
            $config = $app->config['module.LGPD'];
            
            foreach($config as $key => $value){
                $term_hash = self::createHash($value['text']);
                $accept_terms = $user->{"lgpd_{$key}"};
                if(!isset($accept_terms->$term_hash)){
                    $url =  $app->createUrl('lgpd', 'accept', [$key]);
                    $app->redirect($url);
                }
            }
        });
        
    }

    /**
     * @param string $text
     * @return string
     */
    public static function createHash($text)
    {
        $text = str_replace(" ", "", trim($text));
        $text = strip_tags($text);
        $text = str_replace("\n", "", trim($text));
        $text = str_replace("\t", "", trim($text));
        $text = strtolower($text);
        return md5($text);
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