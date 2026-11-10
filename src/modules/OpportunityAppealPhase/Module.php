<?php

namespace OpportunityAppealPhase;

use MapasCulturais\App;
use MapasCulturais\Controllers;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\i;
use OpportunityAppealPhase\AppealReview\Notifier;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;

class Module extends \MapasCulturais\Module {

    function __construct($config = [])
    {
        $config += [
            'sendMailNotification.opportunityAppealPhase' => env('SEND_MAIL_OPPORTUNITY_APPEAL_PHASE', false),
            'featureFlag.appealTwoStagePublish' => env('APPEAL_TWO_STAGE_PUBLISH', false),
            'featureFlag.appealScoreCorrection' => env('APPEAL_SCORE_CORRECTION', false),
        ];

        parent::__construct($config);
    }

    public function _init() {
        $app = App::i();
        $self = $this;

        $app->hook('entity(RegistrationEvaluation).canUser(modify)', function ($user, &$result) use ($self) {
            /** @var RegistrationEvaluation $this */
            if ($result || $user->is('guest')) {
                return;
            }

            $result = $self->canDesignatedCorrectorAccessSlot($this, $user);
        });

        $app->hook('entity(RegistrationEvaluation).canUser(view)', function ($user, &$result) use ($self) {
            /** @var RegistrationEvaluation $this */
            if ($result || $user->is('guest')) {
                return;
            }

            $result = $self->canDesignatedCorrectorAccessSlot($this, $user);
        });

        // Corretor designado precisa ver o formulário da inscrição (mesmos campos
        // que o avaliador original) para refazer a análise e corrigir a nota.
        $app->hook('entity(Registration).canUser(view)', function ($user, &$result) use ($self) {
            /** @var Registration $this */
            if ($result || $user->is('guest')) {
                return;
            }

            $result = $self->canDesignatedCorrectorViewRegistration($this, $user);
        });

        $app->hook('entity(Registration).canUser(viewPrivateData)', function ($user, &$result) use ($self) {
            /** @var Registration $this */
            if ($result || $user->is('guest')) {
                return;
            }

            $result = $self->canDesignatedCorrectorViewRegistration($this, $user);
        });

        // Aba "Correções designadas" em Minhas avaliações: ponto de entrada do corretor
        $app->hook('component(panel--evaluations-tabs).tabs:end', function () use ($app, $self) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            if (!$self->isAppealScoreCorrectionEnabled() || $app->user->is('guest')) {
                return;
            }

            $assignments_count = $app->repo(RegistrationAppealReview::class)->count([
                'correctorUser' => $app->user->id,
            ]);

            if (!$assignments_count) {
                return;
            }

            $this->import('panel--appeal-correction-assignments');
            ?>
            <mc-tab v-if="!isReportingPhase" label="<?= i::esc_attr__('Correções designadas') ?>" slug="appealCorrections" class="panel-evaluations">
                <panel--appeal-correction-assignments></panel--appeal-correction-assignments>
            </mc-tab>
            <?php
        });

