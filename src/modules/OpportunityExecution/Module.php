<?php

namespace OpportunityExecution;

use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    public function _init()
    {
        $app = App::i();

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

        // ----------------------------------------------------------------
        // Posiciona a fase de execução após a lastPhase e antes das fases
        // de prestação de informações na lista allPhases.
        // ----------------------------------------------------------------
        $app->hook('entity(Opportunity).get(allPhases)', function (&$values) {
            /** @var Opportunity $this */
            if (!$values) {
                return;
            }

            $execution_phases = [];
            $remaining = [];

            foreach ($values as $phase) {
                if ($phase->isExecutionPhase) {
                    $execution_phases[] = $phase;
                } else {
                    $remaining[] = $phase;
                }
            }

            if (!$execution_phases) {
                return;
            }

            // Insere as fases de execução após a lastPhase e antes das reporting phases
            $result = [];
            $last_phase_inserted = false;

            foreach ($remaining as $phase) {
                $result[] = $phase;
                if ($phase->isLastPhase && !$last_phase_inserted) {
                    foreach ($execution_phases as $ep) {
                        $result[] = $ep;
                    }
                    $last_phase_inserted = true;
                }
            }

            // Caso não haja lastPhase na lista (edge case), insere antes das reporting
            if (!$last_phase_inserted) {
                $final = [];
                $reporting_inserted = false;
                foreach ($result as $phase) {
                    if ($phase->isReportingPhase && !$reporting_inserted) {
                        foreach ($execution_phases as $ep) {
                            $final[] = $ep;
                        }
                        $reporting_inserted = true;
                    }
                    $final[] = $phase;
                }
                if (!$reporting_inserted) {
                    foreach ($execution_phases as $ep) {
                        $final[] = $ep;
                    }
                }
                $result = $final;
            }

            $values = $result;
        }, 20); // prioridade: roda depois do hook base do OpportunityPhases (priority 10)

        // ----------------------------------------------------------------
        // Garante que isExecutionPhase aparece nos dados simplificados
        // da fase retornados pelo getter phases (para $MAPAS.opportunityPhases).
        // ----------------------------------------------------------------
        $app->hook('module(OpportunityPhases).dataCollectionPhaseData', function (&$mout_simplify) {
            $mout_simplify .= ',isExecutionPhase';
        });

        // ----------------------------------------------------------------
        // Injeta a seção de pedidos de execução na aba "Ficha de inscrição"
        // da view single da inscrição, ao final do conteúdo de ficha.
        // O hook registration-ficha-tab:end é adicionado no single.php
        // da aba ficha, logo após o loop de fases de coleta de dados.
        // ----------------------------------------------------------------
        $app->hook('template(registration.view.registration-ficha-tab):end', function($entity) use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            /** @var \MapasCulturais\Entities\Registration $entity firstPhase registration */

            // Localiza as fases de execução da oportunidade raiz
            $first_phase_opp = $entity->opportunity->firstPhase ?? $entity->opportunity;
            $execution_phases = array_filter($first_phase_opp->allPhases ?? [], fn($p) => $p->isExecutionPhase);

            if (!$execution_phases) {
                return;
            }

            // A lastPhase registration é o vínculo com os pedidos de execução
            $last_phase_reg = $entity->lastPhase ?? $entity;

            $conn = $app->em->getConnection();

            foreach ($execution_phases as $exec_phase) {
                $ids = $conn->fetchFirstColumn(
                    "SELECT r.id FROM registration r
                     INNER JOIN registration_meta m ON m.object_id = r.id
                     WHERE r.opportunity_id = ?
                       AND r.status > 0
                       AND m.key = 'previousPhaseRegistrationId'
                       AND m.value = ?",
                    [$exec_phase->id, (string) $last_phase_reg->id]
                );

                if (!$ids) {
                    continue;
                }

                $requests = $app->repo('Registration')->findBy(['id' => $ids]);

                if (!$requests) {
                    continue;
                }

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
            $this->import('opportunity-execution-requests');
            ?>
            <opportunity-execution-requests
                v-if="item.isExecutionPhase && getRegistration(lastPhase)?.status == 10"
                :registration="getRegistration(lastPhase)"
                :phase="item"
                :phases="phases">
            </opportunity-execution-requests>
            <?php
        });

        // ----------------------------------------------------------------
        // Endpoint: cria a fase de execução vinculada à oportunidade.
        // Cria atomicamente a Opportunity (coleta) + EvaluationMethodConfiguration
        // (tipo simple), seguindo o mesmo padrão de POST_reportingPhase.
        // ----------------------------------------------------------------
        $app->hook('POST(opportunity.createExecutionPhase)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */

            $opportunity = $this->requestedEntity;
            $opportunity->checkPermission('@control');

            // Garante que operamos sempre na oportunidade raiz
            $root = $opportunity->isOpportunityPhase ? $opportunity->firstPhase : $opportunity;

            // Verifica se já existe uma fase de execução
            foreach ($root->allPhases as $phase) {
                if ($phase->isExecutionPhase) {
                    $this->errorJson(i::__('Já existe uma fase de execução para esta oportunidade'), 403);
                    return;
                }
            }

            $data             = $this->data;
            $collection_data  = $data['collectionPhase'] ?? [];
            $evaluation_data  = $data['evaluationPhase']  ?? [];

            $class = $root->getSpecializedClassName();

            $execution_phase = new $class();
            $execution_phase->parent             = $root;
            $execution_phase->status             = Opportunity::STATUS_PHASE;
            $execution_phase->name               = $collection_data['name'] ?? i::__('Fase de Execução');
            $execution_phase->registrationFrom   = $collection_data['registrationFrom']['_date'] ?? null;
            $execution_phase->registrationTo     = $collection_data['registrationTo']['_date'] ?? null;
            $execution_phase->type               = $root->type;
            $execution_phase->ownerEntity        = $root->ownerEntity;
            $execution_phase->isOpportunityPhase = true;
            $execution_phase->isDataCollection   = true;
            $execution_phase->isExecutionPhase   = true;
            $execution_phase->registrationLimitPerOwner = 0;

            $evaluation_phase = new EvaluationMethodConfiguration();
            $evaluation_phase->opportunity    = $execution_phase;
            $evaluation_phase->type           = 'simple';
            $evaluation_phase->name           = $evaluation_data['name'] ?? i::__('Avaliação dos pedidos');
            $evaluation_phase->evaluationFrom = $evaluation_data['evaluationFrom']['_date'] ?? null;
            $evaluation_phase->evaluationTo   = $evaluation_data['evaluationTo']['_date'] ?? null;

            $collection_errors = $execution_phase->getValidationErrors();
            $evaluation_errors = $evaluation_phase->getValidationErrors();

            if (!empty($collection_errors) || !empty($evaluation_errors)) {
                $this->json([
                    'errors'           => true,
                    'collectionErrors' => $collection_errors,
                    'evaluationErrors' => $evaluation_errors,
                ], 400);
                return;
            }

            $execution_phase->save(true);
            $evaluation_phase->save(true);

            $root->executionPhase = $execution_phase->id;
            $root->save(true);

            $execution_phase->evaluationMethodConfiguration = $evaluation_phase;

            $this->json([
                'collectionPhase' => $execution_phase,
                'evaluationPhase' => $evaluation_phase,
            ]);
        });

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

        // ----------------------------------------------------------------
        // Endpoint: o agente abre um pedido (inscrição) na fase de execução.
        // Qualquer agente relacionado à inscrição aprovada pode abrir pedidos.
        // ----------------------------------------------------------------
        $app->hook('POST(opportunity.createExecutionRequest)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */

            $opportunity = $this->requestedEntity;
            $data        = $this->data;

            $first_phase     = $opportunity->firstPhase;
            $execution_phase = null;
            foreach ($first_phase->allPhases as $phase) {
                if ($phase->isExecutionPhase) {
                    $execution_phase = $phase;
                    break;
                }
            }

            if (!$execution_phase) {
                $this->errorJson(i::__('Fase de execução não encontrada'), 404);
            }

            // Recebe o ID da inscrição aprovada diretamente do frontend,
            // seguindo o mesmo padrão do createAppealPhaseRegistration.
            $registration_id = $data['registration_id'] ?? 0;

            if (!$registration_id) {
                $this->errorJson(i::__('ID da inscrição é obrigatório'), 400);
                return;
            }

            $approved_registration = $app->repo('Registration')->find($registration_id);

            if (!$approved_registration) {
                $this->errorJson(i::__('Inscrição não encontrada'), 404);
                return;
            }

            // Garante que a inscrição é da lastPhase desta oportunidade e está aprovada
            if ($approved_registration->opportunity->id !== $first_phase->lastPhase->id
                || $approved_registration->status !== Registration::STATUS_APPROVED) {
                $this->errorJson(i::__('A inscrição não está aprovada na publicação final do resultado'), 403);
                return;
            }

            // Garante que o usuário logado é dono ou agente relacionado da inscrição
            $profile = $app->user->profile;
            $is_owner = $approved_registration->owner->id === $profile->id;
            $is_related = false;
            foreach ($approved_registration->agentRelations as $ar) {
                if ($ar->agent->id === $profile->id) {
                    $is_related = true;
                    break;
                }
            }

            if (!$is_owner && !$is_related) {
                $this->errorJson(i::__('Você não tem permissão para abrir pedidos nesta inscrição'), 403);
                return;
            }

            $new_registration = new Registration();
            $new_registration->opportunity = $execution_phase;
            $new_registration->owner       = $approved_registration->owner;
            // Copia o number da inscrição aprovada — mesmo padrão do recurso.
            // O apiFindRegistrations filtra resultados por number entre fases:
            // sem isso, os pedidos nunca aparecem na lista do gestor.
            $new_registration->number = $approved_registration->number;
            // Vincula ao aprovado original para rastreabilidade
            $new_registration->previousPhaseRegistrationId = $approved_registration->id;

            $new_registration->save(true);

            $this->json($new_registration);
        });
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
