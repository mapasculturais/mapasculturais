<?php

namespace OpportunityExecution;

use MapasCulturais\App;
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
        // Endpoint: cria a fase de execução vinculada à oportunidade.
        // ----------------------------------------------------------------
        $app->hook('POST(opportunity.createExecutionPhase)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */

            $opportunity = $this->requestedEntity;
            $opportunity->checkPermission('@control');

            // Garante que operamos sempre na oportunidade raiz
            $root = $opportunity->isOpportunityPhase ? $opportunity->firstPhase : $opportunity;

            // Verifica se já existe uma fase de execução
            $existing = null;
            foreach ($root->allPhases as $phase) {
                if ($phase->isExecutionPhase) {
                    $existing = $phase;
                    break;
                }
            }

            if ($existing) {
                $this->errorJson(i::__('Já existe uma fase de execução para esta oportunidade'), 403);
            }

            $data = $this->data;
            $class = $opportunity->getSpecializedClassName();

            $execution_phase = new $class();
            $execution_phase->parent             = $opportunity;
            $execution_phase->status             = Opportunity::STATUS_PHASE; // -1
            $execution_phase->name               = $data['name'] ?? i::__('Fase de Execução');
            $execution_phase->type               = $opportunity->type;
            $execution_phase->ownerEntity        = $opportunity->ownerEntity;
            $execution_phase->isOpportunityPhase = true;
            $execution_phase->isDataCollection   = true;
            $execution_phase->isExecutionPhase   = true;
            // Zera o limite por agente para permitir N pedidos por agente
            $execution_phase->registrationLimitPerOwner = 0;
            // Pré-popula com categorias padrão; o gestor pode editar antes de publicar
            $execution_phase->registrationCategories = !empty($data['categories'])
                ? $data['categories']
                : self::DEFAULT_CATEGORIES;

            $execution_phase->save(true);

            $root->executionPhase = $execution_phase->id;
            $root->save(true);

            $this->json($execution_phase);
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
