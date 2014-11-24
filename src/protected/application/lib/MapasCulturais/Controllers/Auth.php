<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;

class Auth extends \MapasCulturais\Controller{
    function ALL_logout(){
        $app = App::i();
        $app->auth->logout();
        $app->redirect($app->baseUrl);
    }
    
    function GET_login(){
        $app = App::i();
        
        if(isset($this->getData['redirectTo'])){
            $app->auth->requireAuthentication($this->getData['redirectTo']);
        }else{
            $app->auth->requireAuthentication();
        }
    }
}