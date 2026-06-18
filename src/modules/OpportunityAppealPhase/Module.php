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

        $this->registerOpportunityMetadata('appealPhaseAffectsSync', [
            'label' => i::__('Sincronizar inscrições para fase seguinte'),
            'type'  => 'boolean',
            'default' => false,
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
