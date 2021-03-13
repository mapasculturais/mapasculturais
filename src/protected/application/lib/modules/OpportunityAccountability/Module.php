<?php

namespace OpportunityAccountability;

use OpportunityPhases\Module as PhasesModule;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;
use MapasCulturais\ApiQuery;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();

        $registration_repository = $app->repo('Registration');

        // impede que a fase de prestação de contas seja considerada a última fase da oportunidade
        $app->hook('entity(Opportunity).getLastCreatedPhase:params', function(Opportunity $base_opportunity, &$params) {
            $params['isAccountabilityPhase'] = 'NULL()';
        });

        $app->hook('entity(Registration).get(accountabilityPhase)', function(&$value) use ($registration_repository){
            $opportunity = $this->opportunity->parent ?: $this->opportunity;
            $accountability_phase = $opportunity->accountabilityPhase;

            $value = $registration_repository->findOneBy([
                'opportunity' => $accountability_phase,
                'number' => $this->number
             ]);
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

            $project->registration = $registration->firstPhase;
            $project->opportunity = $this->parent ?: $this;

            $project->save(true);

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

        //
        $app->hook('entity(Opportunity).insert:after', function () use ($app) {
        });


        $app->hook('template(project.<<single|edit>>.header-content):before', function () {
            $project = $this->controller->requestedEntity;

            if ($project->isAccountability) {
                $this->part('accountability/project-opportunity', ['opportunity' => $project->opportunity]);
            }
        });

        // 
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {

            // $entity = $this->controller->requestedEntity;
            // $this->part('singles/opportunity-projects--tab', ['entity' => $entity]);

        });

        //
        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app) {

            // $entity = $this->controller->requestedEntity;
            // $this->part('singles/opportunity-projects', ['entity' => $entity]);

        });
        
    }

    function register()
    {
        $app = App::i();
        $opportunity_repository = $app->repo('Opportunity');
        $registration_repository = $app->repo('Registration');

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
            'type' => 'number',
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
    }
}
