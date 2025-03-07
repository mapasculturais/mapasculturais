<?php

namespace EvaluationMethodContinuous;

use MapasCulturais\App;
use MapasCulturais\Definitions\ChatThreadType;
use MapasCulturais\Entities;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Entities\ChatThread;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use MapasCulturais\i;
class Module extends \MapasCulturais\EvaluationMethod {
    const CHAT_THREAD_TYPE = 'EvaluationMethodContinuous';

    public $internal = true;

    public function getSlug() {
        return 'continuous';
    }

    public function getName() {
        return i::__('Avaliação Contínua');
    }

    public function getDescription() {
        return i::__('Consiste num select box com os status possíveis para uma inscrição.');
    }

    public function cmpValues($value1, $value2){
        $value1 = (float) $value1;
        $value2 = (float) $value2;
        
        return parent::cmpValues($value1, $value2);
    }

    public function getConfigurationFormPartName() {
        return null;
    }

    protected function _register() {
        $app = App::i();
        
        $app->registerJobType(new JobTypes\Spreadsheet('continuous-spreadsheets'));
        
        $this->registerOpportunityMetadata('allow_proponent_response', [
            'type' => "checkbox",
            'label' => \MapasCulturais\i::__('Possibilitar mais de uma resposta do proponente'),
        ]);

        $thread_type_description = i::__('Conversação entre proponente e avaliador');
        $definition = new ChatThreadType(self::CHAT_THREAD_TYPE, $thread_type_description, function (ChatMessage $message) {
            /** @var ChatThreadType $this */
            $thread = $message->thread;
            $registration = $thread->ownerEntity;
            $notification_content = '';
            $sender = '';
            $recipient = '';
            $notification = new Notification;
            if ($message->thread->checkUserRole($message->user, 'admin')) {
                // mensagem do parecerista
                $notification->user = $registration->owner->user;
                $notification_content = i::__("Nova mensagem do parecerista na inscrição número %s");
                $sender = 'admin';
                $recipient = 'participant';
            } else {
                // mensagem do usuário responsável pela inscrição
                $notification->user = $registration->owner->user;
                $notification_content = i::__("Nova mensagem na inscrição número %s");
                $sender = 'participant';
                $recipient = 'admin';
            }
            $notification->message = sprintf($notification_content, "<a href=\"{$registration->singleUrl}\" >{$registration->number}</a>");
            $notification->save(true);
            $this->sendEmailForNotification($message, $notification, $sender, $recipient);
        });
        $app->registerChatThreadType($definition);
    }

    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data)
    {   
        $errors = [];

        foreach($data as $key => $val){
            if($key === i::__('status') && !trim($val)) {
                $errors[] = i::__('O campo Status é obrigatório');
            }
            
            if($key === i::__('obs') && !trim($val)) {
                $errors[] = i::__('O campo Observações é obrigatório');
            }
        }

        return $errors;
    }

    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'continuous-evaluation-form', 'js/ng.evaluationMethod.continuous.js', ['entity.module.opportunity']);
        $app->view->enqueueStyle('app', 'continuous-evaluation-method', 'css/continuous-evaluation-method.css');

        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.continuous';

        $app->view->jsObject['evaluationStatus']['continuous'] = $this->evaluationStatues;

        $app->view->localizeScript('continuousEvaluationMethod', [
            'saved' => i::__('Avaliação salva'),
            'applyEvaluationsError' => i::__('É necessário selecionar os campos Avaliação e Status'),
            'applyEvaluationsSuccess' => i::__('Avaliações aplicadas com sucesso'),
            'applyEvaluationsNotApplied' => i::__('As avaliações não foram aplicadas.'),
        ]);
    }

    public function _init()
    {
        $app = App::i();

        $self = $this;
        
        $app->hook('template(opportunity.registrations.registration-list-actions-entity-table):begin', function($entity){
            if($em = $entity->evaluationMethodConfiguration){
                if($em->getEvaluationMethod()->slug == "continuous"){
                    $this->part('continuous--evaluation-result-apply');
                }
            }
        });

        $app->hook('repo(Registration).getIdsByKeywordDQL.where', function(&$where, $keyword, $alias) {
            $key = trim(strtolower(str_replace('%','',$keyword)));
            
            $value = null;
            if (in_array($key, explode(',', i::__('inválida,invalida,inválido,invalido')))) {
                $value = '2';
            } else if (in_array($key, explode(',', i::__('não selecionado,nao selecionado,não selecionada,nao selecionada')))) {
                $value = '3';
            } else if ($key == i::__('suplente')) {
                $value = '8';
            } else if (in_array($key, explode(',', i::__('selecionado,selecionada')))) {
                $value = '10';
            }

            if ($value) {
                $where .= " OR e.consolidatedResult = '$value'";
            } 
            
            $where .= " OR unaccent(lower(e.consolidatedResult)) LIKE unaccent(lower(:{$alias}))";
        });

        $app->hook('evaluationsReport(continuous).sections', function (Entities\Opportunity $opportunity, &$sections) use ($app) {
            $columns = [];
            $evaluations = $opportunity->getEvaluations();

            foreach ($evaluations as $eva) {
                $evaluation = $eva['evaluation'];
                $data = (array)$evaluation->evaluationData;
                foreach ($data as $id => $d) {
                    $columns[$id] = $d;
                }
            }

            $result = [
                'registration' => $sections['registration'],
                'committee' => $sections['committee'],
            ];

            $sections['evaluation']->columns['obs'] =  (object)[
                'label' => i::__('Observações'),
                'getValue' => function (Entities\RegistrationEvaluation $evaluation) {
                    $evaluation_data = (array)$evaluation->evaluationData;
                    if (isset($evaluation_data) && isset($evaluation_data['obs'])) {
                        return $evaluation_data['obs'];
                    } else {
                        return '';
                    }
                }
            ];

            $result['evaluation'] = $sections['evaluation'];

            $sections = $result;
        });

        $app->hook('POST(opportunity.applyEvaluationsContinuous)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'continuous') {
                $this->errorJson(i::__('Somente para avaliações contínuas'), 400);
                die;
            }

            if (!is_numeric($this->data['to']) || !in_array($this->data['to'], [0,1,2,3,8,10])) {
                $this->errorJson(i::__('os status válidos são 0,1,2, 3, 8 e 10'), 400);
                die;
            }

            $new_status = intval($this->data['to']);
            
            $apply_status = $this->data['status'] ?? false;
            if ($apply_status == 'all') {
                $status = 'r.status > 0';
            } else {
                $status = 'r.status = 1';
            }
    
            $opp->checkPermission('@control');
            
            // pesquise todas as registrations da opportunity que esta vindo na request
            $dql = "
            SELECT 
                r.id
            FROM
                MapasCulturais\Entities\Registration r
            WHERE 
                r.opportunity = :opportunity_id AND
                r.consolidatedResult = :consolidated_result AND
                r.status <> $new_status AND
                $status 
            ";
            $query = $app->em->createQuery($dql);
        
            $params = [
                'opportunity_id' => $opp->id,
                'consolidated_result' => $this->data['from']
            ];
    
            $query->setParameters($params);
    
            $registrations = $query->getScalarResult();
            
            $count = 0;
            $total = count($registrations);

            if ($total > 0) {
                $opp->enqueueToPCacheRecreation();
            }

            // faça um foreach em cada registration e pegue as suas avaliações
            foreach ($registrations as $reg) {
                $count++;
                $registration = $app->repo('Registration')->find($reg['id']);
                $registration->__skipQueuingPCacheRecreation = true;

                $app->log->debug("{$count}/{$total} Alterando status da inscrição {$registration->number} para {$new_status}");
                
                switch ($new_status) {
                    case Registration::STATUS_DRAFT:
                        $registration->setStatusToDraft();
                    break;
                    case Registration::STATUS_INVALID:
                        $registration->setStatusToInvalid();
                    break;
                    case Registration::STATUS_NOTAPPROVED:
                        $registration->setStatusToNotApproved();
                    break;
                    case Registration::STATUS_WAITLIST:
                        $registration->setStatusToWaitlist();
                    break;
                    case Registration::STATUS_APPROVED:
                        $registration->setStatusToApproved();
                    break;
                    default:
                        $registration->_setStatusTo($new_status);
                    
                }
                $app->disableAccessControl();
                $registration->save(true);
                $app->enableAccessControl();

                $app->em->clear();
            }

            // colocar a oportunidade para regeração de cache

            $this->finish(sprintf(i::__("Avaliações aplicadas à %s inscrições"), count($registrations)), 200);
    
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function() use($app, $self) {
            $opportunity = $this->controller->requestedEntity;
    
            if ($opportunity->evaluationMethodConfiguration->getDefinition()->slug != 'continuous') {
                return;
            }
            
            $consolidated_results = $self->findConsolidatedResult($opportunity);
            
            $this->part('continuous--apply-results', ['entity' => $opportunity, 'consolidated_results' => $consolidated_results]);
        });

        
        $app->hook('entity(ChatMessage).save:finish', function() use ($app, $self){
            /** @var \MapasCulturais\Entities\ChatMessage $this */
            if ($this->thread->ownerEntity instanceof Entities\Registration && 
                $this->thread->ownerEntity->evaluationMethod instanceof $self && 
                $app->user->canUser('evaluateOnTime')) {
                    $data = (object) $this->payload;

                    // altera o status de uma avaliação de acordo com o status da mensagem do chat
                    $evaluation = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $this->thread->ownerEntity]);
                    if ($evaluation && isset($data->status)) {

                        $evaluation->result = $data->status;
                        $app->disableAccessControl();
                        $evaluation->save(true);
                        $app->enableAccessControl();

                        $registration = $evaluation->registration;
                        $opportunity = $registration->opportunity;
                        $evaluation_type = $opportunity->evaluationMethodConfiguration->type->id;

                        if($opportunity->evaluationMethodConfiguration->autoApplicationAllowed) {
                            if($registration->needsTiebreaker() && !$registration->evaluationMethod->getTiebreakerEvaluation($registration)) {
                                return;
                            }
                            $conn = $app->em->getConnection();
                            $evaluations = $conn->fetchAll("
                                SELECT
                                *
                                FROM
                                    evaluations
                                WHERE
                                    registration_id = {$registration->id}"
                            );

                            $all_status_sent = true;

                            foreach ($evaluations as $ev) {
                                if ($ev['evaluation_status'] !== RegistrationEvaluation::STATUS_SENT) {
                                    $all_status_sent = false;
                                }
                            }

                            if ($all_status_sent) {
                                if($evaluation_type == 'appeal-phase') {
                                    $value = $data->status;
                                }

                                $app->disableAccessControl();
                                $registration->setStatus($value);
                                $registration->consolidateResult();
                                $registration->save();
                                $app->enableAccessControl();
                            }
                        }
                    }

                    // altera o status do chat de acordo com o checkbox `endChat` da mensagem
                    $end_chat = $data->endChat ?? false;
                    if ($end_chat) {
                        $thread = $app->repo('ChatThread')->findOneBy(['id' => $this->thread->id]);
                        if ($thread) {
                            $thread->status = ChatThread::STATUS_CLOSED;
                            $app->disableAccessControl();
                            $thread->save(true);
                            $app->enableAccessControl();
                        }
                    }

            }
        });

        $app->hook('mapas.printJsObject:before', function() use ($self) {
            /** @var \MapasCulturais\Theme $this */
            $this->jsObject['config']['evaluationMethodAppealPhase'] = [
                'statuses' => $self->getStatuses(),
            ];
        });

        $app->hook('entity(Opportunity).get(avaliableEvaluationFields)', function(&$value) use ($app) {
            // Verificar se a fase ($this) tem uma fase de recurso ($this->appealPhase);
            // Se o usuário logado é avaliador do recurso
            // Caso seja, Adiciona todos os campos dessa fase até a primeira ($this->previousPhase)
            
            if ($this->isAppealPhase) {
                $appeal_phase = $this;
            } else {
                $appeal_phase = $this->appealPhase;
            }
            
            if (!$appeal_phase) {
                return;
            }

            if ($appeal_phase->canUser('evaluateRegistrations')) {
                $fields = [];
                
                $phase = $this;
                do {
                    $fields = array_merge($fields, $this->registrationFieldConfigurations, $this->registrationFileConfigurations);
                } while ($phase = $phase->previousPhase);
                
                foreach ($fields as $field) {
                    if ($field instanceof RegistrationFieldConfiguration) {
                        $value[$field->fieldName] = "true";
                    }
                    
                    if ($field instanceof RegistrationFileConfiguration) {
                        $value[$field->fileGroupName] = "true";
                    }
                }
            }
        });

        //Ativação do chat
        $app->hook('entity(Registration).send:after', function() use ($app) {
            /** @var Registration $this */
        
            $opportunity = $this->opportunity;
            $evaluation_method = $opportunity->evaluationMethod;
            if ($evaluation_method && $evaluation_method->slug == 'continuous' && $opportunity->allow_proponent_response) {
                
                $group = i::__('Avaliadores');
                $chat_thread = new ChatThread($this->refreshed(), $this, self::CHAT_THREAD_TYPE);
                $chat_thread->save(true);
                
                if($committee = $opportunity->getEvaluationCommittee(false)){

                    $app->disableAccessControl();
                    
                    foreach ($committee as $agent) {
                        $chat_thread->createAgentRelation($agent->refreshed(), $group, true);
                    }

                    $app->enableAccessControl();
                }
            }
        });

        // Permite que avaliadores modifiquem o status da avaliação contínua da fase de recursos
        $app->hook('entity(Registration).canUser(evaluate)', function($user, &$result) use($app){
            /** @var Registration $this */
            $opportunity = $this->opportunity;
            $evaluation_method = $opportunity->evaluationMethod;


            // Verifica se a oportunidade está na fase de recurso
            if($evaluation_method && $evaluation_method->slug == 'continuous' && $opportunity->allow_proponent_response) {
                $chat_thread = $app->repo('ChatThread')->findOneBy(['identifier' => "{$this}"]);

                // Verifica se o chat está ativo
                if($chat_thread && $chat_thread->status == $chat_thread::STATUS_ENABLED) {
                    $result = true;
                }
            }
        });
    }

    public function findConsolidatedResult($opportunity)
    {
        $app = App::i();
        
        $consolidated_results = $app->em->getConnection()->fetchAll("
        SELECT 
            consolidated_result evaluation,
            COUNT(*) as num
        FROM 
            registration
        WHERE 
            opportunity_id = :opportunity AND
            status > 0 
        GROUP BY consolidated_result
        ORDER BY num DESC", ['opportunity' => $opportunity->id]);

        return $consolidated_results;
    }

    public function getEvaluationStatues()
    {
        $status = [
            'valid' => ['10'],
            'invalid' => ['2','3', '8']
        ];

        return $status;
    }

    public function _getConsolidatedResult(Entities\Registration $registration, array $evaluations) {
        $app = App::i();

        $result = 10;
        foreach ($evaluations as $eval){
            $eval_result = $this->getEvaluationResult($eval);
            if($eval_result < $result){
                $result = $eval_result;
            }
        }

        return $result;
    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation) {
        if ($evaluation->evaluationData->status) {
            return $evaluation->evaluationData->status;
        } else {
            return null;
        }
    }

    function getStatuses() {
        $statuses = [
            '10' => i::__('Deferido'),
            '3' => i::__('Indeferido'),
            '2' => i::__('Negado'),
        ];

        return $statuses;
    }

    protected function _valueToString($value) {
        $statuses = self::getStatuses();
        return $statuses[$value] ?? $value;
    }

    function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): array {
        $evaluation_configuration = $evaluation->registration->opportunity->evaluationMethodConfiguration;
        return [
            'entityEvaluation' => $evaluation,
            'obs' => $evaluation->evaluationData->obs
        ];
    }

    function _getConsolidatedDetails(Entities\Registration $registration): ?array {
        return null;
    }

}