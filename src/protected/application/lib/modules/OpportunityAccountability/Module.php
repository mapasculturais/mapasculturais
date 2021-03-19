<?php

namespace OpportunityAccountability;

use OpportunityPhases\Module as PhasesModule;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Definitions\ChatThreadType;
use MapasCulturais\Entities\Notification;

/**
 * @property Module $evaluationMethod
 */
class Module extends \MapasCulturais\Module
{
    /**
     * @var Module
     */
    protected $evaluationMethod;

    function _init()
    {
        $app = App::i();

        $this->evaluationMethod = new EvaluationMethod($this->_config);
        $this->evaluationMethod->module = $this;

        $registration_repository = $app->repo('Registration');

        // impede que a fase de prestação de contas seja considerada a última fase da oportunidade
        $app->hook('entity(Opportunity).getLastCreatedPhase:params', function(Opportunity $base_opportunity, &$params) {
            $params['isAccountabilityPhase'] = 'NULL()';
        });

        // retorna a inscrição da fase de prestação de contas
        $app->hook('entity(Registration).get(accountabilityPhase)', function(&$value) use ($registration_repository){
            $opportunity = $this->opportunity->parent ?: $this->opportunity;
            $accountability_phase = $opportunity->accountabilityPhase;

            $value = $registration_repository->findOneBy([
                'opportunity' => $accountability_phase,
                'number' => $this->number
             ]);
        });

        // retorna o projeto relacionado à inscricão
        $app->hook('entity(Registration).get(project)', function(&$value) {
            if (!$value) {
                $first_phase = $this->firstPhase;
                if ($first_phase && $first_phase->id != $this->id) {
                    $value = $first_phase->project;
                }
            }
        });

        // na publicação da última fase, cria os projetos
        $app->hook('entity(Opportunity).publishRegistration', function (Registration $registration) use($app) {
            if (! $this instanceof \MapasCulturais\Entities\ProjectOpportunity) {
                return;
            }

            if (!$this->isLastPhase) {
                return;
            }

            $project = new Project;
            $project->status = 0;
            $project->type = 0;
            $project->name = $registration->projectName ?: ' ';
            $project->parent = $app->repo('Project')->find($this->ownerEntity->id);
            $project->isAccountability = true;
            $project->owner = $registration->owner;

            $first_phase = $registration->firstPhase;

            $project->registration = $first_phase;
            $project->opportunity = $this->parent ?: $this;

            $project->save();

            $first_phase->project = $project;
            $first_phase->save(true);

            $app->applyHookBoundTo($this, $this->getHookPrefix() . '.createdAccountabilityProject', [$project]);

        });

        $app->hook('template(project.<<single|edit>>.tabs):end', function(){
            $project = $this->controller->requestedEntity;

            if ($project->isAccountability) {
                if ($project->canUser('@control')) {
                    $this->part('accountability/project-tab');
                }
            }
        },1000);

        $app->hook('template(project.<<single|edit>>.tabs-content):end', function(){
            $project = $this->controller->requestedEntity;

            if ($project->isAccountability) {
                if($project->canUser('@control')){
                    $this->part('accountability/project-tab-content');
                }
            }
        },1000);

        // Adidiona o checkbox haverá última fase
        $app->hook('template(opportunity.edit.new-phase-form):end', function () use ($app) {
            $app->view->part('widget-opportunity-accountability', ['opportunity' => '']);
        });

        $self = $this;
        $app->hook('entity(Opportunity).insert:after', function () use ($app, $self) {

            $opportunityData = $app->controller('opportunity');

            if ($this->isLastPhase && isset($opportunityData->postData['hasAccountability']) && $opportunityData->postData['hasAccountability']) {
                
                $self->createAccountabilityPhase($this->parent);
            }
        });


        $app->hook('template(project.<<single|edit>>.header-content):before', function () {
            $project = $this->controller->requestedEntity;

            if ($project->isAccountability) {
                $this->part('accountability/project-opportunity', ['opportunity' => $project->opportunity]);
            }
        });

        // Adiciona aba de projetos nas oportunidades com prestação de contas após a publicação da última fase
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {

            $entity = $this->controller->requestedEntity;

            // accountabilityPhase existe apenas quando lastPhase existe
            if ($entity->accountabilityPhase && $entity->lastPhase->publishedRegistrations) {
                $this->part('singles/opportunity-projects--tab', ['entity' => $entity]);
            }
        });
        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app) {

            $entity = $this->controller->requestedEntity;

            // accountabilityPhase existe apenas quando lastPhase existe
            if ($entity->accountabilityPhase && $entity->lastPhase->publishedRegistrations) {
                $this->part('singles/opportunity-projects', ['entity' => $entity]);
            }
        });
        
        /**
         * Substituição dos seguintes termos 
         * - avaliação por parecer
         * - avaliador por parecerista
         * - inscrição por prestação de contas
         */
        $replacements = [
            i::__('Nenhuma avaliação enviada') => i::__('Nenhum parecer técnico enviado'),
            i::__('Configuração da Avaliação') => i::__('Configuração do Parecer Técnico'),
            i::__('Comissão de Avaliação') => i::__('Comissão de Pareceristas'),
            i::__('Inscrição') => i::__('Prestacão de Contas'),
            i::__('inscrição') => i::__('prestacão de contas'),
            // inscritos deve conter somente a versão com o I maiúsculo para não quebrar o JS
            i::__('Inscritos') => i::__('Prestacoes de Contas'),
            i::__('Inscrições') => i::__('Prestações de Contas'),
            i::__('inscrições') => i::__('prestações de contas'),
            i::__('Avaliação') => i::__('Parecer Técnico'),
            i::__('avaliação') => i::__('parecer técnico'),
            i::__('Avaliações') => i::__('Pareceres'),
            i::__('avaliações') => i::__('pareceres'),
            i::__('Avaliador') => i::__('Parecerista'),
            i::__('avaliador') => i::__('parecerista'),
            i::__('Avaliadores') => i::__('Pareceristas'),
            i::__('avaliadores') => i::__('pareceristas'),
        ];

        $app->hook('view.partial(singles/opportunity-<<tabs|evaluations--admin--table|registrations--tables--manager|evaluations--committee>>):after', function($template, &$html) use($replacements) {
            $phase = $this->controller->requestedEntity;
            if ($phase->isAccountabilityPhase) {
                $html = str_replace(array_keys($replacements), array_values($replacements), $html);
            }
         });

         // substitui botões de importar inscrições da fase anterior
         $app->hook('view.partial(import-last-phase-button).params', function ($data, &$template) {
            $opportunity = $this->controller->requestedEntity;
            
            if ($opportunity->isAccountabilityPhase) {
                $template = "accountability/import-last-phase-button";
            }
         });

         // redireciona a ficha de inscrição da fase de prestação de contas para o projeto relacionado
         $app->hook('GET(registration.view):before', function() use($app) {
            $registration = $this->requestedEntity;
            if ($registration->opportunity->isAccountabilityPhase) {
                if ($project = $registration->project) {
                    $app->redirect($project->singleUrl . '#/tab=accountability');
                }
            }
         });
    }

    function register()
    {
        $app = App::i();
        $opportunity_repository = $app->repo('Opportunity');
        $registration_repository = $app->repo('Registration');
        $project_repository = $app->repo('Project');

        $this->registerProjectMetadata('isAccountability', [
            'label' => i::__('Indica que o projeto é vinculado à uma inscrição aprovada numa oportunidade'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerProjectMetadata('opportunity', [
            'label' => i::__('Oportunidade da prestação de contas vinculada a este projeto'),
            'type' => 'Opportunity',
            'serialize' => function (Opportunity $opportunity) {
                return $opportunity->id;
            },
            'unserialize' => function ($opportunity_id, $opportunity) use($opportunity_repository, $app) {

                if ($opportunity_id) {
                    return $opportunity_repository->find($opportunity_id);
                } else {
                    return null;
                }
            }
        ]);

        $this->registerProjectMetadata('registration', [
            'label' => i::__('Inscrição da oportunidade da prestação de contas vinculada a este projeto (primeira fase)'),
            'type' => 'Registration',
            'private' => true,
            'serialize' => function (Registration $registration) {
                return $registration->id;
            },
            'unserialize' => function ($registration_id) use($registration_repository) {
                if ($registration_id) {
                    return $registration_repository->find($registration_id);
                } else {
                    return null;
                }
            }
        ]);

        $this->registerRegistrationMetadata('project', [
            'label' => i::__('Projeto da prestação de contas vinculada a esta inscrição (primeira fase)'),
            'type' => 'Project',
            'private' => true,
            'serialize' => function (Project $project) {
                return $project->id;
            },
            'unserialize' => function ($project_id) use($project_repository) {
                if ($project_id) {
                    return $project_repository->find($project_id);
                } else {
                    return null;
                }
            }
        ]);

        $this->registerOpportunityMetadata('isAccountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerOpportunityMetadata('accountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'Opportunity',
            'serialize' => function (Opportunity $opportunity) {
                return $opportunity->id;
            },
            'unserialize' => function ($opportunity_id, $opportunity) use($opportunity_repository) {
                if ($opportunity_id) {
                    return $opportunity_repository->find($opportunity_id);
                } else {
                    return null;
                }
            }
        ]);

        $thread_type_description = i::__('Conversação entre proponente e parecerista no campo da prestação de contas');
        $definition = new ChatThreadType('accountability-field', $thread_type_description, function (ChatMessage $message) {
            $thread = $message->thread;
            $evaluation = $thread->ownerEntity;
            $registration = $evaluation->registration;
            $notification_content = '';
            $sender = '';
            $recipient = '';
            $notification = new Notification;
            if ($message->thread->checkUserRole($message->user, 'admin')) {
                // mensagem do parecerista
                $notification->user = $registration->agent->user;
                $notification_content = i::__("Nova mensagem do parecerista da prestação de contas número %s");
                $sender = 'admin';
                $recipient = 'participant';
            } else {
                // mensagem do usuário responsável pela prestação de contas
                $notification->user = $evaluation->user;
                $notification_content = i::__("Nova mensagem na prestação de contas número %s");
                $sender = 'participant';
                $recipient = 'admin';
            }
            $notification->message = sprintf($notification_content, $registration->number);
            $notification->save(true);
            $this->sendEmailForNotification($message, $notification, $sender, $recipient);
        });
        $app->registerChatThreadType($definition);

        $this->evaluationMethod->register();
    }

    // Migrar essa função para o módulo "Opportunity phase"
    function createAccountabilityPhase(Opportunity $parent)
    {

        $opportunity_class_name = $parent->getSpecializedClassName();

        $last_phase = \OpportunityPhases\Module::getLastCreatedPhase($parent);

        $phase = new $opportunity_class_name;

        $phase->status = Opportunity::STATUS_DRAFT;
        $phase->parent = $parent;
        $phase->ownerEntity = $parent->ownerEntity;

        $phase->name = i::__('Prestação de Contas');
        $phase->registrationCategories = $parent->registrationCategories;
        $phase->shortDescription = i::__('Descrição da Prestação de Contas');
        $phase->type = $parent->type;
        $phase->owner = $parent->owner;
        $phase->useRegistrations = true;
        $phase->isOpportunityPhase = true;
        $phase->isAccountabilityPhase = true;

        $_from = $last_phase->registrationTo ? clone $last_phase->registrationTo : new \DateTime;
        $_to = $last_phase->registrationTo ? clone $last_phase->registrationTo : new \DateTime;
        $_to->add(date_interval_create_from_date_string('1 days'));

        $phase->registrationFrom = $_from;
        $phase->registrationTo = $_to;

        $phase->save(true);
    }
}
