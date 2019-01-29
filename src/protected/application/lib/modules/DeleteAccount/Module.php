<?php

namespace DeleteAccount;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Module extends \MapasCulturais\Module{

    public function _init() {
        $app = App::i();
        $theme = $app->view;
        if(false) $theme = new \MapasCulturais\Themes\BaseV1\Theme;

        $app->hook('mapasculturais.isEditable', function(&$result){
            if($this->controller->id == 'panel' && $this->controller->action == 'deleteAccount'){
                $result = true;
            }
        });
        $app->hook('mapasculturais.head', function() use($app, $theme){
            
            $theme->jsObject['angularAppDependencies'][] = 'DeleteAccount';
            $theme->enqueueScript('app', 'delete-account', 'js/ng.delete-account.js');
            if($theme->controller->id == 'panel' && $theme->controller->action == 'deleteAccount'){
                $theme->includeAngularEntityAssets($app->user->profile);
                $theme->includeEditableEntityAssets();
                $theme->addEntityToJs($app->user->profile);
            }
        });

        
        // a cada login salva um novo token
        $app->hook('auth.successful', function() use($app){
            $token = sha1(uniqid(microtime(true), true));
            $app->disableAccessControl();
            $app->user->setMetadata('deleteAccountToken', $token);
            $app->user->save(true);
            $app->enableAccessControl();
        });

        // ação de apagar a conta
        $app->hook('POST(user.deleteAccount)', function() use($app){
            // if(false) $this = new \MapasCulturais\Controllers\User;
            $this->requireAuthentication();

            if(isset($this->data['token'])){
                if($this->data['token'] === $app->user->deleteAccountToken){
                    $this->json($this->data);
                }
            }

            $this->errorJson(false,400);
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