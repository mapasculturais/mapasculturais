<?php

namespace OpportunityAppealPhase;

use MapasCulturais\App;
use MapasCulturais\Controllers;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    public function _init() {
        $app = App::i();
        $self = $this;

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
            $appeal_phase->name = sprintf(i::__('Recurso para %s'), $phase_name);
            $appeal_phase->ownerEntity = $opportunity->ownerEntity;
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

                \OpportunityPhases\Module::removeDownstreamRegistrations($registration);

                // Disparo de comunicação condicional: só executa quando o fluxo
                // "appealCreated" está habilitado na fase de recurso (filha).
                // A criação da inscrição e a limpeza downstream permanecem incondicionais.
                if ($self->isFlowEnabled($appeal_phase, 'appealCreated')) {
                    // Cria notificação do sistema e disparo de e-mail para o proponente e gestores da oportunidade
                    // Disparo para o proponente
                    $registration_email = ($new_registration->owner->emailPrivado ??
                        $new_registration->owner->emailPublico ??
                        $new_registration->ownerUser->email);
                    
                    $self->sendEmail($opportunity, $new_registration, $registration_email, 'proponent');
                    $self->sendSystemNotification($opportunity, $new_registration, false, 'appealCreated');
                    
                    // Disparo para os gestores da oportunidade
                    $relations = $opportunity->getAgentRelations();
                    foreach($relations as $relation) {
                        if($relation->group == 'group-admin') {
                            $user_email = ($relation->agent->emailPrivado ??
                                $relation->agent->emailPublico ??
                                $relation->agent->user->email);

                            $self->sendEmail($opportunity, $new_registration, $user_email, 'manager');
                            $self->sendSystemNotification($opportunity, $relation, false, 'appealCreated');
                        }
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

                // Disparo de comunicação condicional: só notifica avaliadores
                // quando o fluxo "appealSent" está habilitado na fase de recurso.
                if ($self->isFlowEnabled($opportunity, 'appealSent')) {
                    // Disparo de e-mail para todos os avaliadores dessa fase de recurso
                    $relations = $opportunity->evaluationMethodConfiguration->getAgentRelations();
                    foreach($relations as $relation) {
                        $user_email = ($relation->agent->emailPrivado ??
                            $relation->agent->emailPublico ??
                            $relation->agent->user->email);

                        $self->sendEmail($opportunity, $this, $user_email, 'evaluator');
                        $self->sendSystemNotification($opportunity, $relation, true, 'appealSent');
                    }
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para indeferido
        $app->hook('entity(Registration).status(notapproved)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                // SEMPRE executa: sincronização com a próxima fase principal do edital.
                // Não pode ser guardada pelo toggle de comunicação (risco de quebrar o encadeamento).
                $self->enqueueNextMainPhaseSync($app, $this, $opportunity);

                // CONDICIONAL: comunicação só dispara quando o fluxo está habilitado.
                if ($self->isFlowEnabled($opportunity, 'statusNotApproved')) {
                    $self->sendMailNewStatus($opportunity, $this, 'statusNotApproved');
                    $self->sendNotificationNewStatus($opportunity, $this, 'statusNotApproved');
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para deferido
        $app->hook('entity(Registration).status(approved)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->enqueueNextMainPhaseSync($app, $this, $opportunity);

                if ($self->isFlowEnabled($opportunity, 'statusApproved')) {
                    $self->sendMailNewStatus($opportunity, $this, 'statusApproved');
                    $self->sendNotificationNewStatus($opportunity, $this, 'statusApproved');
                }
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para negado
        $app->hook('entity(Registration).status(invalid)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->enqueueNextMainPhaseSync($app, $this, $opportunity);

                if ($self->isFlowEnabled($opportunity, 'statusInvalid')) {
                    $self->sendMailNewStatus($opportunity, $this, 'statusInvalid');
                    $self->sendNotificationNewStatus($opportunity, $this, 'statusInvalid');
                }
            }
        });

        // Altera o texto de "Não avaliado" para "Aguardando resposta" na tabela de avaliações da fase de recursos
        $app->hook('component(opportunity.allEvaluations.opportunity-evaluations-table).texts', function(&$texts) {
            if($this->data['entity']->opportunity->isAppealPhase) {
                $texts['não avaliado'] = i::__('Aguardando resposta');
            }
            
        });

        /**
         * Endpoint de escrita da configuração de notificações da fase de recurso.
         *
         * Aceita apenas as 15 chaves conhecidas (allowlist explícita); nunca
         * espelha $this->data diretamente sobre a entidade. Sanitiza CRLF em
         * subjects e força coercão booleana em enableds. Exige permissão @control.
         */
        $app->hook('POST(opportunity.saveAppealNotifyConfig)', function() use ($app, $self) {
            /** @var Controllers\Opportunity $this */

            try {
                $entity = $this->requestedEntity;

                if (!$entity) {
                    $this->errorJson(i::__('Entidade não encontrada.'), 404);
                }

                // Permissão obrigatória em escrita.
                $entity->checkPermission('@control');

                $data = $this->data ?? [];
                $crlf_search = ["\r", "\n", "%0A", "%0D", "\0"];

                foreach (Module::NOTIFY_FLOWS as $flow) {
                    $enabled_key = 'appealNotify_' . $flow . '_enabled';
                    $subject_key = 'appealNotify_' . $flow . '_subject';
                    $message_key = 'appealNotify_' . $flow . '_message';

                    // Coerção booleana explícita (apenas '1'/true/'true'/'on' -> true).
                    if (array_key_exists($enabled_key, $data)) {
                        $raw = $data[$enabled_key];
                        $entity->{$enabled_key} = ($raw === true || $raw === 1 || $raw === '1' || $raw === 'true' || $raw === 'on');
                    }

                    // Subject: sanitização CRLF obrigatória; null/'' -> null (recua ao fallback).
                    if (array_key_exists($subject_key, $data)) {
                        $raw = $data[$subject_key];
                        if ($raw === null || $raw === '') {
                            $entity->{$subject_key} = null;
                        } else {
                            $entity->{$subject_key} = str_replace($crlf_search, '', (string) $raw);
                        }
                    }

                    // Message: texto livre; null/'' -> null (recua ao fallback).
                    if (array_key_exists($message_key, $data)) {
                        $raw = $data[$message_key];
                        if ($raw === null || $raw === '') {
                            $entity->{$message_key} = null;
                        } else {
                            $entity->{$message_key} = (string) $raw;
                        }
                    }
                }

                $entity->save(true);

                $this->json([
                    'success' => true,
                    'entity' => $entity->simplify('id,name'),
                ]);
            } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
                $this->errorJson($e->getMessage(), 403);
            } catch (\Throwable $e) {
                $app->log->error('OpportunityAppealPhase::saveAppealNotifyConfig: ' . $e->getMessage());
                $this->errorJson(i::__('Erro ao salvar a configuração de notificações.'), 500);
            }
        });

        /**
         * Expõe ao frontend a lista de variáveis Mustache disponíveis em cada fluxo,
         * alimentando o painel de chips do modal de personalização.
         * Popula $MAPAS.config.opportunityAppealPhaseNotifyVariables.
         */
        $app->hook('mapas.printJsObject:before', function() use ($app) {
            /** @var \MapasCulturais\Theme $this */
            $this->jsObject['config']['opportunityAppealPhaseNotifyVariables'] = Module::getNotifyVariables();
        });
    }

    public function register() {
        $app = App::i();

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

        // 15 metadata keys para controle granular dos 5 fluxos de comunicação.
        // Todas com private => true: a leitura/escrita exige @control.
        // Padrão desligado: enabled=false, subject/message=null (recua ao fallback i18n).
        // Sem migration SQL — fases existentes nascem "tudo desligado" via fallback.
        foreach (self::NOTIFY_FLOWS as $flow) {
            $this->registerOpportunityMetadata('appealNotify_' . $flow . '_enabled', [
                'label'  => i::__('Notificações habilitadas para o fluxo'),
                'type'   => 'boolean',
                'default' => false,
                'private' => true,
            ]);

            $this->registerOpportunityMetadata('appealNotify_' . $flow . '_subject', [
                'label'   => i::__('Assunto customizado do e-mail'),
                'type'    => 'string',
                'private' => true,
            ]);

            $this->registerOpportunityMetadata('appealNotify_' . $flow . '_message', [
                'label'   => i::__('Mensagem customizada (e-mail e notificação do sistema)'),
                'type'    => 'text',
                'private' => true,
            ]);
        }
    }

    /**
     * Lista de variáveis Mustache disponíveis por fluxo, exposta ao frontend
     * via $MAPAS.config.opportunityAppealPhaseNotifyVariables. Espelha exatamente
     * o $params usado em sendEmail()/sendMailNewStatus().
     */
    public static function getNotifyVariables(): array
    {
        // Fluxos appealCreated/appealSent: variáveis do template appeal-phase.html
        $registration_flow_vars = [
            ['key' => 'siteName',          'label' => i::__('Nome do site')],
            ['key' => 'message',           'label' => i::__('Mensagem do disparo')],
            ['key' => 'requesterName',     'label' => i::__('Nome do solicitante')],
            ['key' => 'registrationId',    'label' => i::__('ID da inscrição')],
            ['key' => 'registrationNumber', 'label' => i::__('Número da inscrição')],
            ['key' => 'registrationUrl',   'label' => i::__('URL da inscrição')],
            ['key' => 'opportunityName',   'label' => i::__('Nome da oportunidade')],
            ['key' => 'opportunityUrl',    'label' => i::__('URL da oportunidade')],
            ['key' => 'phaseName',         'label' => i::__('Nome da fase')],
            ['key' => 'phaseUrl',          'label' => i::__('URL da fase')],
        ];

        // Fluxos status*: variáveis do template update-status.html
        $status_flow_vars = [
            ['key' => 'siteName',          'label' => i::__('Nome do site')],
            ['key' => 'user',              'label' => i::__('Nome do usuário')],
            ['key' => 'opportunityTitle',  'label' => i::__('Título da oportunidade')],
            ['key' => 'opportunityUrl',    'label' => i::__('URL da oportunidade')],
            ['key' => 'phaseTitle',        'label' => i::__('Título da fase')],
            ['key' => 'registrationId',    'label' => i::__('ID da inscrição')],
            ['key' => 'registrationNumber', 'label' => i::__('Número da inscrição')],
            ['key' => 'registrationUrl',   'label' => i::__('URL da inscrição')],
            ['key' => 'statusTitle',       'label' => i::__('Título do status')],
            ['key' => 'message',           'label' => i::__('Mensagem do disparo')],
        ];

        return [
            'appealCreated'      => $registration_flow_vars,
            'appealSent'         => $registration_flow_vars,
            'statusNotApproved'  => $status_flow_vars,
            'statusApproved'     => $status_flow_vars,
            'statusInvalid'      => $status_flow_vars,
        ];
    }

    /**
     * Flow IDs suportados pelo módulo, na ordem canônica usada pela UI.
     */
    const NOTIFY_FLOWS = [
        'appealCreated',
        'appealSent',
        'statusNotApproved',
        'statusApproved',
        'statusInvalid',
    ];

    /**
     * Verifica se um fluxo de notificação está habilitado na fase de recurso.
     *
     * Única porta de decisão para qualquer disparo de e-mail/notificação.
     * Trata `null` (fase existente que nunca teve a flag escrita) como `false`,
     * alinhado ao `default_value => false` dos metadados.
     */
    function isFlowEnabled(Opportunity $appealPhase, string $flow): bool
    {
        $value = $appealPhase->{'appealNotify_' . $flow . '_enabled'};

        if ($value === null) {
            return false;
        }

        return $value === true || $value === '1';
    }

    /**
     * Resolve o texto (subject ou message) de um fluxo: usa o valor customizado
     * quando definido e não-vazio; caso contrário, recua para o fallback recebido.
     *
     * Sanitização CRLF obrigatória em subjects (defesa em profundidade contra
     * e-mail header injection), aplicada tanto ao texto customizado quanto ao
     * fallback padrão.
     *
     * @param string $field 'subject' ou 'message'
     * @param callable $fallback Reproduz o sprintf(i::__(...)) padrão atual.
     */
    function resolveText(Opportunity $appealPhase, string $flow, string $field, callable $fallback): string
    {
        $value = $appealPhase->{'appealNotify_' . $flow . '_' . $field};

        if ($value !== null && trim((string) $value) !== '') {
            $resolved = (string) $value;
        } else {
            $resolved = (string) call_user_func($fallback);
        }

        // Defesa em profundidade: subjects nunca podem conter quebras de linha.
        if ($field === 'subject') {
            $resolved = str_replace(
                ["\r", "\n", "%0A", "%0D", "\0"],
                '',
                $resolved
            );
        }

        return $resolved;
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
        $app = App::i();

        if (!$email) {
            return;
        }
       
        $template = "opportunityappealphase/appeal-phase.html";
        $appeal_phase = $registration->opportunity;
        $original_opportunity = $appeal_phase->parent ?: $opportunity;
        $is_evaluator = $recipientType === 'evaluator';

        // A configuração de notificação vive na fase de recurso (filha), nunca no pai.
        // O flow ID é determinado pelo destinatário: avaliadores usam appealSent;
        // proponentes e gestores usam appealCreated.
        $flow = $is_evaluator ? 'appealSent' : 'appealCreated';

        $subject = $this->resolveText(
            $appeal_phase,
            $flow,
            'subject',
            $is_evaluator
                ? static fn() => sprintf(i::__("Aviso sobre uma nova avaliação de recurso em %s"), $appeal_phase->name)
                : static fn() => sprintf(i::__("Aviso sobre um novo recurso em %s"), $appeal_phase->name)
        );

        $message = $this->resolveText(
            $appeal_phase,
            $flow,
            'message',
            $is_evaluator
                ? static fn() => sprintf(i::__("Um novo recurso para avaliação foi gerado em %s"), $appeal_phase->name)
                : static fn() => sprintf(i::__("Uma nova solicitação de recurso foi feita em %s"), $appeal_phase->name)
        );
        
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
     * @param string $flow Flow ID ({@see self::NOTIFY_FLOWS}) usado para resolver
     *                     textos customizados; deve ser um dos fluxos status*.
     */
    function sendMailNewStatus(Opportunity $opportunity, Registration $registration, string $flow) {
        $app = App::i();

        $template = "opportunityappealphase/update-status.html";
        $original_opportunity = $opportunity->parent ?: $opportunity;

        $subject = $this->resolveText(
            $opportunity,
            $flow,
            'subject',
            static fn() => sprintf(
                i::__("Aviso sobre a mudança do seu status no(a) %s"),
                $opportunity->name
            )
        );

        $message = $this->resolveText(
            $opportunity,
            $flow,
            'message',
            static fn() => sprintf(
                i::__('O status da sua inscrição na fase de recurso %s foi alterado.'),
                $opportunity->name
            )
        );

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
            "message" => $message,
        ];

        $to = ($registration->owner->emailPrivado ??
                $registration->owner->emailPublico ??
                $registration->ownerUser->email);

        if (!$to) {
            return;
        }

        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => $to,
            "subject" => $subject,
            "body" => $app->renderMustacheTemplate($template, $params)
        ];
        $app->createAndSendMailMessage($email_params);
    }

    /**
     * Envia notificação do sistema para o proponente e gestores da oportunidade
     *
     * @param Opportunity $opportunity Fase de recurso (filha).
     * @param $recipient
     * @param bool $evaluator
     * @param string $flow Flow ID ({@see self::NOTIFY_FLOWS}).
     */
    function sendSystemNotification(Opportunity $opportunity, $recipient, $evaluator, string $flow) {
        $message = $this->resolveText(
            $opportunity,
            $flow,
            'message',
            $evaluator
                ? static fn() => sprintf(i::__('Um novo recurso para avaliação foi gerado em %s'), $opportunity->name)
                : static fn() => sprintf(i::__('Uma nova solicitação de recurso foi feita em %s'), $opportunity->name)
        );

        $notification = new Notification;
        $notification->user = $recipient->ownerUser;
        $notification->message = $message;
        $notification->save(true);
    }
    
    /**
     * Envia notificação do sistema para o proponente sobre a alteração de status
     *
     * @param Opportunity $opportunity Fase de recurso (filha).
     * @param $recipient
     * @param string $flow Flow ID ({@see self::NOTIFY_FLOWS}); deve ser um dos status*.
     */
    function sendNotificationNewStatus(Opportunity $opportunity, $recipient, string $flow) {
        $message = $this->resolveText(
            $opportunity,
            $flow,
            'message',
            static fn() => sprintf(i::__('O status da sua inscrição na fase de recurso %s foi alterado.'), $opportunity->name)
        );

        $notification = new Notification;
        $notification->user = $recipient->ownerUser;
        $notification->message = $message;
        $notification->save(true);
    }
}
