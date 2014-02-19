<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;

class Auth extends \MapasCulturais\Controller{
    function GET_index(){
        App::i()->redirect($this->createUrl('openid'));
    }

    function GET_openid(){
        $app = App::i();
        if(isset($app->config['opauth.strategies']['OpenID']['url']))
            $_POST['openid_url'] = $app->config['opauth.strategies']['OpenID']['url'];
        $app->auth->run();
    }

    function POST_openid(){
        App::i()->auth->run();
    }

    function ALL_logout(){
        $app = App::i();
        $app->auth->logout();
        $app->redirect($app->baseUrl);
    }

    function GET_response(){
        $app = App::i();

        $app->auth->processResponse();

        if($app->auth->isAuthenticated()){

            $app->redirect ($app->auth->getRedirectPath());
        }else{
            $app->redirect ($this->createUrl(''));
        }
    }
}