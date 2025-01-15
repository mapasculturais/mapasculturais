<?php

namespace OpportunityAppealPhase;

use MapasCulturais\App;
use MapasCulturais\Controllers;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Entities\ChatThread;
use MapasCulturais\Definitions\ChatThreadType;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    const CHAT_THREAD_TYPE = 'opportunity_appeal_phase';

    public function _init() {
        $app = App::i();
        $self = $this;

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
            $appeal_phase->save(true);

            $opportunity->appealPhase = $appeal_phase;
            $opportunity->save(true);
            
            $evaluation = new EvaluationMethodConfiguration();
            $evaluation->opportunity = $appeal_phase;
            $evaluation->type = 'appeal-phase';
            $evaluation->save(true);

            $this->json($appeal_phase);
        });

        /**
         * Endpoint para criação de inscrição na fase de recurso da oportunidade.
         *
         * @param int $registration_id
         */
        $app->hook('POST(opportunity.createAppealPhaseRegistration)', function() use ($app, $self) {
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
                
                $self->sendEmail($opportunity, $new_registration, $registration_email);
                $self->sendSystemNotification($opportunity, $new_registration);
                
                // Disparo para os gestores da oportunidade
                $relations = $opportunity->getAgentRelations();
                foreach($relations as $relation) {
                    if($relation->group == 'group-admin') {
                        $user_email = ($relation->agent->emailPrivado ??
                            $relation->agent->emailPublico ??
                            $relation->agent->user->email);

                        $self->sendEmail($opportunity, $new_registration, $user_email);
                        $self->sendSystemNotification($opportunity, $relation);
                    }
                }

                $this->json($new_registration);
            }
        });

        // Envio de e-mail e notificação do sistema após o envio da inscrição
        $app->hook('entity(Registration).send:after', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {

                // Disparo de e-mail para todos os avaliadores dessa fase de recurso
                $relations = $opportunity->evaluationMethodConfiguration->getAgentRelations();
                foreach($relations as $relation) {
                    $user_email = ($relation->agent->emailPrivado ??
                        $relation->agent->emailPublico ??
                        $relation->agent->user->email);

                    $self->sendEmail($opportunity, $this, $user_email, true);
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
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para deferido
        $app->hook('entity(Registration).status(approved)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->sendMailNewStatus($opportunity, $this);
                $self->sendNotificationNewStatus($opportunity, $this);
            }
        });

        // Envio de e-mail e notificação do sistema após status for alterado para negado
        $app->hook('entity(Registration).status(invalid)', function() use ($app, $self) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;

            if($opportunity->status == Opportunity::STATUS_APPEAL_PHASE) {
                $self->sendMailNewStatus($opportunity, $this);
                $self->sendNotificationNewStatus($opportunity, $this);
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

        $thread_type_description = i::__('Conversação entre proponente e avaliador');
        $definition = new ChatThreadType(self::CHAT_THREAD_TYPE, $thread_type_description, function (ChatMessage $message) {
            $thread = $message->thread;
            $registration = $thread->ownerEntity;
            $notification_content = '';
            $sender = '';
            $recipient = '';
            $notification = new Notification;
            if ($message->thread->checkUserRole($message->user, 'admin')) {
                // mensagem do parecerista
                $notification->user = $registration->owner->user;
                $notification_content = i::__("Nova mensagem do parecerista da prestação de contas número %s");
                $sender = 'admin';
                $recipient = 'participant';
            } else {
                // mensagem do usuário responsável pela prestação de contas
                $notification->user = $registration->owner->user;
                $notification_content = i::__("Nova mensagem na prestação de contas número %s");
                $sender = 'participant';
                $recipient = 'admin';
            }
            $notification->message = sprintf($notification_content, "<a href=\"{$registration->singleUrl}\" >{$registration->number}</a>");
            $notification->save(true);
            $this->sendEmailForNotification($message, $notification, $sender, $recipient);
        });
        $app->registerChatThreadType($definition);
    }

    /**
     * Envia e-mail para o proponente e gestores da oportunidade
     *
     * @param Opportunity $opportunity
     * @param Registration $registration
     * @param string $email
     * @param bool $evaluator
     */
    function sendEmail(Opportunity $opportunity, Registration $registration, string $email, $evaluator = false) {
        $app = App::i();
       
        $template = "opportunityappealphase/appeal-phase.html";

        $subject = $evaluator ? sprintf(i::__("Aviso sobre uma nova avaliação de recurso em " ."%s"), $opportunity->name) : sprintf(i::__("Aviso sobre um novo recurso em " ."%s"), $opportunity->appealPhase->name);
        $message = $evaluator ? sprintf(i::__("Um novo recurso para avaliação foi gerado em " ."%s"), $opportunity->name) : sprintf(i::__("Uma nova solicitação de recurso foi feita em " ."%s"), $opportunity->appealPhase->name) ;
        
        $params = [
            "siteName" => $app->siteName,
            "user" => $registration->owner->name,
            "baseUrl" => $registration->singleUrl,
            "message" => $message
        ];
        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => $email,
            "subject" => $subject,
            "body" => $app->renderMustacheTemplate($template, $params)
        ];
        if (!isset($email_params["to"])) {
            return;
        }
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
        $params = [
            "siteName" => $app->siteName,
            "user" => $registration->owner->name,
            "baseUrl" => $registration->singleUrl,
            "opportunityId" => $opportunity->id,
            "opportunityTitle" => $opportunity->name,
            "registrationId" => $registration->id,
            "registrationUrl" => $registration->singleUrl
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
        $message = $evaluator ? i::__('Um novo recurso para avaliação foi gerado em ' . $opportunity->name) : i::__('Uma nova solicitação de recurso foi feita em ' . $opportunity->name);

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
        $message = i::__('O status da sua inscrição na fase de recurso ' . $opportunity->appealPhase->name . ' foi alterado.');

        $notification = new Notification;
        $notification->user = $recipient->ownerUser;
        $notification->message = $message;
        $notification->save(true);
    }
}