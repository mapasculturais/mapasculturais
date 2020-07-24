<?php

namespace DeleteAccount;

use MapasCulturais\App,
    MapasCulturais\i,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Module extends \MapasCulturais\Module{

    public function _init() {
        $app = App::i();
        $theme = $app->view;
     
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
            $this->requireAuthentication();

            if(isset($this->data['token'])){
                if($this->data['token'] === $app->user->deleteAccountToken){
                    if(isset($this->data['agentId']) && $this->data['agentId']){
                        $target_agent = $app->repo('Agent')->find($this->data['agentId']);
                        $app->user->transferEntitiesTo($target_agent, true);
                    }

                    $app->user->delete(true);
                    $this->json(true);
                }
            }

            $this->errorJson(false,400);
        });

        // coloca o link para a página de apagar a conta
        $app->hook('template(panel.index.settings):end', function(){
            $this->part('delete-account--button');
        });

        // página do painel de apagar a conta
        $app->hook('GET(panel.deleteAccount)', function(){
            $this->requireAuthentication();
            $this->render('delete-account');
        });

              // define o isEditable como verdadeiro na página de apagar conta
        // é necessário para o componente de selecionar um agente de destino funcionar
        $app->hook('mapasculturais.isEditable', function(&$result){
            if($this->controller->id == 'panel' && $this->controller->action == 'deleteAccount'){
                $result = true;
            }
        });
        
        // registra o módulo angular da 
        $app->hook('mapasculturais.head', function() use($app, $theme){
            $theme->jsObject['angularAppDependencies'][] = 'DeleteAccount';
            $theme->enqueueScript('app', 'delete-account', 'js/ng.delete-account.js');
            $theme->localizeScript('delete-account', [
                'confirm' => i::__('Sua conta será removida e não poderá ser recuperada, deseja continuar?'),
                'goobye' => i::__('Sua conta foi removida e você será deslogado. =(')
            ]);
            if($theme->controller->id == 'panel' && $theme->controller->action == 'deleteAccount'){
                $theme->includeAngularEntityAssets($app->user->profile);
                $theme->includeEditableEntityAssets();
                $theme->addEntityToJs($app->user->profile);
            }
        });
    }

    public function register() {
        // registra o metadado do token de verificação da remoção da conta.
        $this->registerUserMetadata('deleteAccountToken', ['label' => "Delete Account Token", 'private' => true]);        
    }
}