<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;

class Auth extends \MapasCulturais\Controller{
    function ALL_logout(){
        $app = App::i();
        $app->auth->logout();
        $app->redirect($app->baseUrl);
    }
}