<?php

namespace ProjectMonitoring;

use MapasCulturais\App;
use MapasCulturais\Controllers\Registration;
use MapasCulturais\Definitions\Metadata;
use MapasCulturais\Entities;
use MapasCulturais\i;
use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;

class Module extends \MapasCulturais\Module {

    public function _init() {
        $app = App::i();

        $app->hook('entity(Registration).insert:after', function () use ($app) {
            /** @var Entities\Registration $this */
            $opportunity = $this->opportunity;

            if ($opportunity->isReportingPhase) {
                if ($opportunity->isFinalReportingPhase) {
                    $notification_template = i::__('A fase de prestação de informações da inscrição %s, na oportunidade %s, começou. Você deve preenchê-la com as informações solicitadas até %s.');
                } else {
                    $notification_template = i::__('A fase de monitoramento da inscrição %s, na oportunidade %s, começou. Você deve preenchê-la com as informações solicitadas até %s.');
                }
    
                $notification_message = sprintf(
                   $notification_template,
                    "<strong>{$this->number}</strong>",
                    "<strong>{$opportunity->firstPhase->name}</strong>",
                    "<strong>{$opportunity->registrationTo->format('d/m/Y H:i')}</strong>"
                );
                
                $notification = new Entities\Notification();
                $notification->user = $this->owner->user;
                $notification->message = $notification_message;
                $notification->save(true);
            }
        });

        $app->hook('sendMailNotification.registrationStart', function (Entities\Registration &$registration, string &$template, array &$params) {
            $opportunity = $registration->opportunity;

            if ($opportunity->isReportingPhase) {
                if ($opportunity->isFinalReportingPhase) {
                    $template = 'start_final_reporting_phase';
                } else {
                    $template = 'start_reporting_phase';
                }
                $params['registrationTo'] = $opportunity->registrationTo->format('d/m/Y H:i');
            }
        });
        
        $app->hook('GET(panel.validations)', function() use($app) {
            /** @var \Panel\Controller $this */
            $this->requireAuthentication();

            $this->render('validations', []);
        });

        $app->hook('panel.nav', function(&$group) use($app) {
            $group['opportunities']['items'][] = [
                'route' => 'panel/validations',
                'icon' => 'opportunity',
                'label' => i::__('Minhas validações'),
                'condition' => function() use($app) {
                    return $app->user->getIsEvaluator();
                }
            ];
        });
    }
    
