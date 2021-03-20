<?php

namespace Support;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    public const SUPPORT_GROUP = "@support";

    public function __construct(array $config = [])
    {
        $app = App::i();

        $config += [];
        parent::__construct($config);
    }


    public function _init()
    {
        $app = App::i();

        $self = $this;

        // Adiciona a aba do módulo de suporte dentro da opportunidade
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            if ($this->controller->requestedEntity->canUser("@control")) {
                $this->part('support/opportunity-support--tab');
            }
        });

        // Adiciona conteúdo na aba de suporte dentro da opportunidade
        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app, $self) {
            $this->part('support/opportunity-support');
        });

       
    }


    public function register()
    {
        $app = App::i();

        $app->registerController('support', Controller::class);

        $self = $this;

        $app->hook('view.includeAngularEntityAssets:after', function () use ($self) {
            $self->enqueueScriptsAndStyles();
        });

    }

    public function enqueueScriptsAndStyles()
    {
        $app = App::i();
        $app->view->enqueueStyle('app', 'support', 'css/support.css');
        $app->view->enqueueScript('app', 'support', 'js/ng.support.js', ['entity.module.opportunity']);
        $app->view->jsObject['angularAppDependencies'][] = 'ng.support';
    }
}