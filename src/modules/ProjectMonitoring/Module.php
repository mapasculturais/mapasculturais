<?php

namespace ProjectMonitoring;

use MapasCulturais\App;
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

        // Salva os metadados workplanSnapshot e goalStatuses e uma cópia dos arquivos de evidência no envio da inscrição da fase de monitoramento
        $app->hook('entity(Registration).send:after', function() use ($app) {
            /** @var Entities\Registration $this */
            $registration = $this;
            $opportunity = $registration->opportunity;

            /** @var Entities\Registration */
            $first_phase = $registration->firstPhase;

            if ($opportunity->isReportingPhase && $first_phase) {
                $workplan = $app->repo(\OpportunityWorkplan\Entities\Workplan::class)->findOneBy([
                    'registration' => $first_phase
                ]);

                $workplan_snapshot = json_decode(json_encode($workplan->jsonSerialize()));

                foreach($workplan_snapshot->goals as &$goal) {
                    foreach($goal->deliveries as &$delivery) {
                        foreach(($delivery->files->evidences ?? []) as &$file) {
                            $entity_file = $app->repo('File')->find($file->id);
                            $file->id = $file->id . '--snapshot--' . $registration->id;
                            $file->url = str_replace($file->name, "{$registration->id}--{$file->name}", $file->url);
                            $target_file_name = str_replace($file->name, "{$registration->id}--{$file->name}", $entity_file->path);
                            if(file_exists($target_file_name)) {
                                unlink($target_file_name);
                            }
                            link($entity_file->path, $target_file_name);
                        }
                        
                    }
                }
                
                $registration->workplanSnapshot = $workplan_snapshot;
                
                $goals = $app->repo(\OpportunityWorkplan\Entities\Goal::class)->findBy([
                    'workplan' => $workplan
                ]);

                $goal_statuses = [
                    "numGoals" => count($goals),
                    "0" => 0,
                    "1" => 0,
                    "2" => 0,
                    "3" => 0,
                    "10" => 0
                ];

                foreach ($goals as $goal) {
                    $status = $goal->status;
                    $goal_statuses[$status]++;
                }

                $registration->goalStatuses = $goal_statuses;

                $app->disableAccessControl();
                $registration->save(true);
                $app->enableAccessControl();
            }
        });

        $app->hook('view.requestedEntity(Registration).result', function (&$json) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $requested_entity = $this->controller->requestedEntity;
            $json['workplanProxy'] = $requested_entity->workplanProxy;
        });

        $app->hook('entity(Registration).jsonSerialize', function (&$json) {
            /** @var Entities\Registration $this */
            if ($this->opportunity->isReportingPhase && $this->opportunity->parent->enableWorkplan) {
                $json['workplanSnapshot'] = $this->workplanSnapshot;
                $json['workplanProxy'] = $this->workplanProxy;
            }
        });

        $app->hook('entity(Registration).validationErrors', function (&$errors) use ($app) {
            /** @var Entities\Registration $this*/
            $first_phase = $this->firstPhase;

            if (!($this->opportunity->isReportingPhase && $first_phase->opportunity->enableWorkplan)) {
                return;
            }

            $workplan = $app->repo(\OpportunityWorkplan\Entities\Workplan::class)->findOneBy([
                'registration' => $first_phase
            ]);

            $goals = $app->repo(\OpportunityWorkplan\Entities\Goal::class)->findBy([
                'workplan' => $workplan
            ]);

            $deliveries = $app->repo(\OpportunityWorkplan\Entities\Delivery::class)->findBy([
                'goal' => $goals
            ]);

            $workplan_errors = [
                'goals' => [],
                'deliveries' => [],
            ];
            $has_errors = false;

            foreach ($goals as $goal) {
                if ($goal_errors = $goal->validationErrors) {
                    $workplan_errors['goals'][$goal->id] = $goal_errors;
                    $has_errors = true;
                }
            }

            foreach ($deliveries as $delivery) {
                if ($delivery_errors = $delivery->validationErrors) {
                    $workplan_errors['deliveries'][$delivery->id] = $delivery_errors;
                    $has_errors = true;
                }
            }

            if ($has_errors) {
                $errors['workplanProxy'] = $workplan_errors;
            }
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

        // Metadados para Goal (Meta)
        $executionDetail = new Metadata('executionDetail', [
            'label' => i::__('Detalhamento da execução da meta')
        ]);
        $app->registerMetadata($executionDetail, Goal::class);

        // Metadados para Delivery (Entrega)
        $availabilityType = new Metadata('availabilityType', [
            'label' => i::__('Forma de disponibilização'),
            'type' => 'select',
            'options' => [
                i::__('Virtual/Digital'),
                i::__('Presencial/Físico'),
                i::__('Híbrido'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('availabilityType')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($availabilityType, Delivery::class);

        $accessibilityMeasures = new Metadata('accessibilityMeasures', [
            'label' => i::__('Medidas de acessibilidade'),
            'type' => 'multiselect',
            'options' => [
                i::__('Rotas acessíveis, com espaço de manobra para cadeira de rodas'),
                i::__('Palco acessível'),
                i::__('Camarim acessível'),
                i::__('Piso tátil'),
                i::__('Rampas'),
                i::__("Elevadores adequados para PCD's"),
                i::__('Corrimãos e guarda-corpos'),
                i::__("Banheiros adaptados para PCD's"),
                i::__('Área de alimentação preferencial identificada'),
                i::__("Vagas de estacionamento para PCD's reservadas"),
                i::__("Assentos para pessoas obesas, pessoas com mobilidade reduzida, PCD's e pessoas idosas reservadas"),
                i::__('Filas preferenciais identificadas'),
                i::__('Iluminação adequada'),
                i::__('Livro e/ou similares em braile'),
                i::__('Audiolivro'),
                i::__('Uso Língua Brasileira de Sinais - Libras'),
                i::__('Sistema Braille em materiais impressos'),
                i::__('Sistema de sinalização ou comunicação tátil'),
                i::__('Audiodescrição'),
                i::__('Legendas para surdos e ensurdecidos'),
                i::__('Linguagem simples'),
                i::__('Textos adaptados para software de leitor de tela'),
                i::__('Capacitação em acessibilidade para equipes atuantes nos projetos culturais'),
                i::__('Contratação de profissionais especializados em acessibilidade cultural'),
                i::__('Contratação de profissionais com deficiência'),
                i::__('Formação e sensibilização de agentes culturais sobre acessibilidade'),
                i::__('Formação e sensibilização de públicos da cadeia produtiva cultural sobre acessibilidade'),
                i::__("Envolvimento de PCD's na concepção do projeto"),
                i::__('Outras'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('accessibilityMeasures')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($accessibilityMeasures, Delivery::class);

        $participantProfile = new Metadata('participantProfile', [
            'label' => i::__('Perfil dos participantes'),
            'type' => 'text',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('participantProfile')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($participantProfile, Delivery::class);

        $priorityAudience = new Metadata('priorityAudience', [
            'label' => i::__('Territórios prioritários'),
            'type' => 'multiselect',
            'options' => [
                i::__('Território indígena'),
                i::__('Território de povos e comunidades tradicionais'),
                i::__('Território rural'),
                i::__('Território de fronteira'),
                i::__('Regiões com menor índice de Desenvolvimento Humano - IDH'),
                i::__('Regiões com menor histórico de acesso aos recursos da política pública de cultura'),
                i::__('Área atingida por desastre natural'),
                i::__('Assentamento ou acampamento'),
                i::__('Conjunto ou empreendimento habitacional de interesse social'),
                i::__('Periferia'),
                i::__('Favelas e comunidades urbanas'),
                i::__('Zona especial de interesse social'),
                i::__('Sítios de arqueológicos e de patrimônio cultural'),
                i::__('Não se aplica'),
                i::__('Outros'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('priorityAudience')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($priorityAudience, Delivery::class);

        $numberOfParticipants = new Metadata('numberOfParticipants', [
            'label' => i::__('Número de participantes'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->positive()' => i::__('O valor deve ser um número inteiro positivo'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('numberOfParticipants')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($numberOfParticipants, Delivery::class);

        $executedRevenue = new Metadata('executedRevenue', [
            'label' => i::__('Receita executada'),
            'type' => 'object',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedRevenue')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedRevenue, Delivery::class);

        $evidenceLinks = new Metadata('evidenceLinks', [
            'label' => i::__('Links das evidências'),
            'type' => 'array'
        ]);
        $app->registerMetadata($evidenceLinks, Delivery::class);

        // ============================================
        // NOVOS CAMPOS DE MONITORAMENTO (EXECUTADOS)
        // ============================================

        // Municípios executados
        $executedNumberOfCities = new Metadata('executedNumberOfCities', [
            'label' => i::__('Em quantos municípios a atividade foi realizada?'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ]
        ]);
        $app->registerMetadata($executedNumberOfCities, Delivery::class);

        // Bairros executados
        $executedNumberOfNeighborhoods = new Metadata('executedNumberOfNeighborhoods', [
            'label' => i::__('Em quantos bairros a atividade foi realizada?'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ]
        ]);
        $app->registerMetadata($executedNumberOfNeighborhoods, Delivery::class);

        // Ações de mediação executadas
        $executedMediationActions = new Metadata('executedMediationActions', [
            'label' => i::__('Quantas ações de mediação/formação de público foram realizadas?'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ]
        ]);
        $app->registerMetadata($executedMediationActions, Delivery::class);

        // Unidades comercializadas (executado)
        $executedCommercialUnits = new Metadata('executedCommercialUnits', [
            'label' => i::__('Quantidade de unidades efetivamente comercializadas'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ]
        ]);
        $app->registerMetadata($executedCommercialUnits, Delivery::class);

        // Valor unitário executado
        $executedUnitPrice = new Metadata('executedUnitPrice', [
            'label' => i::__('Valor unitário praticado (R$)'),
            'type' => 'currency'
        ]);
        $app->registerMetadata($executedUnitPrice, Delivery::class);

        // Pessoas remuneradas por função (executado)
        $executedPaidStaffByRole = new Metadata('executedPaidStaffByRole', [
            'label' => i::__('Quantas pessoas foram remuneradas, por função?'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function($val) {
                return json_decode((string) $val, true);
            }
        ]);
        $app->registerMetadata($executedPaidStaffByRole, Delivery::class);

        // Composição da equipe por gênero (executado)
        $executedTeamCompositionGender = new Metadata('executedTeamCompositionGender', [
            'label' => i::__('Composição efetiva da equipe por gênero'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function($val) {
                return json_decode((string) $val, true);
            }
        ]);
        $app->registerMetadata($executedTeamCompositionGender, Delivery::class);

        // Composição da equipe por raça/cor (executado)
        $executedTeamCompositionRace = new Metadata('executedTeamCompositionRace', [
            'label' => i::__('Composição efetiva da equipe por raça/cor'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function($val) {
                return json_decode((string) $val, true);
            }
        ]);
        $app->registerMetadata($executedTeamCompositionRace, Delivery::class);

        // Medidas de acessibilidade executadas (já existe accessibilityMeasures)
        // Este campo já existe e será usado para os dados executados

        // Metadados para Registration (Inscrição)
        $this->registerRegistrationMetadata('workplanSnapshot', [
            'label'     => i::__('Snapshot do plano de trabalho'),
            'type'      => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerRegistrationMetadata('goalStatuses', [
            'label'     => i::__('Status das metas'),
            'type'      => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerRegistrationMetadata('workplanProxy', [
            'label'     => i::__('Registro de plano de trabalho'),
            'type'      => 'json',
            'serialize' => function($value, ?object $registration = null) use ($app) {
                if (!($registration instanceof Entities\Registration)) {
                    return null;
                }

                /** @var Entities\Registration */
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
                        $data = $value['deliveries'][$delivery->id] ?? [];
                        $delivery->accessibilityMeasures = $data['accessibilityMeasures'];
                        $delivery->availabilityType      = $data['availabilityType'];
                        $delivery->evidenceLinks         = $data['evidenceLinks'];
                        $delivery->executedRevenue       = $data['executedRevenue'];
                        $delivery->numberOfParticipants  = $data['numberOfParticipants'];
                        $delivery->participantProfile    = $data['participantProfile'];
                        $delivery->priorityAudience      = $data['priorityAudience'];
                        
                        // Novos campos executados
                        $delivery->executedNumberOfCities        = $data['executedNumberOfCities'] ?? null;
                        $delivery->executedNumberOfNeighborhoods = $data['executedNumberOfNeighborhoods'] ?? null;
                        $delivery->executedMediationActions      = $data['executedMediationActions'] ?? null;
                        $delivery->executedCommercialUnits       = $data['executedCommercialUnits'] ?? null;
                        $delivery->executedUnitPrice             = $data['executedUnitPrice'] ?? null;
                        $delivery->executedPaidStaffByRole       = $data['executedPaidStaffByRole'] ?? null;
                        $delivery->executedTeamCompositionGender = $data['executedTeamCompositionGender'] ?? null;
                        $delivery->executedTeamCompositionRace   = $data['executedTeamCompositionRace'] ?? null;
                    }

                    $app->hook('entity(Registration).save:finish', function() use ($goals, $deliveries, $first_phase, $app) {
                        /** @var Entities\Registration $this */
                        if ($this->opportunity->isReportingPhase && $first_phase->opportunity->enableWorkplan) {
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

                return null;
            },
            'unserialize' => function ($value, ?object $registration = null) use ($app) {
                if (!($registration instanceof Entities\Registration)) {
                    return [
                        'goals'      => [],
                        'deliveries' => [],
                    ];
                }

                /** @var Entities\Registration */
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
                            'evidenceLinks'         => $delivery->evidenceLinks,
                            'executedRevenue'       => $delivery->executedRevenue,
                            'goal'                  => $delivery->goal->id,
                            'numberOfParticipants'  => $delivery->numberOfParticipants,
                            'participantProfile'    => $delivery->participantProfile,
                            'priorityAudience'      => $delivery->priorityAudience,
                            'status'                => $delivery->status,
                            
                            // Novos campos executados
                            'executedNumberOfCities'        => $delivery->executedNumberOfCities,
                            'executedNumberOfNeighborhoods' => $delivery->executedNumberOfNeighborhoods,
                            'executedMediationActions'      => $delivery->executedMediationActions,
                            'executedCommercialUnits'       => $delivery->executedCommercialUnits,
                            'executedUnitPrice'             => $delivery->executedUnitPrice,
                            'executedPaidStaffByRole'       => $delivery->executedPaidStaffByRole,
                            'executedTeamCompositionGender' => $delivery->executedTeamCompositionGender,
                            'executedTeamCompositionRace'   => $delivery->executedTeamCompositionRace,
                        ];
                    }

                    return $result;
                }
            }
        ]);

        $app->registerController('projectReporting', Controller::class);
    }

}