    public function register() {
        $app = App::i();

        $this->registerOpportunityMetadata('isReportingPhase', [
            'label' => i::__('É fase de prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerOpportunityMetadata('isFinalReportingPhase', [
            'label' => i::__('É fase final de prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerOpportunityMetadata('includesWorkPlan', [
            'label' => i::__('Incluir plano de trabalho na prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerEvauationMethodConfigurationMetadata('allowsMultipleReplies', [
            'label' => i::__('Possibilitar mais de uma resposta do proponente'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
        ]);

        // Metadados para Goal (Meta)
        $executionDetail = new Metadata('executionDetail', [
            'label' => \MapasCulturais\i::__('Detalhamento da execução da meta')
        ]);
        $app->registerMetadata($executionDetail, Goal::class);

        // Metadados para Delivery (Entrega)
        $availabilityType = new Metadata('availabilityType', [
            'label' => \MapasCulturais\i::__('Forma de disponibilização'),
            'type' => 'select',
        ]);
        $app->registerMetadata($availabilityType, Delivery::class);

        $deliverySubtype = new Metadata('deliverySubtype', [
            'label' => \MapasCulturais\i::__('Subtipo de entrega'),
            'type' => 'select',
        ]);
        $app->registerMetadata($deliverySubtype, Delivery::class);

        $accessibilityMeasures = new Metadata('accessibilityMeasures', [
            'label' => \MapasCulturais\i::__('Medidas de acessibilidade'),
            'type' => 'multiselect',
        ]);
        $app->registerMetadata($accessibilityMeasures, Delivery::class);

        $participantProfile = new Metadata('participantProfile', [
            'label' => \MapasCulturais\i::__('Perfil dos participantes'),
            'type' => 'text'
        ]);
        $app->registerMetadata($participantProfile, Delivery::class);

        $priorityAudience = new Metadata('priorityAudience', [
            'label' => \MapasCulturais\i::__('Público prioritário'),
            'type' => 'multiselect',
        ]);
        $app->registerMetadata($priorityAudience, Delivery::class);

        $numberOfParticipants = new Metadata('numberOfParticipants', [
            'label' => \MapasCulturais\i::__('Número de participantes'),
            'type' => 'number'
        ]);
        $app->registerMetadata($numberOfParticipants, Delivery::class);

        $executedRevenue = new Metadata('executedRevenue', [
            'label' => \MapasCulturais\i::__('Receita executada'),
            'type' => 'object'
        ]);
        $app->registerMetadata($executedRevenue, Delivery::class);

        $evidenceLinks = new Metadata('evidenceLinks', [
            'label' => \MapasCulturais\i::__('Links das evidências'),
            'type' => 'array'
        ]);
        $app->registerMetadata($evidenceLinks, Delivery::class);

        // Metadados para Registration (Inscrição)
        $workplanSnapshot = new Metadata('workplanSnapshot', [
            'label' => \MapasCulturais\i::__('Snapshot do plano de trabalho'),
            'type' => 'json'
        ]);
        $app->registerMetadata($workplanSnapshot, Registration::class);

        $goalStatuses = new Metadata('goalStatuses', [
            'label' => \MapasCulturais\i::__('Status das metas'),
            'type' => 'json'
        ]);

        $app->registerMetadata($goalStatuses, Registration::class);
        
        $app->registerController('projectReporting', Controller::class);

        $workplanProxy = new Metadata('workplanProxy', [
            'label'     => \MapasCulturais\i::__('Registro de plano de trabalho'),
            'type'      => 'json',
            'serialize' => function($value, Registration $registration            = null) use ($app) {
                if (!$registration) {
                    return $value;
                }

                /** @var Registration */
                $first_phase = $registration->firstPhase;

                if ($first_phase) {
                    $workplan = $app->repo(\OpportunityWorkplan\Entities\Workplan::class)->findOneBy([
                        'registration' => $first_phase
                    ]);

                    $goals = $app->repo(\OpportunityWorkplan\Entities\Goal::class)->findBy([
                        'workplan' => $workplan
                    ]);

                    $deliveries = $app->repo(\OpportunityWorkplan\Entities\Delivery::class)->findBy([
                        'goal' => $goals
                    ]);

                    foreach($goals as $goal) {
                        $data = $value['goals'][$goal->id] ?? [];
                        $goal->status          = $data['status'];
                        $goal->executionDetail = $data['executionDetail'];
                    }

                    foreach($deliveries as $delivery) {
                        $data = $value['deliveries'][$goal->id] ?? [];
                        $goal->accessibilityMeasures = $data['accessibilityMeasures'];
                        $goal->availabilityType      = $data['availabilityType'];
                        $goal->deliverySubtype       = $data['deliverySubtype'];
                        $goal->evidenceLinks         = $data['evidenceLinks'];
                        $goal->executedRevenue       = $data['executedRevenue'];
                        $goal->numberOfParticipants  = $data['numberOfParticipants'];
                        $goal->participantProfile    = $data['participantProfile'];
                        $goal->priorityAudience      = $data['priorityAudience'];
                    }

                    $app->hook('entity(Registration).save:finish', function() use ($goals, $deliveries, $first_phase, $app) {
                        /** @var Registration $this */
                        if ($first_phase->equals($this)) {
                            $app->disableAccessControl();
                            foreach($goals as $goal) {
                                $goal->save(true);
                            }

                            foreach ($deliveries as $delivery) {
                                $delivery->save(true);
                            }
                            $app->enableAccessControl();
                        }
                    });
                }

                return $value;
            },
            'unserialize' => function ($value, Registration $registration = null) use ($app) {
                if (!$registration) {
                    return $value;
                }

                /** @var Registration */
                $first_phase = $registration->firstPhase;

                if ($first_phase) {
                    $workplan = $app->repo(\OpportunityWorkplan\Entities\Workplan::class)->findOneBy([
                        'registration' => $first_phase
                    ]);

                    $goals = $app->repo(\OpportunityWorkplan\Entities\Goal::class)->findBy([
                        'workplan' => $workplan
                    ]);

                    $deliveries = $app->repo(\OpportunityWorkplan\Entities\Delivery::class)->findBy([
                        'goal' => $goals
                    ]);

                    $result = [
                        'goals'      => [],
                        'deliveries' => []
                    ];

                    foreach ($goals as $goal) {
                        $result['goals'][$goal->id] = [
                            'executionDetail' => $goal->executionDetail,
                            'status'          => $goal->status
                        ];
                    }

                    foreach($deliveries as $delivery) {
                        $result['deliveries'][$delivery->id] = [
                            'accessibilityMeasures' => $delivery->accessibilityMeasures,
                            'availabilityType'      => $delivery->availabilityType,
                            'deliverySubtype'       => $delivery->deliverySubtype,
                            'evidenceLinks'         => $delivery->evidenceLinks,
                            'executedRevenue'       => $delivery->executedRevenue,
                            'goal'                  => $delivery->goal->id,
                            'numberOfParticipants'  => $delivery->numberOfParticipants,
                            'participantProfile'    => $delivery->participantProfile,
                            'priorityAudience'      => $delivery->priorityAudience,
                            'status'                => $delivery->status
                        ];
                    }

                    return $result;
                }
            }
        ]);
        $app->registerMetadata($workplanProxy, Registration::class);
    }

}
