<?php

namespace ProfileCompletion;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    public function __construct(array $config = [])
    {
        $app = App::i();

        $config += [
            'enable' => false,
            'checkRequiredFieldsAgents' => false
        ];

        parent::__construct($config);
    }

    function _init()
    {
        /** @var MapasCulturais\App $app */

        $app = App::i();

        $self = $this;

        //Caso config estiver em false, o modulo nao irá iniciar
        if(!$this->config['enable']){
            return;
        }
       
        //Insere uma mensagem no topo da página de edição do agente
        $app->hook('template(agent.edit.name):after', function() use ($app, $self){
            if($app->user->profile->status === 0 || $self->hasErrorsRequiredFields()){
                $this->part('profile-complete-message');
            }
        });

        // Evita acesso a locais que precisam de autenticação caso tenha algum campo obrigatorio nao preenchido no agente
        $app->hook('GET(panel.<<*>>):before, GET(<<opportunity|project|space|event>>.<<create|edit>>):before', function() use($app,$self){
            if($self->config['checkRequiredFieldsAgents'] && $self->hasErrorsRequiredFields()){
                $app->redirect($app->user->profile->editUrl);
            }
        });

        // Remove acessos do menu principal caso tenha algum campo obrigatorio nao preenchido no agente
        $app->hook('view.partial(nav-main-user).params', function($params, &$name) use($app,$self){ 
            if($self->config['checkRequiredFieldsAgents'] && $self->hasErrorsRequiredFields()){
                $name = 'header-profile-link';
            }
        });

        // Remove botão de criar entidade nas telas de listagens das entidades caso tenha algum campo obrigatorio nao preenchido no agente
        $app->hook('view.partial(modal/modal):after', function($template, &$html) use($app,$self){
            if($self->config['checkRequiredFieldsAgents'] && $self->hasErrorsRequiredFields()){
                $html = "";
            }
        });
        
    }

    function register(){}

    public function hasErrorsRequiredFields()
    {
        /** @var MapasCulturais\App $app */
        $app = App::i();

        if ($this->config['checkRequiredFieldsAgents'] && !$app->user->is('guest')) {
            $agent = $app->user->profile;

            if ($agent->ValidationErrors) {
                return true;
            }
        }

        return false;
    }
    
}
