<?php

namespace OpportunityExecution;

use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    public function _init()
    {
        $app = App::i();

        $this->registerValidationHooks($app);
        $this->registerRegistrationCreationHook($app);
        $this->registerRegistrationSyncHooks($app);
        $this->registerPhaseHooks($app);
        $this->registerTemplateHooks($app);
        $this->registerCreateExecutionPhaseEndpoint($app);
        $this->registerExecutionPhaseRemovalHook($app);
        $this->registerCreateExecutionRequestEndpoint($app);
    }

    private function registerValidationHooks(App $app)
    {
        // ----------------------------------------------------------------
        // Remove as restrições de data que exigem que a fase seja anterior
        // ao publishTimestamp da lastPhase. A fase de execução ocorre APÓS
        // a publicação do resultado, então essas regras não se aplicam.
        // ----------------------------------------------------------------
        $app->hook('entity(Opportunity).validations', function (&$validations) {
            /** @var Opportunity $this */
            if (!$this->isExecutionPhase) {
                return;
            }
            unset($validations['registrationFrom']);
            unset($validations['registrationTo']);
        }, 999); // alta prioridade: roda depois do hook do OpportunityPhases

        // ----------------------------------------------------------------
        // Remove as restrições de data da EMC da fase de execução.
        // O getter EMC.nextPhase resolve para a lastPhase (pois não há fase
        // seguinte), e a validação compararia evaluationTo com publishTimestamp
        // da lastPhase — que já passou. A fase de execução não tem fase
        // sequencial seguinte, então as restrições de data relativas não
        // se aplicam.
        // ----------------------------------------------------------------
        $app->hook('entity(EvaluationMethodConfiguration).validations', function (&$validations) {
            /** @var EvaluationMethodConfiguration $this */
            if (!$this->opportunity->isExecutionPhase) {
                return;
            }
            unset($validations['evaluationFrom']);
            unset($validations['evaluationTo']);
        }, 999); // alta prioridade: roda depois do hook do OpportunityPhases
    }

    private function registerRegistrationCreationHook(App $app)
    {
        // ----------------------------------------------------------------
        // Permite criação de inscrições (pedidos) na fase de execução,
        // sobrescrevendo o bloqueio genérico do OpportunityPhases que
        // impede POST em qualquer fase filha (isOpportunityPhase).
        // ----------------------------------------------------------------
        $app->hook('POST(registration.index):before', function () use ($app) {
            $opportunity_id = $this->data['opportunityId'] ?? $this->data['opportunity'] ?? -1;
            $opportunity = $app->repo('Opportunity')->find($opportunity_id);

            if ($opportunity && $opportunity->isOpportunityPhase && $opportunity->isExecutionPhase) {
                // fase de execução: permite — anula o bloqueio do OpportunityPhases
                // (os hooks são chamados em ordem de registro; este módulo registra
                //  depois, mas o throw do OpportunityPhases precisa ser evitado.
                //  A solução real é o hook em OpportunityPhases verificar isExecutionPhase —
                //  ver ajuste em OpportunityPhases/Module.php)
                return;
            }
        }, 1000); // prioridade alta para rodar antes do hook de bloqueio
    }

    private function registerRegistrationSyncHooks(App $app)
    {
        // ----------------------------------------------------------------
        // Exclui a fase de execução da sincronização automática de
        // inscrições entre fases (sync, enqueue, import, removeOrphan).
        // ----------------------------------------------------------------
        $app->hook('Entities\Opportunity::enqueueRegistrationSync', function ($value) {
            /** @var Opportunity $this */
            if ($this->isExecutionPhase) {
                return false;
            }
        });

        $app->hook('Entities\Opportunity::syncRegistrations', function ($value) {
            /** @var Opportunity $this */
            if ($this->isExecutionPhase) {
                return false;
            }
        });

        $app->hook('Entities\Opportunity::importPreviousPhaseRegistrations', function () {
            /** @var Opportunity $this */
            if ($this->isExecutionPhase) {
                return;
            }
        });

        $app->hook('Entities\Opportunity::removeOrphanRegistrations', function () {
            /** @var Opportunity $this */
            if ($this->isExecutionPhase) {
                return;
            }
        });
    }

    private function registerPhaseHooks(App $app)
    {
        $self = $this;

        // ----------------------------------------------------------------
        // Posiciona a fase de execução após a lastPhase e antes das fases
        // de prestação de informações na lista allPhases.
        // ----------------------------------------------------------------
        $app->hook('entity(Opportunity).get(allPhases)', function (&$values) use ($self) {
            /** @var Opportunity $this */
            $values = $self->sortExecutionPhases($values);
        }, 20); // prioridade: roda depois do hook base do OpportunityPhases (priority 10)

        // ----------------------------------------------------------------
        // Garante que isExecutionPhase aparece nos dados simplificados
        // da fase retornados pelo getter phases (para $MAPAS.opportunityPhases).
        // ----------------------------------------------------------------
        $app->hook('module(OpportunityPhases).dataCollectionPhaseData', function (&$mout_simplify) {
            $mout_simplify .= ',isExecutionPhase';
        });
    }

    private function registerTemplateHooks(App $app)
    {
        $self = $this;

        // ----------------------------------------------------------------
        // Injeta a seção de pedidos de execução na aba "Ficha de inscrição"
        // da view single da inscrição, ao final do conteúdo de ficha.
        // O hook registration-ficha-tab:end é adicionado no single.php
        // da aba ficha, logo após o loop de fases de coleta de dados.
        // ----------------------------------------------------------------
        $app->hook('template(registration.view.registration-ficha-tab):end', function($entity) use ($self) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            /** @var \MapasCulturais\Entities\Registration $entity firstPhase registration */
            $self->renderExecutionRequestsOnRegistrationFicha($entity);
        });

        // ----------------------------------------------------------------
        // Injeta o componente opportunity-execution-requests na timeline
        // do proponente (opportunity-phases-timeline), ao final do item
        // da lastPhase, quando a inscrição do usuário está aprovada (status=10).
        // Fica aqui (e não em registration-status) para não depender de
        // publishedRegistrations — a fase de execução deve estar acessível
        // assim que a inscrição estiver aprovada e a fase de execução ativa.
        // ----------------------------------------------------------------
        $app->hook('component(opportunity-phases-timeline).item:end', function () {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            Module::renderOpportunityPhasesTimelineItem($this);
        });
    }

    private function registerCreateExecutionPhaseEndpoint(App $app)
    {
        $self = $this;

        // ----------------------------------------------------------------
        // Endpoint: cria a fase de execução vinculada à oportunidade.
        // Cria atomicamente a Opportunity (coleta) + EvaluationMethodConfiguration
        // (tipo simple), seguindo o mesmo padrão de POST_reportingPhase.
        // ----------------------------------------------------------------
        $app->hook('POST(opportunity.createExecutionPhase)', function () use ($self) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */
            $self->createExecutionPhase($this);
        });
    }

    private function registerExecutionPhaseRemovalHook(App $app)
    {
        // ----------------------------------------------------------------
        // Exclusão em cascata: quando a EvaluationMethodConfiguration da fase
        // de execução é removida diretamente, a fase de execução (Opportunity)
        // também deve ser destruída. O sentido inverso (Opportunity destruída →
        // EMC apagada) já é coberto pelo onDelete="CASCADE" no schema do banco.
        // ----------------------------------------------------------------
        $app->hook('entity(EvaluationMethodConfiguration).remove:after', function () use ($app) {
            /** @var EvaluationMethodConfiguration $this */
            $opportunity_id = $this->opportunity->id;
            $app->em->clear();
            $opportunity = $app->repo('Opportunity')->find($opportunity_id);
            if ($opportunity && $opportunity->isExecutionPhase) {
                $opportunity->destroy(true);
            }
        });
    }

    private function registerCreateExecutionRequestEndpoint(App $app)
    {
        $self = $this;

        // ----------------------------------------------------------------
        // Endpoint: o agente abre um pedido (inscrição) na fase de execução.
        // Qualquer agente relacionado à inscrição aprovada pode abrir pedidos.
        // ----------------------------------------------------------------
        $app->hook('POST(opportunity.createExecutionRequest)', function () use ($self) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */
            $self->createExecutionRequest($this);
        });
    }

    private function sortExecutionPhases($values)
    {
        if (!$values) {
            return $values;
        }

        [$execution_phases, $remaining] = $this->splitExecutionPhases($values);

        if (!$execution_phases) {
            return $values;
        }

        $result = $this->insertExecutionPhasesAfterLastPhase($remaining, $execution_phases, $inserted);

        if ($inserted) {
            return $result;
        }

        return $this->insertExecutionPhasesBeforeReportingPhases($result, $execution_phases);
    }

    private function splitExecutionPhases($values)
    {
        $execution_phases = [];
        $remaining = [];

        foreach ($values as $phase) {
            if ($phase->isExecutionPhase) {
                $execution_phases[] = $phase;
                continue;
            }

            $remaining[] = $phase;
        }

        return [$execution_phases, $remaining];
    }

    private function insertExecutionPhasesAfterLastPhase($phases, $execution_phases, &$inserted)
    {
        $result = [];
        $inserted = false;

        foreach ($phases as $phase) {
            $result[] = $phase;

            if ($phase->isLastPhase && !$inserted) {
                $result = array_merge($result, $execution_phases);
                $inserted = true;
            }
        }

        return $result;
    }

    private function insertExecutionPhasesBeforeReportingPhases($phases, $execution_phases)
    {
        $result = [];
        $inserted = false;

        foreach ($phases as $phase) {
            if ($phase->isReportingPhase && !$inserted) {
                $result = array_merge($result, $execution_phases);
                $inserted = true;
            }

            $result[] = $phase;
        }

        return $inserted ? $result : array_merge($result, $execution_phases);
    }

    private function renderExecutionRequestsOnRegistrationFicha($entity)
    {
        $first_phase_opp = $entity->opportunity->firstPhase ?? $entity->opportunity;
        $execution_phases = array_filter($first_phase_opp->allPhases ?? [], fn($p) => $p->isExecutionPhase);

        if (!$execution_phases) {
            return;
        }

        $last_phase_reg = $entity->lastPhase ?? $entity;

        foreach ($execution_phases as $exec_phase) {
            $requests = $this->findExecutionRequests($exec_phase, $last_phase_reg);

            if (!$requests) {
                continue;
            }

            $this->renderExecutionRequestsGroup($exec_phase, $requests);
        }
    }

    private function findExecutionRequests($exec_phase, $last_phase_reg)
    {
        $app = App::i();
        $ids = $app->em->getConnection()->fetchFirstColumn(
            "SELECT r.id FROM registration r
             INNER JOIN registration_meta m ON m.object_id = r.id
             WHERE r.opportunity_id = ?
               AND r.status > 0
               AND m.key = 'previousPhaseRegistrationId'
               AND m.value = ?",
            [$exec_phase->id, (string) $last_phase_reg->id]
        );

        if (!$ids) {
            return [];
        }

        return $app->repo('Registration')->findBy(
            ['id' => $ids],
            ['createTimestamp' => 'ASC', 'id' => 'ASC']
        );
    }

    private function renderExecutionRequestsGroup($exec_phase, $requests)
    {
        ?>
        <h2><?= htmlspecialchars($exec_phase->name) ?></h2>
        <?php
        foreach ($requests as $request) {
            ?>
            <h3><?= $request->number ?></h3>
            <v1-embed-tool route="registrationview" :id="<?= $request->id ?>"></v1-embed-tool>
            <?php
        }
    }

    private static function renderOpportunityPhasesTimelineItem($theme)
    {
        $theme->import('opportunity-execution-requests');
        ?>
        <opportunity-execution-requests
            v-if="item.isExecutionPhase && getRegistration(lastPhase)?.status == 10"
            :registration="getRegistration(lastPhase)"
            :phase="item"
            :phases="phases">
        </opportunity-execution-requests>
        <?php
    }

    private function createExecutionPhase($controller)
    {
        $opportunity = $controller->requestedEntity;
        $opportunity->checkPermission('@control');

        $root = $opportunity->isOpportunityPhase ? $opportunity->firstPhase : $opportunity;

        if ($this->hasExecutionPhase($root)) {
            $controller->errorJson(i::__('Já existe uma fase de execução para esta oportunidade'), 403);
            return;
        }

        $execution_phase = $this->buildExecutionPhase($root, $controller->data['collectionPhase'] ?? []);
        $evaluation_phase = $this->buildExecutionEvaluationPhase($execution_phase, $controller->data['evaluationPhase'] ?? []);

        if ($this->sendExecutionPhaseValidationErrors($controller, $execution_phase, $evaluation_phase)) {
            return;
        }

        $execution_phase->save(true);
        $evaluation_phase->save(true);

        $root->executionPhase = $execution_phase->id;
        $root->save(true);

        $execution_phase->evaluationMethodConfiguration = $evaluation_phase;

        $controller->json([
            'collectionPhase' => $execution_phase,
            'evaluationPhase' => $evaluation_phase,
        ]);
    }

    private function hasExecutionPhase(Opportunity $root)
    {
        foreach ($root->allPhases as $phase) {
            if ($phase->isExecutionPhase) {
                return true;
            }
        }

        return false;
    }

    private function buildExecutionPhase(Opportunity $root, array $data)
    {
        $class = $root->getSpecializedClassName();
        $execution_phase = new $class();
        $execution_phase->parent             = $root;
        $execution_phase->status             = Opportunity::STATUS_PHASE;
        $execution_phase->name               = $data['name'] ?? i::__('Fase de Execução');
        $execution_phase->registrationFrom   = $data['registrationFrom']['_date'] ?? null;
        $execution_phase->registrationTo     = $data['registrationTo']['_date'] ?? null;
        $execution_phase->type               = $root->type;
        $execution_phase->ownerEntity        = $root->ownerEntity;
        $execution_phase->isOpportunityPhase = true;
        $execution_phase->isDataCollection   = true;
        $execution_phase->isExecutionPhase   = true;
        $execution_phase->registrationLimitPerOwner = 0;

        return $execution_phase;
    }

    private function buildExecutionEvaluationPhase(Opportunity $execution_phase, array $data)
    {
        $evaluation_phase = new EvaluationMethodConfiguration();
        $evaluation_phase->opportunity    = $execution_phase;
        $evaluation_phase->type           = 'simple';
        $evaluation_phase->name           = $data['name'] ?? i::__('Avaliação dos pedidos');
        $evaluation_phase->evaluationFrom = $data['evaluationFrom']['_date'] ?? null;
        $evaluation_phase->evaluationTo   = $data['evaluationTo']['_date'] ?? null;

        return $evaluation_phase;
    }

    private function sendExecutionPhaseValidationErrors($controller, Opportunity $execution_phase, EvaluationMethodConfiguration $evaluation_phase)
    {
        $collection_errors = $execution_phase->getValidationErrors();
        $evaluation_errors = $evaluation_phase->getValidationErrors();

        if (empty($collection_errors) && empty($evaluation_errors)) {
            return false;
        }

        $controller->json([
            'errors'           => true,
            'collectionErrors' => $collection_errors,
            'evaluationErrors' => $evaluation_errors,
        ], 400);

        return true;
    }

    private function createExecutionRequest($controller)
    {
        $app = App::i();
        $opportunity = $controller->requestedEntity;
        $first_phase = $opportunity->firstPhase;
        $execution_phase = $this->findExecutionPhase($first_phase);

        if (!$execution_phase) {
            $controller->errorJson(i::__('Fase de execução não encontrada'), 404);
            return;
        }

        $registration_id = $controller->data['registration_id'] ?? 0;

        if (!$registration_id) {
            $controller->errorJson(i::__('ID da inscrição é obrigatório'), 400);
            return;
        }

        $approved_registration = $app->repo('Registration')->find($registration_id);

        if (!$this->validateApprovedRegistration($controller, $approved_registration, $first_phase)) {
            return;
        }

        if (!$this->canCreateExecutionRequest($approved_registration)) {
            $controller->errorJson(i::__('Você não tem permissão para abrir pedidos nesta inscrição'), 403);
            return;
        }

        $controller->json($this->saveExecutionRequest($execution_phase, $approved_registration));
    }

    private function findExecutionPhase(Opportunity $first_phase)
    {
        foreach ($first_phase->allPhases as $phase) {
            if ($phase->isExecutionPhase) {
                return $phase;
            }
        }

        return null;
    }

    private function validateApprovedRegistration($controller, $approved_registration, Opportunity $first_phase)
    {
        if (!$approved_registration) {
            $controller->errorJson(i::__('Inscrição não encontrada'), 404);
            return false;
        }

        if ($approved_registration->opportunity->id === $first_phase->lastPhase->id
            && $approved_registration->status === Registration::STATUS_APPROVED) {
            return true;
        }

        $controller->errorJson(i::__('A inscrição não está aprovada na publicação final do resultado'), 403);
        return false;
    }

    private function canCreateExecutionRequest(Registration $approved_registration)
    {
        $profile = App::i()->user->profile;

        if ($approved_registration->owner->id === $profile->id) {
            return true;
        }

        foreach ($approved_registration->agentRelations as $ar) {
            if ($ar->agent->id === $profile->id) {
                return true;
            }
        }

        return false;
    }

    private function saveExecutionRequest(Opportunity $execution_phase, Registration $approved_registration)
    {
        $new_registration = new Registration();
        $new_registration->opportunity = $execution_phase;
        $new_registration->owner       = $approved_registration->owner;
        // Copia o number da inscrição aprovada — mesmo padrão do recurso.
        // O apiFindRegistrations filtra resultados por number entre fases:
        // sem isso, os pedidos nunca aparecem na lista do gestor.
        $new_registration->number = $approved_registration->number;
        // Vincula ao aprovado original para rastreabilidade.
        $new_registration->previousPhaseRegistrationId = $approved_registration->id;

        $new_registration->save(true);

        return $new_registration;
    }

    public function register()
    {
        $app = App::i();

        // Flag que marca a fase como sendo de execução
        $this->registerOpportunityMetadata('isExecutionPhase', [
            'label'                    => i::__('É fase de execução?'),
            'type'                     => 'checkbox',
            'default'                  => false,
            'private'                  => false,
            'available_for_opportunities' => true,
        ]);

        // Referência da fase de execução armazenada na oportunidade principal
        // (atalho de navegação — armazena o ID da fase)
        $this->registerOpportunityMetadata('executionPhase', [
            'label'   => i::__('Fase de execução vinculada'),
            'type'    => 'integer',
            'default' => null,
            'private' => false,
            'available_for_opportunities' => true,
        ]);
    }
}
