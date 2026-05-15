<?php

namespace ProjectMonitoring;

use MapasCulturais\App;
use MapasCulturais\Definitions\Metadata;
use MapasCulturais\Entities;
use MapasCulturais\i;
use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Module as OpportunityWorkplanModule;

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
            $appendError = function (string $field, string $message) use (&$errors) {
                if (!isset($errors[$field])) {
                    $errors[$field] = [];
                }

                if (!in_array($message, $errors[$field], true)) {
                    $errors[$field][] = $message;
                }
            };

            foreach ($goals as $goal) {
                if ($goal_errors = $goal->validationErrors) {
                    $workplan_errors['goals'][$goal->id] = $goal_errors;
                    $has_errors = true;

                    foreach ($goal_errors as $field => $messages) {
                        $label = OpportunityWorkplanModule::getFieldLabel($field);
                        foreach ((array) $messages as $message) {
                            if (!is_string($message) || $message === '') {
                                continue;
                            }

                            $appendError('goal', sprintf(
                                i::__('Meta "%s": %s. %s'),
                                $goal->title,
                                $label,
                                $message
                            ));
                        }
                    }
                }
            }

            foreach ($deliveries as $delivery) {
                if ($delivery_errors = $delivery->validationErrors) {
                    $workplan_errors['deliveries'][$delivery->id] = $delivery_errors;
                    $has_errors = true;

                    foreach ($delivery_errors as $field => $messages) {
                        $label = OpportunityWorkplanModule::getFieldLabel($field);
                        foreach ((array) $messages as $message) {
                            if (!is_string($message) || $message === '') {
                                continue;
                            }

                            $appendError('delivery', sprintf(
                                i::__('Entrega "%s": %s. %s'),
                                $delivery->name,
                                $label,
                                $message
                            ));
                        }
                    }
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
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedNumberOfCities')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedNumberOfCities, Delivery::class);

        // Bairros executados
        $executedNumberOfNeighborhoods = new Metadata('executedNumberOfNeighborhoods', [
            'label' => i::__('Em quantos bairros a atividade foi realizada?'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedNumberOfNeighborhoods')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedNumberOfNeighborhoods, Delivery::class);

        // Ações de mediação executadas
        $executedMediationActions = new Metadata('executedMediationActions', [
            'label' => i::__('Quantas ações de mediação/formação de público foram realizadas?'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedMediationActions')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedMediationActions, Delivery::class);

        // Unidades comercializadas (executado)
        $executedCommercialUnits = new Metadata('executedCommercialUnits', [
            'label' => i::__('Quantidade de unidades efetivamente comercializadas'),
            'type' => 'integer',
            'validations' => [
                'v::intVal()->min(0)' => i::__('Deve ser um número maior ou igual a zero')
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedCommercialUnits')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedCommercialUnits, Delivery::class);

        // Valor unitário executado
        $executedUnitPrice = new Metadata('executedUnitPrice', [
            'label' => i::__('Valor unitário praticado (R$)'),
            'type' => 'currency',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedUnitPrice')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
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
            },
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedPaidStaffByRole')) {
                    return i::__('Campo obrigatório');
                }
                return false;
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
            },
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedTeamCompositionGender')) {
                    return i::__('Campo obrigatório');
                }
                return false;
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
            },
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedTeamCompositionRace')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedTeamCompositionRace, Delivery::class);

        $executedArtChainLink = new Metadata('executedArtChainLink', [
            'label' => i::__('Principal elo das artes acionado (executado)'),
            'type' => 'select',
            'options' => [
                i::__('Acesso'),
                i::__('Criação'),
                i::__('Produção'),
                i::__('Difusão'),
                i::__('Circulação'),
                i::__('Internacionalização'),
                i::__('Formação'),
                i::__('Fruição'),
                i::__('Memória/Preservação'),
                i::__('Pesquisa'),
                i::__('Reflexão'),
                i::__('Gestão Cultural'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedArtChainLink')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedArtChainLink, Delivery::class);

        $executedCommunicationChannels = new Metadata('executedCommunicationChannels', [
            'label' => i::__('Canais de comunicação utilizados (executado)'),
            'type' => 'multiselect',
            'options' => [
                i::__('Instagram'),
                i::__('Facebook'),
                i::__('TikTok'),
                i::__('YouTube'),
                i::__('X/Twitter'),
                i::__('WhatsApp (listas/grupos)'),
                i::__('Telegram (canais/grupos)'),
                i::__('Site/página oficial do projeto'),
                i::__('E-mail marketing/newsletter'),
                i::__('Plataformas de eventos/inscrição (ex.: Sympla/Shotgun/Eventbrite)'),
                i::__('Portais, blogs e influenciadores/as locais'),
                i::__('Rádio comunitária'),
                i::__('Rádio comercial'),
                i::__('TV local'),
                i::__('Mídia impressa'),
                i::__('Cartazes e materiais impressos'),
                i::__('Carro de som'),
                i::__('Outros'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedCommunicationChannels')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedCommunicationChannels, Delivery::class);

        $executedRevenueType = new Metadata('executedRevenueType', [
            'label' => i::__('Qual o tipo de receita executada?'),
            'type' => 'multiselect',
            'options' => [
                i::__('Venda de ingressos'),
                i::__('Venda de produtos'),
                i::__('Patrocínio privado'),
                i::__('Apoio cultural'),
                i::__('Doações'),
                i::__('Cachê'),
                i::__('Prestação de serviços'),
                i::__('Direitos autorais'),
                i::__('Licenciamento'),
                i::__('Não haverá receita'),
                i::__('Outros'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedRevenueType')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedRevenueType, Delivery::class);

        $executedSegmentDelivery = new Metadata('executedSegmentDelivery', [
            'label' => i::__('Segmento artístico-cultural executado da entrega'),
            'type' => 'select',
            'options' => [
                i::__('Artes Visuais'),
                i::__('Artesanato'),
                i::__('Audiovisual e Mídias Interativas'),
                i::__('Circo'),
                i::__('Culturas Tradicionais e Populares'),
                i::__('Culturas dos Povos Originários'),
                i::__('Dança'),
                i::__('Design e Serviços Criativos'),
                i::__('Economia, Produção e Áreas Técnicas da Cultura'),
                i::__('Festas Populares'),
                i::__('Humanidades'),
                i::__('Livro, Leitura e Literatura'),
                i::__('Música'),
                i::__('Patrimônio Cultural Imaterial'),
                i::__('Patrimônio Cultural Material'),
                i::__('Performance'),
                i::__('Produção e Áreas Técnicas da Cultura'),
                i::__('Teatro'),
                i::__('Transversalidades')
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedSegmentDelivery')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedSegmentDelivery, Delivery::class);

        $executedHasCommunityCoauthors = new Metadata('executedHasCommunityCoauthors', [
            'label' => i::__('A atividade executada contou com envolvimento de comunidades/coletivos como coautores/coexecutores?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasCommunityCoauthors, Delivery::class);

        $executedCommunityCoauthorsDetail = new Metadata('executedCommunityCoauthorsDetail', [
            'label' => i::__('Descreva o envolvimento executado das comunidades/coletivos como coautores/coexecutores'),
            'type' => 'text',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedCommunityCoauthorsDetail')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedCommunityCoauthorsDetail, Delivery::class);

        $executedHasTransInclusionStrategy = new Metadata('executedHasTransInclusionStrategy', [
            'label' => i::__('A atividade executada contou com estratégias voltadas à promoção do acesso de pessoas Trans e Travestis?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasTransInclusionStrategy, Delivery::class);

        $executedTransInclusionActions = new Metadata('executedTransInclusionActions', [
            'label' => i::__('Quais ações executadas promoveram o acesso de pessoas Trans e Travestis?'),
            'type' => 'text',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedTransInclusionActions')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedTransInclusionActions, Delivery::class);

        $executedHasAccessibilityPlan = new Metadata('executedHasAccessibilityPlan', [
            'label' => i::__('A atividade executada contou com medidas de acessibilidade?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasAccessibilityPlan, Delivery::class);

        $executedExpectedAccessibilityMeasures = new Metadata('executedExpectedAccessibilityMeasures', [
            'label' => i::__('Quais medidas de acessibilidade foram executadas na atividade?'),
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
                if($entity->isMetadataRequired('executedExpectedAccessibilityMeasures')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedExpectedAccessibilityMeasures, Delivery::class);

        $executedHasEnvironmentalPractices = new Metadata('executedHasEnvironmentalPractices', [
            'label' => i::__('A atividade executada contou com medidas ou práticas socioambientais?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasEnvironmentalPractices, Delivery::class);

        $executedEnvironmentalPracticesDescription = new Metadata('executedEnvironmentalPracticesDescription', [
            'label' => i::__('Quais medidas e práticas socioambientais foram executadas na atividade?'),
            'type' => 'text',
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedEnvironmentalPracticesDescription')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedEnvironmentalPracticesDescription, Delivery::class);

        $executedHasPressStrategy = new Metadata('executedHasPressStrategy', [
            'label' => i::__('A atividade executada contou com estratégia de relacionamento com a imprensa?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasPressStrategy, Delivery::class);

        $executedHasInnovationAction = new Metadata('executedHasInnovationAction', [
            'label' => i::__('A atividade executada contou com ação de experimentação/inovação?'),
            'type' => 'select',
            'options' => [
                'true' => i::__('Sim'),
                'false' => i::__('Não'),
            ],
        ]);
        $app->registerMetadata($executedHasInnovationAction, Delivery::class);

        $executedInnovationTypes = new Metadata('executedInnovationTypes', [
            'label' => i::__('Quais tipos de experimentação/inovação foram executados?'),
            'type' => 'multiselect',
            'options' => [
                i::__('Uso de novas tecnologias (AR, VR, IA, etc.)'),
                i::__('Novas linguagens artísticas'),
                i::__('Fusão de linguagens'),
                i::__('Metodologias participativas inovadoras'),
                i::__('Novos modelos de gestão cultural'),
                i::__('Economia criativa e novos modelos de negócio'),
                i::__('Sustentabilidade e práticas ambientais inovadoras'),
                i::__('Inclusão e acessibilidade de forma inovadora'),
                i::__('Experimentação em espaços não convencionais'),
                i::__('Coprodução/cocriação com públicos'),
                i::__('Outros'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedInnovationTypes')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedInnovationTypes, Delivery::class);

        $executedDocumentationTypes = new Metadata('executedDocumentationTypes', [
            'label' => i::__('Tipo de documentação produzida (executado)'),
            'type' => 'multiselect',
            'options' => [
                i::__('Fotografia'),
                i::__('Vídeo'),
                i::__('Áudio'),
                i::__('Relatório textual'),
                i::__('Caderno de processo'),
                i::__('Publicação impressa'),
                i::__('Publicação digital'),
                i::__('Website/Plataforma online'),
                i::__('Redes sociais'),
                i::__('Depoimentos'),
                i::__('Registros de processo'),
                i::__('Acervo digitalizado'),
                i::__('Não haverá documentação específica'),
                i::__('Outros'),
            ],
            'should_validate' => function($entity) {
                if($entity->isMetadataRequired('executedDocumentationTypes')) {
                    return i::__('Campo obrigatório');
                }
                return false;
            }
        ]);
        $app->registerMetadata($executedDocumentationTypes, Delivery::class);

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
                        $delivery->executedArtChainLink          = $data['executedArtChainLink'] ?? null;
                        $delivery->executedCommunicationChannels = $data['executedCommunicationChannels'] ?? null;
                        $delivery->executedRevenueType           = $data['executedRevenueType'] ?? null;
                        $delivery->executedSegmentDelivery       = $data['executedSegmentDelivery'] ?? null;
                        $delivery->executedHasCommunityCoauthors = $data['executedHasCommunityCoauthors'] ?? null;
                        $delivery->executedCommunityCoauthorsDetail = $data['executedCommunityCoauthorsDetail'] ?? null;
                        $delivery->executedHasTransInclusionStrategy = $data['executedHasTransInclusionStrategy'] ?? null;
                        $delivery->executedTransInclusionActions = $data['executedTransInclusionActions'] ?? null;
                        $delivery->executedHasAccessibilityPlan  = $data['executedHasAccessibilityPlan'] ?? null;
                        $delivery->executedExpectedAccessibilityMeasures = $data['executedExpectedAccessibilityMeasures'] ?? null;
                        $delivery->executedHasEnvironmentalPractices = $data['executedHasEnvironmentalPractices'] ?? null;
                        $delivery->executedEnvironmentalPracticesDescription = $data['executedEnvironmentalPracticesDescription'] ?? null;
                        $delivery->executedHasPressStrategy      = $data['executedHasPressStrategy'] ?? null;
                        $delivery->executedHasInnovationAction   = $data['executedHasInnovationAction'] ?? null;
                        $delivery->executedInnovationTypes       = $data['executedInnovationTypes'] ?? null;
                        $delivery->executedDocumentationTypes    = $data['executedDocumentationTypes'] ?? null;
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
                            'executedArtChainLink'          => $delivery->executedArtChainLink,
                            'executedCommunicationChannels' => $delivery->executedCommunicationChannels,
                            'executedRevenueType'           => $delivery->executedRevenueType,
                            'executedSegmentDelivery'       => $delivery->executedSegmentDelivery,
                            'executedHasCommunityCoauthors' => $delivery->executedHasCommunityCoauthors,
                            'executedCommunityCoauthorsDetail' => $delivery->executedCommunityCoauthorsDetail,
                            'executedHasTransInclusionStrategy' => $delivery->executedHasTransInclusionStrategy,
                            'executedTransInclusionActions' => $delivery->executedTransInclusionActions,
                            'executedHasAccessibilityPlan'  => $delivery->executedHasAccessibilityPlan,
                            'executedExpectedAccessibilityMeasures' => $delivery->executedExpectedAccessibilityMeasures,
                            'executedHasEnvironmentalPractices' => $delivery->executedHasEnvironmentalPractices,
                            'executedEnvironmentalPracticesDescription' => $delivery->executedEnvironmentalPracticesDescription,
                            'executedHasPressStrategy'      => $delivery->executedHasPressStrategy,
                            'executedHasInnovationAction'   => $delivery->executedHasInnovationAction,
                            'executedInnovationTypes'       => $delivery->executedInnovationTypes,
                            'executedDocumentationTypes'    => $delivery->executedDocumentationTypes,
                        ];
                    }

                    return $result;
                }
            }
        ]);

        $app->registerController('projectReporting', Controller::class);
    }

}
