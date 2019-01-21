<?php

namespace DeleteAccount;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Module extends \MapasCulturais\Module{

    public function _init() {
        $app = App::i();
        
        // a cada login salva um novo token
        $app->hook('auth.successful', function() use($app){
            $token = sha1(uniqid(microtime(true), true));
            $app->disableAccessControl();
            $app->user->setMetadata('deleteAccountToken', $token);
            $app->user->save(true);
            $app->enableAccessControl();
        });

        // ação de apagar a conta
        $app->hook('GET(user.deleteAccount)', function() use($app){
            $this->requireAuthentication();

            if(isset($this->data['token'])){
                if($this->data['token'] === $app->user->deleteAccountToken){
                    die('OK');
                }
            }

            die('NOT OK');
        });

        $app->hook('GET(panel.deleteAccount)', function(){
            $this->requireAuthentication();
            $this->render('delete-account');
        });

        $app->hook('template(panel.index.content.entities):after', function(){
            $this->part('delete-account--button');
        });
    }

    public function register() {
        $this->registerUserMetadata('deleteAccountToken', ['label' => "Delete Account Token", 'private' => true]);        
    }
}