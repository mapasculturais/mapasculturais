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
    /**
     * Categorias pré-definidas oferecidas ao gestor ao criar a fase.
     * O gestor pode mantê-las, removê-las ou acrescentar outras.
     */
    const DEFAULT_CATEGORIES = [
        'Alteração de planilha orçamentária',
        'Alteração de ficha técnica',
        'Aprovação de logomarca',
        'Alteração de projeto',
        'Prorrogação',
    ];

    public function _init()
    {
        $app = App::i();

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
            $execution_phase->registrationCategories = !empty($data['categories'])
                ? $data['categories']
                : self::DEFAULT_CATEGORIES;

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

            $profile = $app->user->profile;

            // Busca a inscrição aprovada: como owner OU como agente relacionado.
            // Necessário para cobrir coletivos/organizações onde o usuário logado
            // é membro relacionado, não o owner direto da inscrição.
            $dql = "
                SELECT r
                FROM MapasCulturais\Entities\Registration r
                LEFT JOIN r.__agentRelations ar
                WHERE
                    r.opportunity = :last_phase AND
                    r.status      = :approved   AND
                    (r.owner = :profile OR ar.agent = :profile)
            ";
            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'last_phase' => $opportunity->lastPhase,
                'approved'   => Registration::STATUS_APPROVED,
                'profile'    => $profile,
            ]);
            $query->setMaxResults(1);
            $approved_registration = $query->getOneOrNullResult();

            if (!$approved_registration) {
                $this->errorJson(i::__('Inscrição aprovada não encontrada para este agente'), 403);
            }

            $category = $data['category'] ?? null;

            $pedido = new Registration();
            $pedido->opportunity = $execution_phase;
            $pedido->owner       = $approved_registration->owner;
            $pedido->category    = $category;
            // Vincula ao aprovado original para rastreabilidade
            $pedido->previousPhaseRegistrationId = $approved_registration->id;

            $pedido->save(true);

            $this->json($pedido);
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