        // Painel de controle: bloco "Correções designadas" junto às avaliações disponíveis
        $app->hook('template(panel.index.panel-home-evaluations):after', function () use ($app, $self) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            if (!$self->isAppealScoreCorrectionEnabled() || $app->user->is('guest')) {
                return;
            }

            $assignments_count = $app->repo(RegistrationAppealReview::class)->count([
                'correctorUser' => $app->user->id,
            ]);

            if (!$assignments_count) {
                return;
            }

            $this->import('panel--appeal-correction-assignments');
            ?>
            <panel--appeal-correction-assignments
                carousel
                available-only
                hide-when-empty
                title="<?= i::esc_attr__('Correções designadas') ?>"
            ></panel--appeal-correction-assignments>
            <?php
        });

        // PR5 (issue #15): notifica corretor designado quando a designação é criada
        $app->hook('entity(OpportunityAppealPhase.Entities.RegistrationAppealReview).insert:after', function () {
            /** @var RegistrationAppealReview $this */
            Notifier::notifyDesignation($this);
        });

        /*
         * F4 (#20) — CA-11: colunas de nota original/corrigida/diferença.
         *
         * Propriedades computadas, expostas ao @select da ApiQuery (o parse
         * aceita chaves virtuais: processEntities as preenche com null e o
         * hook abaixo computa os valores). Sem persistência e sem schema.
         * A ordenação por essas colunas é ignorada pela ApiQuery (sem erro);
         * filtragem por elas não é suportada (PropertyDoesNotExists).
         */
        $app->hook('ApiQuery(registrationevaluation).findResult', function (&$result) use ($self) {
            $self->enrichEvaluationScoreColumns($result);
        });

        $app->hook('ApiQuery(registration).findResult', function (&$result) use ($self) {
            $self->enrichRegistrationScoreColumns($result);
            $self->enrichPreliminaryResultSnapshot($result);
        });

        $app->hook('entity(EvaluationMethodConfiguration).save:before', function() use ($self) {
            /** @var EvaluationMethodConfiguration $this */
            // Na criação, OpportunityPhases ainda pode mudar a oportunidade da avaliação.
            if (!$this->isNew()) {
                $self->syncAppealPhaseName($this->opportunity, $this->name);
            }
        });

        $app->hook('entity(Opportunity).save:before', function() use ($self) {
            /** @var Opportunity $this */
            if (!$this->isNew()) {
                $phase_name = $this->evaluationMethodConfiguration?->name ?? $this->name;
                $self->syncAppealPhaseName($this, $phase_name);
            }
        });

        /* Endpoint de criação de fase de recurso na oportunidade */
        $app->hook('POST(opportunity.createAppealPhase)', function() use ($app) {
            /** @var Controllers\Opportunity $this  */

            $opportunity = $this->requestedEntity;
            
            if($opportunity->firstPhase->isContinuousFlow) {
                $this->errorJson(i::__('Não é possível criar recurso em oportunidades com fluxo contínuo'), 404);
            }

            $opportunity->checkPermission('@control');

            $existing_appeal_phase = $app->repo('Opportunity')->findOneBy([
                'parent' => $opportunity->id,
                'status' => Opportunity::STATUS_APPEAL_PHASE,
            ]);

            if ($existing_appeal_phase) {
                $appeal_phase_meta = $app->repo('OpportunityMeta')->findOneBy([
                    'owner' => $opportunity,
                    'key' => 'appealPhase',
                ]);

                if ($appeal_phase_meta) {
                    $this->errorJson(sprintf(i::__('Já existe uma fase de recurso para %s'), $opportunity->name), 403);
                }

                $existing_appeal_phase->delete(true);
                $opportunity = $opportunity->refreshed();
            }

            $class_name = $opportunity->getSpecializedClassName();

            $phase_name = $opportunity->evaluationMethodConfiguration ?
                $opportunity->evaluationMethodConfiguration->name : $opportunity->name;
            $appeal_phase = new $class_name();
            $appeal_phase->parent = $opportunity;
            $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
            $appeal_phase->name = self::buildAppealPhaseName($phase_name);
            $appeal_phase->ownerEntity = $opportunity->ownerEntity;
            $appeal_phase->type = $opportunity->type;
            $appeal_phase->registrationCategories = $opportunity->registrationCategories;
            $appeal_phase->registrationRanges = $opportunity->registrationRanges;
            $appeal_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;
            $appeal_phase->isDataCollection = true;
            $appeal_phase->isAppealPhase = true;
            $appeal_phase->showPreviousPhaseEvaluationDetails = true;

            $conn = $app->conn;
            $conn->beginTransaction();

            try {
                $appeal_phase->save(true);

                $step = new RegistrationStep();
                $step->name = '';
                $step->opportunity = $appeal_phase;
                $step->save(true);

                $opportunity->appealPhase = $appeal_phase;
                $opportunity->save(true);

                $evaluationMethodConfiguration = new EvaluationMethodConfiguration();
                $evaluationMethodConfiguration->opportunity = $appeal_phase;
                $evaluationMethodConfiguration->type = 'continuous';
                $evaluationMethodConfiguration->publishEvaluationDetails = true;
                $evaluationMethodConfiguration->save(true);

                $appeal_phase->evaluationMethodConfiguration = $evaluationMethodConfiguration;

                $conn->commit();
            } catch (\Throwable $e) {
                if ($conn->isTransactionActive()) {
                    $conn->rollBack();
                }

                $orphan = $app->repo('Opportunity')->findOneBy([
                    'parent' => $opportunity->id,
                    'status' => Opportunity::STATUS_APPEAL_PHASE,
                ]);

                if ($orphan && !$app->repo('OpportunityMeta')->findOneBy([
                    'owner' => $opportunity,
                    'key' => 'appealPhase',
                ])) {
                    $conn->delete('registration_step', ['opportunity_id' => $orphan->id]);
                    $orphan->delete(true);
                }

                $app->em->clear();

                throw $e;
            }

            $this->json($appeal_phase);
        });

        /**
         * Endpoint para criação de inscrição na fase de recurso da oportunidade.
         *
         * @param int $registration_id
         */
        $app->hook('POST(opportunity.createAppealPhaseRegistration)', function() use ($app, $self) {
            /** @var Controllers\Opportunity $this  */

            try {
                $opportunity = $this->requestedEntity;
                $appeal_phase = $opportunity->appealPhase;

                $data = $this->data;
                $registration_id = $data['registration_id'] ?? 0;

                if (!$registration_id) {
                    $this->errorJson(i::__('ID da inscrição é obrigatório'), 400);
                }

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

                /*
                    R02 (#71) — decisão do dono 2026-09-22: o recurso é aberto
                    pela publicação do resultado PRELIMINAR. Espelha o gate
                    client-side (canShowAppeal → publishState.preliminary);
                    parse booleano da metadata registrada pelo módulo
                    (default false), como os demais gates do two-stage.
                */
                if (!(bool) $opportunity->publishedPreliminaryRegistrations) {
                    $this->errorJson(i::__('O resultado preliminar precisa estar publicado para solicitar recurso'), 403);
                }

                // Verifica se já existe inscrição de recurso com o mesmo number
                $existing_appeal = $app->repo('Registration')->findOneBy([
                    'opportunity' => $appeal_phase,
                    'number' => $registration->number
                ]);

                if ($existing_appeal) {
                    $this->json($existing_appeal);
                    return;
                }

                $new_registration = new \MapasCulturais\Entities\Registration();
                $new_registration->opportunity = $appeal_phase;
                $new_registration->category = $registration->category;
                $new_registration->proponentType = $registration->proponentType;
                $new_registration->range = $registration->range;
                $new_registration->owner = $registration->owner;
                $new_registration->number = $registration->number;
                
                $new_registration->save(true);

                // Cria notificação do sistema e disparo de e-mail para o proponente e gestores da oportunidade
                // Disparo para o proponente
                $registration_email = ($new_registration->owner->emailPrivado ??
                    $new_registration->owner->emailPublico ??
                    $new_registration->ownerUser->email);
                
                $self->sendEmail($opportunity, $new_registration, $registration_email, 'proponent');
                $self->sendSystemNotification($opportunity, $new_registration);
                
                // Disparo para os gestores da oportunidade
                $relations = $opportunity->getAgentRelations();
                foreach($relations as $relation) {
                    if($relation->group == 'group-admin') {
                        $user_email = ($relation->agent->emailPrivado ??
                            $relation->agent->emailPublico ??
                            $relation->agent->user->email);

                        $self->sendEmail($opportunity, $new_registration, $user_email, 'manager');
                        $self->sendSystemNotification($opportunity, $relation);
                    }
                }

                $this->json($new_registration);
            } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
                $this->errorJson($e->getMessage(), 403);
            }
        });

        // Envio de e-mail e notificação do sistema após o envio da inscrição
        $app->hook('entity(Registration).send:after', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $parent_phase = $opportunity->parent;
                $parent_registration = $app->repo('Registration')->findOneBy([
                    'opportunity' => $parent_phase,
                    'number' => $this->number,
                ]);

                if ($parent_registration && \OpportunityPhases\Module::appealPhaseAffectsSync($parent_phase)) {
                    \OpportunityPhases\Module::removeDownstreamRegistrations($parent_registration, $this);
                }

                // Disparo de e-mail para todos os avaliadores dessa fase de recurso
                $relations = $opportunity->evaluationMethodConfiguration->getAgentRelations();
                foreach($relations as $relation) {
                    $user_email = ($relation->agent->emailPrivado ??
                        $relation->agent->emailPublico ??
                        $relation->agent->user->email);

                    $self->sendEmail($opportunity, $this, $user_email, 'evaluator');
                    $self->sendSystemNotification($opportunity, $relation, true);
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para indeferido
        $app->hook('entity(Registration).status(notapproved)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->sendMailNewStatus($opportunity, $this);
                $self->sendNotificationNewStatus($opportunity, $this);
                if (\OpportunityPhases\Module::appealPhaseAffectsSync($opportunity->parent)) {
                    $self->enqueueNextMainPhaseSync($app, $this, $opportunity);
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para deferido
        $app->hook('entity(Registration).status(approved)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->sendMailNewStatus($opportunity, $this);
                $self->sendNotificationNewStatus($opportunity, $this);
                if (\OpportunityPhases\Module::appealPhaseAffectsSync($opportunity->parent)) {
                    $self->enqueueNextMainPhaseSync($app, $this, $opportunity);
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para negado
        $app->hook('entity(Registration).status(invalid)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->sendMailNewStatus($opportunity, $this);
                $self->sendNotificationNewStatus($opportunity, $this);
                if (\OpportunityPhases\Module::appealPhaseAffectsSync($opportunity->parent)) {
                    $self->enqueueNextMainPhaseSync($app, $this, $opportunity);
                }
            }
        });

        // Altera o texto de "Não avaliado" para "Aguardando resposta" na tabela de avaliações da fase de recursos
        $app->hook('component(opportunity.allEvaluations.opportunity-evaluations-table).texts', function(&$texts) {
            if($this->data['entity']->opportunity->isAppealPhase) {
                $texts['não avaliado'] = i::__('Aguardando resposta');
            }
            
        });

        /* PR4 — aplica correção de nota no slot designado (APPEAL_SCORE_CORRECTION) */
        $app->hook('POST(registrationEvaluation.applyAppealCorrection)', function () use ($app) {
            /** @var Controllers\RegistrationEvaluation $this */
            $this->requireAuthentication();

            if (!(bool) env('APPEAL_SCORE_CORRECTION', false)) {
                $this->errorJson(i::__('Correção de notas por recurso desabilitada'), 404);
            }

            $slot = $this->requestedEntity;
            if (!$slot) {
                $app->pass();
            }

            $review = RegistrationAppealReview::findActiveForEvaluationAndUser($slot, $app->user);
            if (!$review) {
                $this->errorJson(i::__('Nenhuma designação ativa de correção para esta avaliação'), 403);
            }

            $data = $this->data['evaluationData'] ?? $this->data['correctedValue'] ?? null;
            $draft = !empty($this->data['draft']);

            $service = new \OpportunityAppealPhase\AppealReview\Service();

            try {
                if ($draft) {
                    if ($data === null) {
                        $this->errorJson(i::__('Dados da correção são obrigatórios.'), 400);
                    }
                    $review = $service->saveDraft($review, $data);
                } else {
                    $review = $service->applyCorrection($review, $data);
                }
            } catch (\InvalidArgumentException $e) {
                $this->errorJson($e->getMessage(), 400);
            } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
                $this->errorJson(i::__('Sem permissão para aplicar a correção'), 403);
            }

            $this->json([
                'review' => $review,
                'evaluation' => $slot->refreshed(),
                'registration' => [
                    'id' => $slot->registration->id,
                    'consolidatedResult' => $slot->registration->refreshed()->consolidatedResult,
                ],
            ]);
        });
    }

    public function register() {
        $app = App::i();

        $app->registerController('appealCorrector', \OpportunityAppealPhase\Controllers\AppealCorrector::class);

        // PR6 (#40): API de designação de corretores por slot (criação + listagem do painel do F2).
        $app->registerController('registrationappealreview', \OpportunityAppealPhase\Controllers\RegistrationAppealReview::class);

        $this->registerOpportunityMetadata('appealPhase', [
            'label' => i::__('Fase de recurso'),
            'type'  => 'entity'
        ]);

        $this->registerOpportunityMetadata('isAppealPhase', [
            'label' => i::__('Indica se é uma fase de recurso'),
            'type'  => 'boolean'
        ]);

        $this->registerOpportunityMetadata('showPreviousPhaseEvaluationDetails', [
            'label' => i::__('Exibir detalhamento da avaliação anterior para avaliadores do recurso'),
            'type'  => 'boolean',
            'default' => true,
        ]);

        $this->registerOpportunityMetadata('appealPhaseAffectsSync', [
            'label' => i::__('Sincronizar inscrições para fase seguinte'),
            'type'  => 'boolean',
            'default' => false,
        ]);

        $this->registerOpportunityMetadata('publishedPreliminaryRegistrations', [
            'label' => i::__('Resultado preliminar das inscrições publicado'),
            'type'  => 'boolean',
            'default' => false,
            'private' => false,
        ]);

        // R02 (#64): snapshot do consolidado capturado no ato da publicação
        // preliminar (fidelidade histórica em todos os métodos). Formato =
        // getConsolidatedResult() por método (técnica: valor numérico;
        // documental: 1/-1; simples: código do status MIN; qualificação:
        // status do mapa). Gravado/limpo por Opportunity::publish/
        // unPublishPreliminaryRegistrations; legível pelo dono.
        $this->registerRegistrationMetadata('preliminaryResultSnapshot', [
            'label' => i::__('Snapshot do resultado preliminar (publicação em dois estágios)'),
            'type'  => 'string',
            'private' => false,
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

    public function isTwoStagePublishEnabled(): bool
    {
        return (bool) env('APPEAL_TWO_STAGE_PUBLISH', $this->config['featureFlag.appealTwoStagePublish']);
    }

    /**
     * F7 (#51): o acesso do corretor designado ao slot vale para TODOS os
     * métodos de avaliação (antes restrito ao técnico). Guardas mantidos:
     * feature flag, designação ativa do corretor para o slot (com inscrição,
     * fase de recurso e dono conferidos por findActiveForEvaluationAndUser),
     * janela de prazo e elegibilidade (eligibleCorrectors).
     */
    public function canDesignatedCorrectorAccessSlot(RegistrationEvaluation $slot, $user): bool
    {
        if (!$this->isAppealScoreCorrectionEnabled()) {
            return false;
        }

        $review = RegistrationAppealReview::findActiveForEvaluationAndUser($slot, $user);

        if (!$review) {
            return false;
        }

        foreach ($review->eligibleCorrectors($slot) as $eligible_user) {
            if ($eligible_user->equals($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Corretor com designação (ativa ou já enviada) na inscrição pode ver o
     * formulário — necessário para o embed `registrationevaluationtionformview`
     * na página de correção.
     */
    public function canDesignatedCorrectorViewRegistration(Registration $registration, $user): bool
    {
        if (!$this->isAppealScoreCorrectionEnabled() || $user->is('guest')) {
            return false;
        }

        $statuses = array_merge(
            RegistrationAppealReview::getActiveStatuses(),
            [RegistrationAppealReview::STATUS_SENT]
        );

        $count = App::i()->repo(RegistrationAppealReview::class)->count([
            'registration' => $registration,
            'correctorUser' => $user,
            'status' => $statuses,
        ]);

        return $count > 0;
    }

    // ============================================================ //
    // F4 (#20) — CA-11: propriedades computadas de nota
    // ============================================================ //

    /**
     * Colunas por slot (avaliação), computadas no findResult da ApiQuery:
     *
     * - originalScore: nota do slot ANTES da primeira correção aplicada
     *   (registration_appeal_review com status SENT, menor id); sem correção,
     *   é a própria nota vigente (snapshot originalScore da designação);
     * - correctedScore: nota vigente (result pós-correção in-place);
     * - scoreDifference: correctedScore − originalScore.
     *
     * Formato por método, derivado do `result` como as listas já exibem:
     * técnico = pontuação ponderada; documental = 1/−1 (válida/inválida);
     * simples = código do status. Resultados não numéricos → null.
     *
     * Só computa quando as chaves foram pedidas no @select (a chave existe,
     * null, nas linhas); queries que não pedem não pagam o custo.
     *
     * @param array<int, array<string, mixed>> $result
     */
    public function enrichEvaluationScoreColumns(array &$result): void
    {
        if (!$result) {
            return;
        }

        $wants_original = array_key_exists('originalScore', $result[0]);
        $wants_corrected = array_key_exists('correctedScore', $result[0]);
        $wants_difference = array_key_exists('scoreDifference', $result[0]);

        if (!$wants_original && !$wants_corrected && !$wants_difference) {
            return;
        }

        $evaluation_ids = [];
        foreach ($result as $row) {
            if (!empty($row['id'])) {
                $evaluation_ids[] = (int) $row['id'];
            }
        }

        $snapshot = $this->fetchCurrentAndOriginalScores($evaluation_ids);

        foreach ($result as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $current = $snapshot[$id]['current'] ?? null;
            $original = $snapshot[$id]['original'] ?? null;

            // Sem correção aplicada: original é a nota vigente.
            $original = $original ?? $current;

            if ($wants_original) {
                $row['originalScore'] = $original;
            }
            if ($wants_corrected) {
                $row['correctedScore'] = $current;
            }
            if ($wants_difference) {
                $row['scoreDifference'] = ($original !== null && $current !== null)
                    ? $current - $original
                    : null;
            }
        }
    }

    /**
     * R02 (#64): expõe o snapshot do resultado preliminar ao DONO quando o
     * @select pede `preliminaryResultSnapshot`. A metadata é registrada não
     * privada (o join EAV da própria ApiQuery já a resolve no @select); este
     * enrichment cobre caminhos em que a chave vem null/preenchida a partir
     * de leitura direta da metadata (mesma defesa do padrão F4 — só computa
     * quando a chave foi pedida).
     *
     * @param array<int, array<string, mixed>> $result
     */
    public function enrichPreliminaryResultSnapshot(array &$result): void
    {
        if (!$result || !array_key_exists('preliminaryResultSnapshot', $result[0])) {
            return;
        }

        $app = App::i();
        $registration_ids = [];

        foreach ($result as $row) {
            if (!empty($row['id'])) {
                $registration_ids[] = (int) $row['id'];
            }
        }

        if (!$registration_ids) {
            return;
        }

        $metas = $app->repo('RegistrationMeta')->findBy([
            'owner' => $registration_ids,
            'key' => 'preliminaryResultSnapshot',
        ]);

        $snapshot_by_owner = [];
        foreach ($metas as $meta) {
            $snapshot_by_owner[(int) $meta->owner->id] = $meta->value;
        }

        foreach ($result as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['preliminaryResultSnapshot'] = $snapshot_by_owner[$id] ?? null;
        }
    }

    /**
     * Colunas por inscrição (lista de inscritos), computadas no findResult:
     *
     * - averageCorrectedScore: média das notas vigentes dos slots ENVIADOS
     *   (mesma base do getSentEvaluations — consolidação vigente);
     * - averageOriginalScore: média recomputada trocando, em cada slot
     *   corrigido, a nota vigente pela originalScore da designação aplicada;
     * - scoreDifference: média corrigida − média original (sem nenhuma
     *   correção: médias iguais, diferença 0; sem notas numéricas: null).
     *
     * @param array<int, array<string, mixed>> $result
     */
    public function enrichRegistrationScoreColumns(array &$result): void
    {
        if (!$result) {
            return;
        }

        $wants_original = array_key_exists('averageOriginalScore', $result[0]);
        $wants_corrected = array_key_exists('averageCorrectedScore', $result[0]);
        $wants_difference = array_key_exists('scoreDifference', $result[0]);

        if (!$wants_original && !$wants_corrected && !$wants_difference) {
            return;
        }

        $registration_ids = [];
        foreach ($result as $row) {
            if (!empty($row['id'])) {
                $registration_ids[] = (int) $row['id'];
            }
        }

        // Linha da fase de recurso: as notas (e correções) estão na inscrição
        // de mesmo número na fase principal, não na avaliação do recurso.
        $score_source = $this->mapAppealToMainRegistrations($registration_ids);

        $rows = $this->fetchScoresByRegistration(array_values(array_unique(array_merge(
            $registration_ids,
            array_values($score_source)
        ))));

        $by_registration = [];
        foreach ($rows as $row) {
            $by_registration[(int) $row['registration_id']][] = $row;
        }

        $average = static function (array $values): ?float {
            $numeric = array_values(array_filter($values, static fn ($v) => $v !== null));

            return $numeric ? round(array_sum($numeric) / count($numeric), 4) : null;
        };

        foreach ($result as &$row) {
            $registration_id = (int) ($row['id'] ?? 0);
            $source_id = $score_source[$registration_id] ?? $registration_id;
            $evaluations = $by_registration[$source_id] ?? [];

            $corrected_values = [];
            $original_values = [];

            foreach ($evaluations as $evaluation) {
                $current = $evaluation['current'];
                $original = $evaluation['original'] ?? $current;

                $corrected_values[] = $current;
                $original_values[] = $original;
            }

            $average_corrected = $average($corrected_values);
            $average_original = $average($original_values);

            if ($wants_original) {
                $row['averageOriginalScore'] = $average_original;
            }
            if ($wants_corrected) {
                $row['averageCorrectedScore'] = $average_corrected;
            }
            if ($wants_difference) {
                $row['scoreDifference'] = ($average_original !== null && $average_corrected !== null)
                    ? round($average_corrected - $average_original, 4)
                    : null;
            }
        }
    }

    /**
     * Nota vigente e nota original (primeira correção aplicada) por avaliação.
     *
     * @param int[] $evaluation_ids
     * @return array<int, array{current: ?float, original: ?float}>
     */
    private function fetchCurrentAndOriginalScores(array $evaluation_ids): array
    {
        if (!$evaluation_ids) {
            return [];
        }

        $app = App::i();
        $ids = implode(',', array_map('intval', $evaluation_ids));

        // sent_timestamp preenchido = correção aplicada (inclui designação
        // reaberta após envio); menor id = primeira correção do slot.
        $rows = $app->em->getConnection()->fetchAllAssociative("
            SELECT
                ev.id,
                ev.result,
                (
                    SELECT r.original_score
                    FROM registration_appeal_review r
                    WHERE r.original_evaluation_id = ev.id AND r.sent_timestamp IS NOT NULL
                    ORDER BY r.id ASC
                    LIMIT 1
                ) AS original_score
            FROM registration_evaluation ev
            WHERE ev.id IN ({$ids})
        ");

        $snapshot = [];
        foreach ($rows as $row) {
            $snapshot[(int) $row['id']] = [
                'current' => $this->toNumericScore($row['result']),
                'original' => $this->toNumericScore($row['original_score']),
            ];
        }

        return $snapshot;
    }

    /**
     * Para inscrições de fase de recurso, a inscrição de mesmo número na fase
     * principal (oportunidade cujo metadado `appealPhase` aponta para a fase
     * de recurso, serializado como "<classe>:<id>").
     *
     * @param int[] $registration_ids
     * @return array<int, int> id da inscrição do recurso => id da inscrição principal
     */
    private function mapAppealToMainRegistrations(array $registration_ids): array
    {
        if (!$registration_ids) {
            return [];
        }

        $app = App::i();
        $ids = implode(',', array_map('intval', $registration_ids));

        $rows = $app->em->getConnection()->fetchAllAssociative("
            SELECT appeal_reg.id AS appeal_id, main_reg.id AS main_id
            FROM registration appeal_reg
            JOIN opportunity_meta om
                ON om.key = 'appealPhase'
                AND split_part(om.value, ':', 2) = appeal_reg.opportunity_id::text
            JOIN registration main_reg
                ON main_reg.opportunity_id = om.object_id
                AND main_reg.number = appeal_reg.number
            WHERE appeal_reg.id IN ({$ids})
        ");

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['appeal_id']] = (int) $row['main_id'];
        }

        return $map;
    }

    /**
     * Notas por inscrição, apenas avaliações ENVIADAS (ev.status = 2 =
     * RegistrationEvaluation::STATUS_SENT — mesma base de getSentEvaluations).
     *
     * @param int[] $registration_ids
     * @return array<int, array<string, mixed>>
     */
    private function fetchScoresByRegistration(array $registration_ids): array
    {
        if (!$registration_ids) {
            return [];
        }

        $app = App::i();
        $ids = implode(',', array_map('intval', $registration_ids));

        return $app->em->getConnection()->fetchAllAssociative("
            SELECT
                ev.registration_id,
                ev.result AS current,
                (
                    SELECT r.original_score
                    FROM registration_appeal_review r
                    WHERE r.original_evaluation_id = ev.id AND r.sent_timestamp IS NOT NULL
                    ORDER BY r.id ASC
                    LIMIT 1
                ) AS original
            FROM registration_evaluation ev
            WHERE ev.registration_id IN ({$ids})
                AND ev.status = 2
        ");
    }

    /**
     * Resultado do slot como número (técnico: pontuação; documental: 1/−1;
     * simples: código do status). Não numérico → null.
     */
    private function toNumericScore(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function isAppealScoreCorrectionEnabled(): bool
    {
        return (bool) env('APPEAL_SCORE_CORRECTION', $this->config['featureFlag.appealScoreCorrection']);
    }

    /**
     * Mantém o formato automático, inclusive o legado, e preserva títulos fora desses formatos.
     * Sem nome anterior, usa o formato atual de criação do recurso.
     */
    public static function buildAppealPhaseName(string $phase_name, ?string $previous_name = null): ?string
    {
        $templates = $previous_name === null ? [i::__('Recurso para %s')] : array_unique([
            i::__('Recurso para %s'),
            i::__('Fase de recurso para %s'),
            'Recurso para %s',
            'Fase de recurso para %s',
        ]);

        foreach ($templates as $template) {
            [$prefix, $suffix] = explode('%s', $template, 2);
            if ($previous_name !== null && (!str_starts_with($previous_name, $prefix) || !str_ends_with($previous_name, $suffix))) {
                continue;
            }

            return mb_substr(sprintf($template, $phase_name), 0, 255, 'UTF-8');
        }

        return null;
    }

    public function syncAppealPhaseName(Opportunity $opportunity, string $phase_name): void
    {
        if ($opportunity->isAppealPhase || $opportunity->status === Opportunity::STATUS_APPEAL_PHASE) {
            return;
        }

        $appeal_phase = $opportunity->appealPhase;
        if (!$appeal_phase || $appeal_phase->status !== Opportunity::STATUS_APPEAL_PHASE || !$appeal_phase->parent?->equals($opportunity)) {
            return;
        }

        $name = self::buildAppealPhaseName($phase_name, $appeal_phase->name);
        if ($name === null || $name === $appeal_phase->name) {
            return;
        }

        $appeal_phase->checkPermission('modify');
        // A entidade já está gerenciada pelo Doctrine. O flush do pai persiste o nome
        // sem executar novamente os hooks de save do recurso, que reagendam jobs.
        $appeal_phase->name = $name;
    }

    /**
     * Sincroniza a inscrição com a próxima fase principal do edital após mudança de status no recurso.
     * Não altera o status na fase avaliativa de origem; a importação considera o deferimento no recurso.
     */
    function enqueueNextMainPhaseSync(App $app, Registration $registration, Opportunity $appeal_phase): void
    {
        $parent_phase = $appeal_phase->parent;
        if (!$parent_phase) {
            return;
        }

        $next_main_phase = \OpportunityPhases\Module::getNextMainPhase($parent_phase);
        if (!$next_main_phase) {
            return;
        }

        $parent_registration = $app->repo('Registration')->findOneBy([
            'opportunity' => $parent_phase,
            'number' => $registration->number,
        ]);

        if ($parent_registration) {
            $next_main_phase->enqueueRegistrationSync([$parent_registration]);
        }
    }

    /**
     * Envia e-mail para o proponente e gestores da oportunidade
     *
     * @param Opportunity $opportunity
     * @param Registration $registration
     * @param string|null $email
     * @param string $recipientType
     */
    function sendEmail(Opportunity $opportunity, Registration $registration, ?string $email, string $recipientType = 'manager') {
        if(!$this->config['sendMailNotification.opportunityAppealPhase']) {
            return;
        }

        $app = App::i();

        if (!$email) {
            return;
        }
       
        $template = "opportunityappealphase/appeal-phase.html";
        $appeal_phase = $registration->opportunity;
        $original_opportunity = $appeal_phase->parent ?: $opportunity;
        $is_evaluator = $recipientType === 'evaluator';

        $subject = $is_evaluator ? sprintf(i::__("Aviso sobre uma nova avaliação de recurso em %s"), $appeal_phase->name) : sprintf(i::__("Aviso sobre um novo recurso em %s"), $appeal_phase->name);
        $message = $is_evaluator ? sprintf(i::__("Um novo recurso para avaliação foi gerado em %s"), $appeal_phase->name) : sprintf(i::__("Uma nova solicitação de recurso foi feita em %s"), $appeal_phase->name);
        
        $params = [
            "siteName" => $app->siteName,
            "message" => $message,
            "requesterName" => $registration->owner->name,
            "registrationId" => $registration->id,
            "registrationNumber" => $registration->number,
            "registrationUrl" => $registration->singleUrl,
            "opportunityName" => $original_opportunity->name,
            "opportunityUrl" => $original_opportunity->singleUrl,
            "phaseName" => $appeal_phase->name,
            "phaseUrl" => $appeal_phase->singleUrl,
            "isEvaluator" => $is_evaluator,
            "isProponent" => $recipientType === 'proponent',
            "isManager" => $recipientType === 'manager',
        ];
        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => $email,
            "subject" => $subject,
            "body" => $app->renderMustacheTemplate($template, $params)
        ];
        $app->createAndSendMailMessage($email_params);
    }
    /**
     * Envia e-mail para o proponente sobre a alteração de status
     *
     * @param Opportunity $opportunity
     * @param Registration $registration
     */
    function sendMailNewStatus(Opportunity $opportunity, Registration $registration) {
        if(!$this->config['sendMailNotification.opportunityAppealPhase']) {
            return;
        }

        $app = App::i();

        $template = "opportunityappealphase/update-status.html";
        $original_opportunity = $opportunity->parent ?: $opportunity;
        $params = [
            "siteName" => $app->siteName,
            "user" => $registration->owner->name,
            "opportunityTitle" => $original_opportunity->name,
            "opportunityUrl" => $original_opportunity->singleUrl,
            "phaseTitle" => $opportunity->name,
            "registrationId" => $registration->id,
            "registrationNumber" => $registration->number,
            "registrationUrl" => $registration->singleUrl,
            "statusTitle" => $registration->getStatusNameById($registration->status),
        ];
        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => ($registration->owner->emailPrivado ??
                        $registration->owner->emailPublico ??
                        $registration->ownerUser->email),
            "subject" => sprintf(i::__("Aviso sobre a mudança do seu status no(a) " .
                            "%s"),
                            $opportunity->name),
            "body" => $app->renderMustacheTemplate($template, $params)
        ];
        if (!isset($email_params["to"])) {
            return;
        }
        $app->createAndSendMailMessage($email_params);
    }

    /**
     * Envia notificação do sistema para o proponente e gestores da oportunidade
     *
     * @param Opportunity $opportunity
     * @param $recipient
     * @param bool $evaluator
     */
    function sendSystemNotification(Opportunity $opportunity, $recipient, $evaluator = false) {
        $message = $evaluator ? sprintf(i::__('Um novo recurso para avaliação foi gerado em %s'), $opportunity->name) : sprintf(i::__('Uma nova solicitação de recurso foi feita em %s'), $opportunity->name);

        $notification = new Notification;
        $notification->user = $recipient->ownerUser;
        $notification->message = $message;
        $notification->save(true);
    }
    
    /**
     * Envia notificação do sistema para o proponente sobre a alteração de status
     *
     * @param Opportunity $opportunity
     * @param $recipient
     */
    function sendNotificationNewStatus(Opportunity $opportunity, $recipient) {
        $message = sprintf(i::__('O status da sua inscrição na fase de recurso %s foi alterado.'), $opportunity->name);

        $notification = new Notification;
        $notification->user = $recipient->ownerUser;
        $notification->message = $message;
        $notification->save(true);
    }
}
