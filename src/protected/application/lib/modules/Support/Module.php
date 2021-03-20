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

        $app->hook("PATCH(registration.single):before", function () use ($app) {
            $app->em->beginTransaction();
            return;
        });
        $app->hook("entity(RegistrationMeta).update:before", function ($params) use ($app) {
            if ($this->owner->canUser("@control")) {
                return;
            }
            foreach ($this->owner->opportunity->agentRelations as $relation) {
                if (($relation->group != self::SUPPORT_GROUP) || ($relation->agent->user->id != $app->user->id)) {
                    continue;
                }
                if (($relation->metadata["registrationPermissions"][$this->key] ?? "") == "rw") {
                    return;
                }
            }
            $app->em->rollback();
            throw new \Exception("Permission denied.");
            return;
        });
        $app->hook("slim.after", function () use ($app) {
            if ($app->em->getConnection()->getTransactionNestingLevel() > 0) {
                $app->em->commit();
            }
            return;
        });
        $app->hook("can(Registration.support)", function ($user, &$result) use ($self) {
            $result = $self->isSupportUser($this->opportunity, $user);
            return;
        });


        $app->hook("can(Registration.<<view|modify|viewPrivateData>>)", function ($user, &$result) {
            if (!$result) {
                $result = $this->canUser("support", $user);
            }
            return;
        });


        // redireciona a ficha de inscrição para o suporte
        $app->hook('GET(registration.view):before', function() use($app) {
            $registration = $this->requestedEntity;

            if ($registration->canUser('support', $app->user)){
                $app->redirect($app->createUrl('support','registration', [$registration->id]) ) ;
            }
            
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
    public function isSupportUser($opportunity, $user){
        foreach (($opportunity->relatedAgents[self::SUPPORT_GROUP] ?? []) as $agent) {
            if ($agent->user->id == $user->id) {
                return true;
            }
        }
        return false;
    }
}