<?php

namespace OpportunityCounterArgumentPhase;

use MapasCulturais\App;
use MapasCulturais\Controllers;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    public function _init()
    {
        $app = App::i();
        $self = $this;

        $app->hook('POST(opportunity.createCounterArgumentPhase)', function() use ($app, $self) {
            /** @var Controllers\Opportunity $this */
            $opportunity = $this->requestedEntity;

            $opportunity->checkPermission('@control');

            if (!$self->isTechnicalEvaluationPhase($opportunity)) {
                $this->errorJson(i::__('Contrarrazão só pode ser criada em fase de avaliação técnica'), 403);
            }

            if (!$opportunity->appealPhase) {
                $this->errorJson(i::__('Contrarrazão exige uma fase de recurso configurada'), 403);
            }

            $existing_counter_argument_phase = $app->repo('Opportunity')->findOneBy([
                'parent' => $opportunity->id,
                'status' => Opportunity::STATUS_COUNTER_ARGUMENT_PHASE,
            ]);

            if ($existing_counter_argument_phase) {
                $counter_argument_phase_meta = $app->repo('OpportunityMeta')->findOneBy([
                    'owner' => $opportunity,
                    'key' => 'counterArgumentPhase',
                ]);

                if ($counter_argument_phase_meta) {
                    $this->errorJson(sprintf(i::__('Já existe uma fase de contrarrazão para %s'), $opportunity->name), 403);
                }

                $existing_counter_argument_phase->delete(true);
                $opportunity = $opportunity->refreshed();
            }

            $class_name = $opportunity->getSpecializedClassName();
            $phase_name = $opportunity->evaluationMethodConfiguration ?
                $opportunity->evaluationMethodConfiguration->name : $opportunity->name;

            $counter_argument_phase = new $class_name();
            $counter_argument_phase->parent = $opportunity;
            $counter_argument_phase->status = Opportunity::STATUS_COUNTER_ARGUMENT_PHASE;
            $counter_argument_phase->name = sprintf(i::__('Contrarrazão para %s'), $phase_name);
            $counter_argument_phase->ownerEntity = $opportunity->ownerEntity;
            $counter_argument_phase->registrationCategories = $opportunity->registrationCategories;
            $counter_argument_phase->registrationRanges = $opportunity->registrationRanges;
            $counter_argument_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;
            $counter_argument_phase->isDataCollection = true;
            $counter_argument_phase->isCounterArgumentPhase = true;
            $counter_argument_phase->showPreviousPhaseEvaluationDetails = true;

            $conn = $app->conn;
            $conn->beginTransaction();

            try {
                $counter_argument_phase->save(true);

                $opportunity->counterArgumentPhase = $counter_argument_phase;
                $opportunity->save(true);

                $evaluation_method_configuration = new EvaluationMethodConfiguration();
                $evaluation_method_configuration->opportunity = $counter_argument_phase;
                $evaluation_method_configuration->type = 'continuous';
                $evaluation_method_configuration->publishEvaluationDetails = true;
                $evaluation_method_configuration->save(true);

                $counter_argument_phase->evaluationMethodConfiguration = $evaluation_method_configuration;

                $conn->commit();
            } catch (\Throwable $e) {
                if ($conn->isTransactionActive()) {
                    $conn->rollBack();
                }

                $orphan = $app->repo('Opportunity')->findOneBy([
                    'parent' => $opportunity->id,
                    'status' => Opportunity::STATUS_COUNTER_ARGUMENT_PHASE,
                ]);

                if ($orphan && !$app->repo('OpportunityMeta')->findOneBy([
                    'owner' => $opportunity,
                    'key' => 'counterArgumentPhase',
                ])) {
                    $orphan->delete(true);
                }

                $app->em->clear();

                throw $e;
            }

            $this->json($counter_argument_phase);
        });

        $app->hook('POST(opportunity.createCounterArgumentPhaseRegistration)', function() use ($app) {
            /** @var Controllers\Opportunity $this */
            try {
                $data = $this->data;
                $registration_id = $data['registration_id'] ?? $data['registrationId'] ?? 0;

                if (!$registration_id) {
                    $this->errorJson(i::__('ID da inscrição é obrigatório'), 400);
                }

                $registration = $app->repo('Registration')->findOneBy(['id' => $registration_id]);

                if (!$registration) {
                    $this->errorJson(sprintf(i::__('Não existe uma inscrição com o ID %s'), $registration_id), 403);
                }

                if ($registration->status === Registration::STATUS_DRAFT) {
                    $this->errorJson(i::__('Apenas inscrições enviadas podem solicitar contrarrazão'), 403);
                }

                $opportunity = $app->repo('Opportunity')->findOneBy(['id' => $registration->opportunity->id]);

                if (!$opportunity) {
                    $this->errorJson(sprintf(i::__('Não existe uma oportunidade com o ID %s'), $registration->opportunity_id), 403);
                }

                $counter_argument_phase = $opportunity->counterArgumentPhase;

                if (!$counter_argument_phase) {
                    $this->errorJson(sprintf(i::__('Não existe uma fase de contrarrazão para %s'), $opportunity->name), 403);
                }

                $existing_counter_argument = $app->repo('Registration')->findOneBy([
                    'opportunity' => $counter_argument_phase,
                    'number' => $registration->number,
                ]);

                if ($existing_counter_argument) {
                    $this->json($existing_counter_argument);
                    return;
                }

                $new_registration = new Registration();
                $new_registration->opportunity = $counter_argument_phase;
                $new_registration->category = $registration->category;
                $new_registration->proponentType = $registration->proponentType;
                $new_registration->range = $registration->range;
                $new_registration->owner = $registration->owner;
                $new_registration->number = $registration->number;
                $new_registration->save(true);

                $this->json($new_registration);
            } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
                $this->errorJson($e->getMessage(), 403);
            }
        });
    }

    public function register()
    {
        $this->registerOpportunityMetadata('counterArgumentPhase', [
            'label' => i::__('Fase de contrarrazão'),
            'type'  => 'entity'
        ]);

        $this->registerOpportunityMetadata('isCounterArgumentPhase', [
            'label' => i::__('Indica se é uma fase de contrarrazão'),
            'type'  => 'boolean'
        ]);

        $this->registerEvauationMethodConfigurationMetadata('counterArgumentPhase', [
            'label'     => i::__('Indica se é uma fase de contrarrazão'),
            'type'      => 'entity',
            'serialize' => function($value, $evaluationMethodConfiguration) {
                $evaluationMethodConfiguration->opportunity->counterArgumentPhase = $value;
            },
            'unserialize' => function($value, $evaluationMethodConfiguration) {
                return $evaluationMethodConfiguration->opportunity->counterArgumentPhase;
            }
        ]);
    }

    public function isTechnicalEvaluationPhase(Opportunity $opportunity): bool
    {
        $configuration = $opportunity->evaluationMethodConfiguration;

        if (!$configuration) {
            return false;
        }

        return $configuration->definition?->slug === 'technical' ||
            $configuration->type?->id === 'technical';
    }
}
