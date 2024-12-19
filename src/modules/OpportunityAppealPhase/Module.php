<?php

namespace OpportunityAppealPhase;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Controllers;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;

class Module extends \MapasCulturais\Module {
    public function _init() {
        $app = App::i();

        /* Endpoint de criação de fase de recurso na oportunidade */
        $app->hook('POST(opportunity.createAppealPhase)', function() use ($app) {
            /** @var Controllers\Opportunity $this  */

            $opportunity = $this->requestedEntity;

            $opportunity->checkPermission('@control');

            $has_appeal_phase = $app->repo("Opportunity")->findOneBy(['parent' => $opportunity->id, 'status' => Opportunity::STATUS_APPEAL_PHASE]);

            if ($has_appeal_phase) {
                $this->errorJson(sprintf(i::__('Já existe uma fase de recurso para %s'), $opportunity->name), 403);
            }

            $class_name = $opportunity->getSpecializedClassName();

            $appeal_phase = new $class_name();
            $appeal_phase->parent = $opportunity;
            $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
            $appeal_phase->name = sprintf(i::__('Fase de recurso para %s'), $opportunity->name);
            $appeal_phase->ownerEntity = $opportunity->ownerEntity;
            $appeal_phase->registrationCategories = $opportunity->registrationCategories;
            $appeal_phase->registrationRanges = $opportunity->registrationRanges;
            $appeal_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;
            $appeal_phase->isDataCollection = true;
            $appeal_phase->isAppealPhase = true;
            $appeal_phase->save(true);

            $opportunity->appealPhase = $appeal_phase;
            $opportunity->save(true);
            
            $evaluation = new EvaluationMethodConfiguration();
            $evaluation->opportunity = $appeal_phase;
            $evaluation->type = 'simple';
            $evaluation->save(true);

            $this->json($appeal_phase);
        });

        /**
         * Endpoint para criação de inscrição na fase de recurso da oportunidade.
         *
         * @param int $registration_id
         */
        $app->hook('POST(opportunity.createAppealPhaseRegistration)', function() use ($app) {
            /** @var Controllers\Opportunity $this  */

            $opportunity = $this->requestedEntity;
            $appeal_phase = $opportunity->appealPhase;

            $data = $this->data;

            $registration_id = $data['registration_id'] ?? 0;

            if ($registration_id) {
                $registration = $app->repo('Registration')->findOneBy(['id' => $registration_id]);

                if (!$registration) {
                    $this->errorJson(sprintf(i::__('Não existe uma inscrição com o ID %s'), $registration_id), 403);
                }

                $opportunity = $app->repo('Opportunity')->findOneBy(['id' => $registration->opportunity->id]);

                if (!$opportunity) {
                    $this->errorJson(sprintf(i::__('Não existe uma oportunidade com o ID %s'), $registration->opportunity_id), 403);
                }

                $appeal_phase = $app->repo("Opportunity")->findOneBy(['parent' => $opportunity->id, 'status' => Opportunity::STATUS_APPEAL_PHASE]);
                
                if (!$appeal_phase) {
                    $this->errorJson(sprintf(i::__('Não existe uma fase de recurso para a %s'), $opportunity->name), 403);
                }

                $registration = new \MapasCulturais\Entities\Registration();
                $registration->opportunity = $appeal_phase;
                $registration->category = $opportunity->category;
                $registration->proponentType = $opportunity->proponentType;
                $registration->range = $opportunity->range;
                $registration->owner = $opportunity->owner;
                $registration->save(true);

                $this->json([]);
            }
        });
    }

    public function register() {
        $app = App::i();

        $this->registerOpportunityMetadata('appealPhase', [
            'label' => i::__('Indica se é uma fase de recurso'),
            'type'  => 'entity'
        ]);

        $this->registerOpportunityMetadata('isAppealPhase', [
            'label' => i::__('Indica se é uma fase de recurso'),
            'type'  => 'boolean'
        ]);

        $this->registerEvauationMethodConfigurationMetadata('appealPhase', [
            'label'     => i::__('Indica se é uma fase de recurso'),
            'type'      => 'entity',
            'serialize' => function($value, $evaluationMethodConfiguration) {
                $evaluationMethodConfiguration->opportunity->appealPhase = $value;
            },
            'unserialize' => function($value, $evaluationMethodConfiguration) {
                return $evaluationMethodConfiguration->opportunity->appealPhase;
            }
        ]);
    }
}