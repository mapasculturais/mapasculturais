<?php

namespace ProfileCompletion;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    public function __construct(array $config = [])
    {
        $app = App::i();

        $config += ['enable' => false];
        parent::__construct($config);
    }

    function _init()
    {
        /** @var MapasCulturais\App $app */

        $app = App::i();

        //Caso config estiver em false, o modulo nao irá iniciar
        if(!$this->config['enable']){
            return;
        }
       
        //Insere uma mensagem no topo da página de edição do agente
        $app->hook('template(agent.edit.name):after', function() use ($app){
            if($app->user->profile->status === 0){
                $this->part('profile-complete-message');
            }
        });
         
    }

    function register(){}
    
}
