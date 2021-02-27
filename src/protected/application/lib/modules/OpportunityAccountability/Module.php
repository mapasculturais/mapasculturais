<?php

namespace OpportunityAccountability;

use OpportunityPhases\Module as PhasesModule;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();

        // impede que a fase de prestação de contas seja considerada a última fase da oportunidade
        $app->hook('entity(Opportunity).getLastPhase:params', function(Opportunity $base_opportunity, &$params) {
            $params['isAccountabilityPhase'] = 'NULL()';
        });

        // na publicação da última fase, cria os projetos
        $app->hook('entity(ProjectOpportunity).publishRegistration', function (Registration $registration) use($app) {
            $last_phase = PhasesModule::getLastPhase($this);
            
            if (!$this->equals($last_phase)) {
                return;
            }

            // se não usa o campo nome do projeto, não criar projeto para prestação de conta
            if (!$this->projectName) {
                return;
            }
            
            $project = new Project;
            $project->parent = $this->ownerEntity;
            $project->name = $registration->projectName;
            $project->isAccountability = true;
            $project->owner = $registration->owner;
            $project->accountabilityRegistrationId = $registration->id;

            $project->save(true);

        });
    }

    function register()
    {
        $this->registerProjectMetadata('isAccountability', [
            'label' => i::__('Indica que o projeto é vinculado à uma inscrição aprovada numa oportunidade'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerProjectMetadata('accountabilityOpportunityId', [
            'label' => i::__('Id da oportunidade'),
            'type' => 'numeric'
        ]);

        $this->registerProjectMetadata('accountabilityRegistrationNumber', [
            'label' => i::__('Número da inscrição que originou o projeto'),
            'type' => 'string',
        ]);

        $this->registerProjectMetadata('accountabilityRegistrationId', [
            'label' => i::__('Id da inscrição da prestação de contas'),
            'type' => 'number',
            'private' => true
        ]);

        $this->registerOpportunityMetadata('isAccountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'boolean',
            'default' => false
        ]);
    }
}
