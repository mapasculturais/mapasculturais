<?php

namespace ProfileCompletion;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    public function __construct(array $config = [])
    {
        $app = App::i();

        $config += [];
        parent::__construct($config);
    }

    function _init()
    {
        /** @var MapasCulturais\App $app */

        $app = App::i();

        //Caso config estiver em false, o modulo nao irá iniciar
        if(!$app->config['profilecompletion']){
            return;
        }            

        // Verifica se o agente tem os dados mínimos em seu cadastro
        $app->hook('auth.successful', function() use ($app){
            $agenteMetadada = $app->user->profile->getMetadata();
            $profile = $app->user->profile;
            $terms = $app->user->profile->getTerms()->getArrayCopy();

            if(!isset($agenteMetadada['documento']) || $profile->name === "" || empty($terms['area'] || empty($profile->shortDescription))){
                $url = $app->createUrl('agent', 'edita', ["id"=>$profile->id,"completeRegister"=>true]);
                $app->redirect($url);
            }
        });
        
        //Insere uma mensagem no topo da página de edição do agente
        $app->hook('template(agent.edit.name):after', function() use ($app){
            $request = $this->controller->data;
            if(isset($request['completeRegister'])){
                $this->part('profile-complete-message');
            }
        });
         
    }

    function register(){}
    
}
