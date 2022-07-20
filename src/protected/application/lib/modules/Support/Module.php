<?php

namespace Support;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    public const SUPPORT_GROUP = "@support";
    protected $inTransaction = false;
    protected $grantedCoarse = false;

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

        // Adiciona link da página de suporte no topo da ficha de inscrição
        $app->hook('template(registration.view.header-fieldset):end', function() use ($app){            
            $entity = $this->controller->requestedEntity;
            if($entity->canUser('support')){
                $this->part('support/support-link', ['entity' => $entity]);
            }
        });

        // Adiciona a aba do módulo de suporte na oportunidade
        $app->hook('template(opportunity.<<single|edit>>.tabs):end', function () use ($app, $self) {
            $entity = $this->controller->requestedEntity; 
            if ($entity->canUser("@control") || $self->isSupportUser($entity, $app->user)) {
                $this->part('support/opportunity-support--tab', ['entity' => $entity, 'user' => $app->user, 'module' => $self]);
            }
        });

        // Adiciona o conteúdo na aba de suporte dentro da opportunidade
        $app->hook('template(opportunity.<<single|edit>>.tabs-content):end', function () use ($app, $self) {
            $entity = $this->controller->requestedEntity; 
            if($entity->canUser('@control')){
                $this->part('support/opportunity-support-settings', ['entity' => $entity]);
            }

            
                $this->part('support/opportunity-support', ['entity' => $entity]);
        });
        // permissões granulares com uso de transactions
        $app->hook("PATCH(registration.single):before", function () use ($app, $self) {
            if ($self->isSupportUser($this->requestedEntity->opportunity, $app->user) ){
                $self->inTransaction = true;
                $app->hook("can(<<Agent|Space>>.<<@control|modify>>)", function($user,&$result) {
                    $result = true;
                });
                $app->em->beginTransaction();
            }
            return;
        });
        $app->hook("entity(RegistrationMeta).update:before", function ($params) use ($app, $self) {
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
            if ($self->inTransaction) {
                $app->em->rollback();
                throw new \Exception("Permission denied.");
            }
            return;
        });
        $app->hook("slim.after", function () use ($app, $self) {
            if ($self->inTransaction) {
                $app->em->commit();
            }
            return;
        });
        // permissões gerais
        $app->hook("can(Registration.support)", function ($user, &$result) use ($self) {
            $result = $self->isSupportUser($this->opportunity, $user);
            return;
        });
        $app->hook("can(Registration.<<view|modify|viewPrivateData>>)", function ($user, &$result) use ($self) {
            if (!$result) {
                $result = $this->canUser("support", $user);
                $self->grantedCoarse = $result;
            }
            return;
        });
        $app->hook("can(Registration<<File|Meta>>.<<create|remove>>)", function ($user, &$result) use ($self) {
            if (!$this->owner->canUser("@control")) {
                if ($self->grantedCoarse) {
                    $result = false;
                }
                $key = $this->group ?? $this->key;
                foreach ($this->owner->opportunity->agentRelations as $relation) {
                    if ((($relation->group == self::SUPPORT_GROUP) && ($relation->agent->user->id == $user->id)) &&
                        (($relation->metadata["registrationPermissions"][$key] ?? "") == "rw")) {
                            $result = true;
                            return;
                        }
                }
            }
            return;
        });
        $app->hook("entity(Registration).permissionCacheUsers", function (&$users) {
            $support_users = array_map(function ($agent) {
                return $agent->user;
            }, ($this->opportunity->relatedAgents[self::SUPPORT_GROUP] ?? []));
            $users = array_values(array_unique(array_merge($users, $support_users)));
            return;
        });

        // redireciona a ficha de inscrição para o suporte
        $app->hook('GET(registration.view):before', function() use($app) {
            $registration = $this->requestedEntity;
            if ($registration->canUser('support', $app->user)){
                if(!$registration->isUserAdmin($app->user) && !$registration->canUser('evaluate')){
                    $app->redirect($app->createUrl('support','registration', [$registration->id]) ) ;
                }
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

    public function isSupportUser($opportunity, $user)
    {
        if($user->is("admin")){
            return true;
        }

        foreach (($opportunity->relatedAgents[self::SUPPORT_GROUP] ?? []) as $agent) {
            if ($agent->user->id == $user->id) {
                return true;
            }
        }
        return false;
    }
}
