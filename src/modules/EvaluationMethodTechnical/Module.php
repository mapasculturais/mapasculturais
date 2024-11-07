<?php

namespace EvaluationMethodTechnical;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Controller;
use MapasCulturais\Controllers\Opportunity as ControllersOpportunity;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

class Module extends \MapasCulturais\EvaluationMethod {
    
    protected static Module $instance;
    private $viability_status;

    public static $quotaData = null;

    function __construct(array $config = []) {
        self::$instance = $this;
        $config += ['step' => '0.1'];
        parent::__construct($config);
    }

    /**
     * Retorna a instância do módulo
     * @return Module
     */
    public static function i(): Module {
        return self::$instance;
    }

    public function getSlug() {
        return 'technical';
    }

    public function getName() {
        return i::__('Avaliação Técnica');
    }

    public function getDescription() {
        return i::__('Consiste em avaliação por critérios e cotas.');
    }

    public function filterEvaluationsSummary(array $data) {
        $items = array_filter(array_keys($data), function($item) {
            return is_numeric($item) ? $item : null;            
        });
        
        // encontra o maior valor do array
        $max_value = $items ? max($items) + 1 : null;
        
        // divide em 5 faixas
        $result = [];

        $non_numeric = [];
        foreach($data as $val => $sum) {
            if(!is_numeric($val)) {
                $non_numeric[$val] = $non_numeric[$val] ?? 0;
                $non_numeric[$val] += $sum;
            
            } else if($max_value) {
                for($i=0;$i<5;$i++){
                    $min = $i * $max_value / 5;
                    $max = ($i+1) * $max_value / 5;
                    if($val >= $min && $val < $max) {
                        $min = number_format($i * $max_value / 5,       1, ',', '.');
                        $max = number_format(($i+1) * $max_value / 5,   1, ',', '.');

                        $key = "{$min} - {$max}";
                        $result[$key] = $result[$key] ?? 0;
                        $result[$key] += $sum;
                    }
                }
            }
        }

        $result += $non_numeric;
        return $result;
    }

    public function cmpValues($value1, $value2){
        $value1 = (float) $value1;
        $value2 = (float) $value2;
        
        return parent::cmpValues($value1, $value2);
    }

    public function getStep(){
        return $this->_config['step'];
    }
    
    protected function _register() {
        $app = App::i();
        
        $this->registerEvaluationMethodConfigurationMetadata('sections', [
            'label' => i::__('Seções'),
            'type' => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('criteria', [
            'label' => i::__('Critérios'),
            'type' => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);
        
        $this->registerEvaluationMethodConfigurationMetadata('pointReward', [
            'label' => i::__('Bônus por pontuação'),
            'type' => 'json',
            'serialize' => function ($val){
                return (!empty($val)) ? json_encode($val) : "[]";
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerRegistrationMetadata('appliedPointReward', [
            'label' => i::__('Bônus por pontuação aplicadas a inscrição'),
            'type' => 'json',
            'private' => true,
            'serialize' => function ($val){
                $val = (!empty($val)) ? $val : ['raw' => null, 'percentage' => null, 'rules' => []];
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('isActivePointReward', [
            'label' => i::__('Controla se as induções por pontuação estão ou não ativadas'),
            'type' => 'boolean'
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('pointRewardRoof', [
            'label' => i::__('Define o valor máximo das induções por pontuação'),
            'type' => 'string',
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('quota', [
            'label' => i::__('Cotas'),
            'type' => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('enableViability',[
            'label' => i::__('Exequibilidade da inscrição'),
            'description' => i::__('Ao habilitar esta configuração, os avaliadores deverão considerar a exequibilidade da inscrição. Se a maioria dos avaliadores considerarem a inabilitação por exequibilidade, a mesma será marcada com o status de inválida para o dono do edital, que ainda assim poderá mudar seu status para válida.'),
            'type' => 'radio',
            'options' => array(
                'true' => i::__('Habilitar'),
                'false' => i::__('Não habilitar'),
            ),
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('geoQuotaConfiguration', [
            'label' => i::__('Configuração territorial'),
            'type' => 'json',
            'default' => json_encode(['distribution' => (object) [], 'geoDivision' => null])
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('tiebreakerCriteriaConfiguration', [
            'label' => i::__('Definição dos critérios de desempate'),
            'type' => 'json',
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('quotaConfiguration', [
            'label' => i::__('Configuração de cotas'),
            'type' => 'json',
            'serialize' => function ($val){
                return (!empty($val)) ? json_encode($val) : "[]";
            },
            'unserialize' => function($val){
                return json_decode((string) $val);
            }
        ]);

        $this->registerRegistrationMetadata('appliedForQuota', [
            'label' => i::__('A inscrição está concorrendo por cotas?'),
            'type' => 'boolean',
            'private' => false,
            'default' => false
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('cutoffScore', [
            'label' => i::__('Nota de corte'),
            'type' => 'float',
        ]);

        $this->registerOpportunityMetadata('enableQuotasQuestion', [
            'label' => "Vai concorrer às cotas",
            'type' => 'boolean',
            'private' => false,
            'field_type' => 'radio',
            'default' => '0',
            'options' => (object) array(
                '0' => \MapasCulturais\i::__('Desabilitado'),
                '1' => \MapasCulturais\i::__('Habilitado'),
            ),
        ]);

        $this->registerOpportunityMetadata('considerQuotasInGeneralList', [
            'label' => i::__('Considerar os cotistas dentro da listagem da ampla concorrência'),
            'description' => i::__('Ao habilitar esta configuração, os cotistas seráo considerados na listagem da ampla concorrência.'),
            'type' => 'boolean',
            'private' => false,
            'default' => true,
        ]);

        $app->registerJobType(new JobTypes\Spreadsheet('technical-spreadsheets'));
    }

    function enqueueScriptsAndStyles() {
        $app = App::i();
        $app->view->enqueueStyle('app', 'technical-evaluation-method', 'css/technical-evaluation-method.css');
        $app->view->enqueueScript('app', 'technical-evaluation-form', 'js/ng.evaluationMethod.technical.js', ['entity.module.opportunity']);

        $app->view->localizeScript('technicalEvaluationMethod', [
            'sectionNameAlreadyExists' => i::__('Já existe uma seção com o mesmo nome'),
            'changesSaved' => i::__('Alteraçṍes salvas'),
            'deleteSectionConfirmation' => i::__('Deseja remover a seção? Esta ação não poderá ser desfeita e também removerá todas os critérios desta seção.'),
            'deleteCriterionConfirmation' => i::__('Deseja remover este critério de avaliação? Esta ação não poderá ser desfeita.'),
            'deleteAffirmativePolicy' => i::__('Deseja remover esta política afirmativa? Esta ação não poderá ser desfeita.')
        ]);
        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.technical';
    }

    public function _init() {
        $app = App::i();

        $self = $this;


        // Define o valor da coluna eligible
        $app->hook('entity(Registration).<<save|send>>:before', function() use($app){
            /** @var Registration $this */
            if($this->evaluationMethod && $this->evaluationMethod->slug == 'technical') {
                $this->eligible = $this->isEligibleForAffirmativePolicies();
            }
        });

        // Define o valor da coluna eligible da inscrição no momento do update
        $app->hook('entity(Registration).<<save|send>>:after', function() use($app){
            /** @var Registration $this */
            $app->disableAccessControl();

            if($next_phase = $this->nextPhase){
                $next_phase->eligible = $this->eligible;
                $next_phase->save(true);
            }
            $app->enableAccessControl();
        });

        // Devolve se a inscrição é elegível a participar das cotas
        $app->hook('Entities\\Registration::isEligibleForAffirmativePolicies', function() use($self) {
            /** 
             * @var Registration $this 
             * @var Module $self 
             **/
            $registration = $this;
            $em = $registration->evaluationMethodConfiguration;
            
            $registration->isFirstPhase;
            $opportunity_first_phase = $registration->opportunity->firstPhase;
            $_registration = $registration->isFirstPhase ? $this : $this->firstPhase;
            if($opportunity_first_phase->enableQuotasQuestion && !$_registration->appliedForQuota) {
                return false;
            }
            
            if(!$em) {
                return false;
            }

            if($quota_configurations = $em->quotaConfiguration) {
                if($rules = $quota_configurations->rules) {
                    foreach($rules as $rule) {
                        if($self->qualifiesForQuotaRule($rule, $registration)) {
                            return true;
                        }
                    }
                }
            }

            return false;
        });

        $app->hook('Entities\\Opportunity::isAffirmativePoliciesActive', function() use($self) {
            /** @var Opportunity $this */
        
            $result = false;
            $opportunity = $this;

            do{
                $em = $opportunity->evaluationMethodConfiguration;
                
                if($em && ($em->quotaConfiguration || $em->pointReward)) {
                    $result = true;
                }

                if($opportunity->isFirstPhase) {
                    $opportunity = null;
                } else {
                    $opportunity = $opportunity->previousPhase;
                }
                
            } while ($opportunity);

            return $result;
        });

        $app->hook('Entities\\Opportunity::hadTechnicalEvaluationPhase', function() use($self) {
            /** @var Opportunity $this */
        
            $result = false;
            $opportunity = $this;
            
            do{
                $em = $opportunity->getEvaluationMethod();
                
                if($em && $em->slug == 'technical') {
                    $result = true;
                }

                if($opportunity->isFirstPhase) {
                    $opportunity = null;
                } else {
                    $opportunity = $opportunity->previousPhase;
                }
                
            } while ($opportunity);

            return $result;
        });
        
        $app->hook('entity(Registration).consolidateResult', function(&$result) use ($self) {
            /** @var Registration $this */
            $app = App::i();

            $em = $this->evaluationMethod;
            if($em->slug === "technical") {
                $registration = $this;
                $connection = $app->em->getConnection();

                $app->disableAccessControl();

                $score = $self->applyPointReward((float) $this->consolidatedResult, $registration);
                $eligible = $this->isEligibleForAffirmativePolicies() ? 'true' : 'false';
                
                do {
                    $connection->executeQuery("UPDATE registration SET score = :score, eligible = '{$eligible}' WHERE id = :id", [
                        'score' => $score,
                        'id' => $registration->id
                    ]);
                }
                while($registration = $this->nexPhase);
               
                $app->enableAccessControl();
                
                // limpa o cache das cotas
                $cache_key = "{$this->opportunity}:quota-registrations";
                $app->cache->delete($cache_key);
                
            }
           
        });

        $app->hook('ApiQuery(registration).params', function(&$params) use($app) {
            /** @var ApiQuery $this */

            $order = $params['@order'] ?? '';
            preg_match('#EQ\((\d+)\)#', $params['opportunity'] ?? '', $matches);
            $phase_id = $matches[1] ?? null;
            if(!$phase_id) {
                return;
            }
            $opportunity = $app->repo('Opportunity')->find($phase_id);
            $evaluation_method = $opportunity->evaluationMethodConfiguration;
            $quota = $evaluation_method && $evaluation_method->type->id == 'technical' ? 
                Quotas::instance($phase_id) : null;
            
            if($phase_id && $quota && is_null(Module::$quotaData)) {
                Module::$quotaData = (object) [];                

                Module::$quotaData->objectId = spl_object_id($this);
                Module::$quotaData->params = $params;
                Module::$quotaData->quota = $quota;
                Module::$quotaData->orderByQuota = $order == '@quota';

                $quota_order = Module::$quotaData->quota->getRegistrationsOrderByScoreConsideringQuotas($params);
                $opportunity = $app->repo('Opportunity')->find($phase_id);
                $opportunity->registerRegistrationMetadata();
                
                if(Module::$quotaData->orderByQuota && $limit = (int) ($params['@limit'] ?? 0)) {
                    unset($params['@order']);
                    $ids_params = $params;
                    unset(
                        $ids_params['@limit'], 
                        $ids_params['@order'], 
                        $ids_params['@page'],
                        $ids_params['oppotunity'],
                    );
                    $ids_params['@select'] = 'id';

                    /** @var ControllersOpportunity $opportunity_controller */
                    $opportunity_controller = $app->controller('opportunity');
                    $result = $opportunity_controller->apiFindRegistrations($opportunity, $ids_params);
                    $_ids = [];
                    foreach($result->registrations as $reg) {
                        $_ids[$reg['id']] = $reg['id'];
                    }

                    $ids = [];
                    foreach($quota_order as $reg) {
                        if(isset($_ids[$reg->id])) {
                            $ids[] = $reg->id;
                        }
                    }

                    Module::$quotaData->foundIds = $ids;

                    $page = $params['@page'] ?? 1;
                    $offset = ($page - 1) * $limit;
                    $ids = array_slice($ids, $offset, $limit);
                    // eval(\psy\sh());
                    
                } else {
                    $ids = array_map(fn($reg) => $reg->id, $quota_order);
                    Module::$quotaData->foundIds = $ids;
                }

                if(Module::$quotaData->orderByQuota){
                    
                    $params['id'] = API::IN($ids);
                    unset(
                        $params['@page'],
                        $params['@limit']
                    );
                }

                Module::$quotaData->order = $quota_order;
                Module::$quotaData->ids = $ids;

            }
        });

        $app->hook('ApiQuery(registration).findResult', function(&$result) {
            /** @var ApiQuery $this */
            if((Module::$quotaData->objectId ?? false) == spl_object_id($this)) {
                $app = App::i();
                $_new_result = [];
                $quota_fields = Module::$quotaData->quota->registrationFields;
                foreach(Module::$quotaData->ids as $id) {
                    foreach($result as &$registration) {
                        if($registration['id'] == $id) {
                            $registration = array_merge($registration, $quota_fields[$id] ?? []);
                            $_new_result[] = $registration;
                        }
                    }
                }

                if(Module::$quotaData->orderByQuota) {
                    $result = $_new_result;
                }

                Module::$quotaData->result = $result;
            }
        });

        $app->hook('ApiQuery(registration).countResult', function(&$result) {
            if((Module::$quotaData->objectId ?? false) == spl_object_id($this)) {
                $result = count(Module::$quotaData->foundIds);
            }
        });
        
        
        $app->hook('API.findRegistrations(opportunity).result', function() {
            /** @var Controller $this */
            $params = $this->data;
            
            if(Module::$quotaData && isset($params['@opportunity'])) {
                $params['opportunity'] = API::EQ($params['@opportunity']);

                $count_query = new ApiQuery(Registration::class, Module::$quotaData->params);
                $count = $count_query->count();
                
                $this->apiAddHeaderMetadata(Module::$quotaData->params, Module::$quotaData->result, $count);
            }
        });

        $app->hook('template(opportunity.registrations.registration-list-actions-entity-table):begin', function($entity){
            if($em = $entity->evaluationMethodConfiguration){
                if($em->getEvaluationMethod()->slug == "technical"){
                    $this->part('technical--apply-results');
                }
            }
        });

        $app->hook('POST(opportunity.applyTechnicalEvaluation)', function() use($self) {
            /** @var ControllersOpportunity $this */
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();

            $opp = $this->requestedEntity;
    
            $opp->checkPermission('@control');

            $type = $opp->evaluationMethodConfiguration->definition->slug;
    
            if($type != 'technical') {
                $this->errorJson(i::__('Somente para avaliações técnicas'), 400);
                die;
            }
            
            $statusIn = API::IN([1,3,8,10]);
            $query_params = [
                '@select' => 'id,score', 
                'opportunity' => "EQ({$opp->id})",
                '@order' => 'score DESC'
            ];

            // TAB POR PONTUAÇÃO
            if($this->data['tabSelected'] === 'score') {
                if(!isset($this->data['setStatusTo'])) {
                    $this->errorJson(i::__('Por favor selecione um status para ser aplicado.'), 400);
                }
                
                if ($this->data['setStatusTo'] && (!is_numeric($this->data['setStatusTo']) || !in_array($this->data['setStatusTo'], [0,1,2,3,8,10]))) {
                    $this->errorJson(i::__('Os status válidos são 0, 1, 2, 3, 8 e 10'), 400);
                    die;
                }

                $new_status = intval($this->data['setStatusTo']);
                $statusNotEqual =  API::NOT_EQ($new_status);
                $min = $this->data['from'][0];
                $max = $this->data['from'][1];
                
                $query_params['status'] = "AND($statusNotEqual, $statusIn)";
                $query_params['score'] = "AND(GTE({$min}), LTE({$max}))";

                $query = new ApiQuery(Registration::class, $query_params);
                $registrations = $query->findIds();
                $total = count($registrations);

                foreach($registrations as $i => $reg) {
                    $count = $i+1;
                    /** @var Registration $registration */
                    $registration = $app->repo('Registration')->find($reg);
                    $registration->skipSync = true;
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
                        case Registration::STATUS_SENT:
                            $registration->setStatusToSent();
                        break;
                        default:
                            $registration->_setStatusTo($new_status);
                    }

                    $app->em->clear();
                }
            }

            // TAB POR CLASSIFICAÇÃO
            if($this->data['tabSelected'] === 'classification') {
                $early_registrations = $this->data['earlyRegistrations'];
                $wait_list = $this->data['waitList'];
                $invalidate_registrations = $this->data['invalidateRegistrations'];

                $cutoff_score = $this->data['cutoffScore'];
                $quantity_vacancies = $this->data['quantityVacancies'];
                $consider_quotas = $this->data['considerQuotas'];

                $query_params['status'] = $statusIn;
                
                // considerar cotas
                if($consider_quotas) {
                    $query_params['@order'] = '@quota';
                }

                $query = new ApiQuery(Registration::class, $query_params);
                $registrations = $query->getFindResult();
                $total = count($registrations);
                
                // selecionar os primeiros
                if($early_registrations) {
                    for($i = 0; $i < $quantity_vacancies; $i++) {
                        $count = $i+1;
                        if($registrations[$i]['score'] >= $cutoff_score) {
                            $registration_id = $registrations[$i]['id'];
                            /** @var Registration $registration */
                            $registration = $app->repo('Registration')->find($registration_id);
                            $registration->skipSync = true;
                            $registration->__skipQueuingPCacheRecreation = true;

                            $app->log->debug("{$count}/{$total} Alterando status da inscrição {$registration->number} para SELECIONADO");
                            $registration->setStatusToApproved();
                            $app->em->clear();
                        }
                        
                    }
                }

                // marcar como suplente as inscrições com nota acima da nota de corte mas que não foram selecionados (posição inferior ao máximo de vagas)
                if($wait_list) {
                    for($i = $quantity_vacancies; $i < count($registrations); $i++) {
                        $count = $i+1;
                        if($registrations[$i]['score'] >= $cutoff_score) {
                            $registration_id = $registrations[$i]['id'];
                            /** @var Registration $registration */
                            $registration = $app->repo('Registration')->find($registration_id);
                            $registration->skipSync = true;
                            $registration->__skipQueuingPCacheRecreation = true;

                            $app->log->debug("{$count}/{$total} Alterando status da inscrição {$registration->number} para SUPLENTE");
                            $registration->setStatusToWaitlist();
                            $app->em->clear();
                        }  
                    }
                }

                // eliminar as inscrições com nota inferior a nota de corte
                if($invalidate_registrations) {
                    foreach($registrations as $i => $reg) {
                        $count = $i+1;
                        if($reg['score'] < $cutoff_score) {
                            /** @var Registration $registration */
                            $registration = $app->repo('Registration')->find($reg['id']);
                            $registration->skipSync = true;
                            $registration->__skipQueuingPCacheRecreation = true;

                            $app->log->debug("{$count}/{$total} Alterando status da inscrição {$registration->number} para INVÁLIDO");
                            $registration->setStatusToNotApproved();
                            $app->em->clear();
                        }
                    }
                }
            }

            if($next_phase = $opp->nextPhase) {
                $next_phase->enqueueRegistrationSync();
            }

            $opp->enqueueToPCacheRecreation();

            $this->finish(sprintf(i::__("Avaliações aplicadas à %s inscrições"), count($registrations)), 200);
        });


         // Reconsolida a avaliação da inscrição caso em fases posteriores exista avaliação técnica com bônus por pontuação aplicadas
         $app->hook('entity(Registration).update:after', function() use ($app){
            /** @var \MapasCulturais\Entities\Registration $this */
            $phase = $this;
            
            do{
                if($em = $phase->getEvaluationMethod()){
                    if($em->getSlug() == "technical"){
                        $app->disableAccessControl();
                        $phase->consolidateResult();
                        $app->enableAccessControl();
                    }
                }
            }while($phase = $phase->nextPhase);
        });


        // Insere valores das bônus por pontuação aplicadas na planilha de inscritos
        $app->hook('opportunity.registrations.reportCSV', function(\MapasCulturais\Entities\Opportunity $opportunity, $registrations, &$header, &$body) use ($app){
            
            $isActivePointReward = $opportunity->evaluationMethodConfiguration ? filter_var($opportunity->evaluationMethodConfiguration->isActivePointReward, FILTER_VALIDATE_BOOL) : false;

            if($isActivePointReward){

                $header[] = 'POLITICAS-AFIRMATIVAS';
                            
                foreach($body as $i => $line){    
                    $reg = $app->repo("Registration")->findOneBy(['number' => $line[0], 'opportunity' => $opportunity]);

                    $policies = $reg->appliedPointReward;

                    if(!$policies || !$policies->rules){
                        continue;
                    }

                    $valuePencentage = (($policies->raw * $policies->percentage)/100);
                    $cell = "";
                    $cell.= "Bônus por pontuação atribuídos \n\n";
                    foreach($policies->rules as $k => $rule){
                        $_value = is_array($rule->value) ? implode(",", $rule->value) : $rule->value;
                        $cell.= "{$rule->field->title}: {$_value} (+{$rule->percentage}%)\n";
                        $cell.= "-------------------- \n";
                    }
                    
                    $cell.= "\nAvaliação Técnica: {$policies->raw} \n";
                    $cell.= "valor somado ao resultado: {$valuePencentage} (+{$policies->percentage}%)\n";
                    $cell.= "Resultado final: {$reg->consolidatedResult} \n";
                    $body[$i][] = $cell;
                }

            }

        });

        // passa os dados de configuração das bônus por pontuação para JS
        $app->hook('GET(opportunity.edit):before', function() use ($app, $self){
            $entity = $this->requestedEntity;
            if($entity->evaluationMethodConfiguration){
                $app->view->jsObject['pointsByInductionFieldsList'] = $self->getFieldsAllPhases($entity);
               
                $evaluationMethodConfiguration = $entity->evaluationMethodConfiguration;
    
                $app->view->jsObject['isActivePointReward'] = $evaluationMethodConfiguration->isActivePointReward;
                $app->view->jsObject['pointReward'] = $evaluationMethodConfiguration->pointReward;
                $app->view->jsObject['pointRewardRoof'] = $evaluationMethodConfiguration->pointRewardRoof;
            }
        });

        $app->hook('evaluationsReport(technical).sections', function(Entities\Opportunity $opportunity, &$sections){
            $i = 0;
            $get_next_color = function($last = false) use(&$i){
                $colors = [
                    '#FFAAAA',
                    '#BB8888',
                    '#FFAA66',
                    '#AAFF00',
                    '#AAFFAA'
                ];

                $result = $colors[$i];

                $i++;

                return $result;
            };

            $cfg = $opportunity->evaluationMethodConfiguration;

            $result = [
                'registration' => $sections['registration'],
                'committee' => $sections['committee'],
            ];
            foreach($cfg->sections as $sec){
                $section = (object) [
                    'label' => $sec->name,
                    'color' => $get_next_color(),
                    'columns' => []
                ];

                foreach($cfg->criteria as $crit){
                    if($crit->sid != $sec->id) {
                        continue;
                    }

                    $section->columns[] = (object) [
                        'label' => $crit->title . ' ' . sprintf(i::__('(peso: %s)'), $crit->weight),
                        'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($crit) {
                            return isset($evaluation->evaluationData->{$crit->id}) ? $evaluation->evaluationData->{$crit->id} : '';
                        }
                    ];
                }

                $max = 0;
                foreach($cfg->criteria as $crit){
                    if($crit->sid != $sec->id) {
                        continue;
                    }

                    $max += $crit->max * $crit->weight;
                }

                $section->columns[] = (object) [
                    'label' => sprintf(i::__('Subtotal (max: %s)'),$max),
                    'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($sec, $cfg) {
                        $result = 0;
                        foreach($cfg->criteria as $crit){
                            if($crit->sid != $sec->id) {
                                continue;
                            }

                            $val =  isset($evaluation->evaluationData->{$crit->id}) ? (float) $evaluation->evaluationData->{$crit->id} : 0;
                            $weight = (float) $crit->weight;
                            $result += $val * $weight;

                        }

                        return $result;
                    }
                ];

                $result[] = $section;
            }

            $result['evaluation'] = $sections['evaluation'];
//            $result['evaluation']->color = $get_next_color(true);


            // adiciona coluna do parecer técnico
            $result['evaluation']->columns[] = (object) [
                'label' => i::__('Parecer Técnico'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($crit) {
                    return isset($evaluation->evaluationData->obs) ? $evaluation->evaluationData->obs : '';
                }
            ];

            $viability = [
                'label' => i::__('Esta proposta apresenta exequibilidade?'),
                'getValue' => function(Entities\RegistrationEvaluation $evaluation) {
                    return $this->viabilityLabel($evaluation);
                }
            ];

            $result['evaluation']->columns[] = (object) $viability;

            $sections = $result;

            $this->viability_status = [
                'valid' => i::__('Válido'),
                'invalid' => i::__('Inválido')
            ];
        });

        $app->hook('entity(Opportunity).propertiesMetadata', function(&$result) {
            $result['affirmativePoliciesEligibleFields'] = [
                'label' => i::__('Campos disponíveis para políticas afirmativas'),
                'type' => 'array',
                'isEntityRelation' => false,
                'isMetadata' => false,
                'private' => false,
                'required' => false
            ];
        });
        
        // Cria a affirmativePoliciesEligibleFields com os campos da fase atual e anterior
        $app->hook('entity(Opportunity).jsonSerialize', function(&$result) {
            /** @var Entities\Opportunity $this */
            if($this->evaluationMethodConfiguration && $this->evaluationMethodConfiguration->definition->slug == 'technical') {
                $result['affirmativePoliciesEligibleFields'] = $this->getFields();
            }
        });
    }

    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data){
        $errors = [];
        $empty = false;

        if ($evaluation_method_configuration->enableViability === "true" && !array_key_exists('viability',$data)) {
            $empty = true;
            $errors[] = i::__('Informe sobre a exequibilidade orçamentária desta inscrição!');
        }

        foreach($data as $key => $val){
            if ($key === 'viability' && empty($val)) {
                $empty = true;
            } else if($key === 'obs' && !trim($val)) {
                $empty = true;
            } else if($key !== 'obs' && $key !== 'viability' && !is_numeric($val)){
                $empty = true;
            }
        }

        if($empty){
            $errors[] = i::__('Todos os campos devem ser preenchidos');
        }

        if(!$errors){
            foreach($evaluation_method_configuration->criteria as $c){
                if(isset($data[$c->id])){
                    $val = (float) $data[$c->id];
                    if($val > (float) $c->max){
                        $errors[] = sprintf(i::__('O valor do campo "%s" é maior que o valor máximo permitido'), $c->title);
                        break;
                    } else if($val < (float) $c->min) {
                        $errors[] = sprintf(i::__('O valor do campo "%s" é menor que o valor mínimo permitido'), $c->title);
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    public function _getConsolidatedResult(\MapasCulturais\Entities\Registration $registration, array $evaluations) {
        $app = App::i();
        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();
        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $result = 0;
        foreach ($evaluations as $eval){
            $result += $this->getEvaluationResult($eval);
        }

        $num = count($evaluations);
        if($num){
            return number_format($result / $num, 2);
        } else {
            return null;
        }
    }

    public function applyPointReward($result, \MapasCulturais\Entities\Registration $registration)
    {
        $app = App::i();

        $reg = $registration;
        
        do{
            $reg->registerFieldsMetadata();
        } while($reg = $reg->previousPhase);
        
        $affirmativePoliciesConfig = $registration->opportunity->evaluationMethodConfiguration->pointReward;
        $pointRewardRoof = $registration->opportunity->evaluationMethodConfiguration->pointRewardRoof;
        $isActivePointReward = filter_var($registration->opportunity->evaluationMethodConfiguration->isActivePointReward, FILTER_VALIDATE_BOOL);
        $metadata = $registration->getRegisteredMetadata();
       
        if(!$isActivePointReward || !array_filter($affirmativePoliciesConfig) || empty($affirmativePoliciesConfig)){
            return $result;
        }

        $totalPercent = 0.00;
        $appliedPolicies = [];
        foreach($affirmativePoliciesConfig as $rules){
            if(empty($metadata) || empty($rules) || !$rules){
                continue;
            }
            
            $fieldName = "field_".$rules->field;
            $applied = false;
            $field_conf = $metadata[$fieldName]->config['registrationFieldConfiguration'];

            if($field_conf->categories && !in_array($registration->category, $field_conf->categories)){
                continue;
            }

            if($field_conf->conditional){
                $_field_name = $field_conf->conditionalField;
                if(trim($registration->$_field_name) != trim($field_conf->conditionalValue)){
                    continue;
                }
            }

            if(is_object($rules->value) || is_array($rules->value)){

                foreach($rules->value as $key => $value){
                    if(is_array($registration->$fieldName)){
                        if(in_array($key, $registration->$fieldName) && filter_var($value, FILTER_VALIDATE_BOOL)){
                            $_value = $key;
                            $applied = true;
                            continue;
                        }

                    }else{
                        if($registration->$fieldName == $key && filter_var($value, FILTER_VALIDATE_BOOL)){
                            $_value = $key;
                            $applied = true;
                            continue;
                        }
                    }
                }
            }else{
                if(filter_var($registration->$fieldName, FILTER_VALIDATE_BOOL) == filter_var($rules->value, FILTER_VALIDATE_BOOL)){
                    $applied = true;
                    $_value = $registration->$fieldName;
                }
            }
        
            if($applied){
                $totalPercent += $rules->fieldPercent;
                $field = $app->repo('RegistrationFieldConfiguration')->find($rules->field);
                $appliedPolicies[] = [
                    'field' => [
                        'title' => $field->title,
                        'id' =>$rules->field
                    ],
                    'percentage' => $rules->fieldPercent,
                    'value' => $_value,
                ];
                continue;
            }
        }
        
        $percentage = (($pointRewardRoof > 0) && $totalPercent > $pointRewardRoof) ? $pointRewardRoof : $totalPercent;

        $registration->appliedPointReward = [
            'raw' => $result,
            'percentage' => $percentage,
            'rules' => $appliedPolicies
        ];

        $registration->save(true);

        if($percentage > 0){
            return $this->percentCalc($result, $percentage);
        }else{
            return $result;
        }

    }

    private function percentCalc($value, $percent)
    {
        return (($value * $percent) /100) + $value;
    }

    /**
     * @param object $rule
     * @param Registration $registration
     * @return boolean
     */
    public function qualifiesForQuotaRule(object $rule, Registration $registration): bool
    {     
        foreach($rule->fields as $field) {
            if($field_name = $field->fieldName) {
                if($val = $registration->$field_name) {
                    
                    if(is_array($val) && array_intersect($val, $field->eligibleValues)) {
                        return true;
    
                    } else if(is_object($field->eligibleValues)) {
                        $eligibleValues = json_decode(json_encode($field->eligibleValues), true);

                        if(array_keys($eligibleValues) !== range(0, count($eligibleValues) - 1)){
                            return in_array($val, array_keys($eligibleValues)) ? true : false;
                            
                        }else {
                            return in_array($val, $eligibleValues) ? true : false;

                        }
                    } else if(in_array($val, ["true", "false"])) {
                        return $val === "true" ? true : false;

                    } else if(in_array($val, $field->eligibleValues)) {
                        return true;

                    }
                }
            }
        }

        return false;
    }
    

    public function getFieldsAllPhases($entity)
    {
            $previous_phases = $entity->previousPhases;

            if($entity->firstPhase->id != $entity->id){
                $previous_phases[] = $entity;
            }

            $fieldsPhases = [];
            foreach($previous_phases as $phase){
                foreach($phase->registrationFieldConfigurations as $field){
                    $fieldsPhases[] = $field;
                }

                foreach($phase->registrationFileConfigurations as $file){
                    $fieldsPhases[] = $file;
                }
            }

            return $fieldsPhases;
    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation) {
        $total = 0;

        $cfg = $evaluation->getEvaluationMethodConfiguration();
        foreach($cfg->criteria as $cri){
            $key = $cri->id;
            if(!isset($evaluation->evaluationData->$key)){
                return null;
            } else {
                $val = $evaluation->evaluationData->$key;
                $total += is_numeric($val) ? $cri->weight * $val : 0;
            }
        }

        return $total;
    }

    protected function _valueToString($value) {
        if(is_null($value)){
            return i::__('Avaliação incompleta');
        } else {
            return $value;
        }
    }

    function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): array {
        $evaluation_configuration = $evaluation->registration->opportunity->evaluationMethodConfiguration;

        $sections = $evaluation_configuration->sections ?: [];
        $criteria = $evaluation_configuration->criteria ?: [];

        foreach($sections as &$section) {
            $section->criteria = [];
            $section->score = 0;
            $section->maxScore = 0;

            foreach($criteria as &$cri){
                if(($cri->sid ?? false) == $section->id) {
                    unset($cri->sid);
                    $score = $evaluation->evaluationData->{$cri->id};
                    $cri->score = is_numeric($score) ? $cri->weight * $score : 0;
                    $section->score += $cri->score;
                    $cri->maxScore = $cri->max * $cri->weight;
                    $section->maxScore += $cri->maxScore;
                    $section->criteria[] = $cri;
                }
            }
        }

        return [
            'scores' => $sections,
            'obs' => $evaluation->evaluationData->obs
        ];
    }

    function _getConsolidatedDetails(Entities\Registration $registration): array {
        $evaluation_configuration = $registration->opportunity->evaluationMethodConfiguration;
        $sections =  [];
        $criteria = [];
        $max_score = 0;
        $affirmative_policy = null;

        if($evaluations = $registration->sentEvaluations){
            $sections = $evaluation_configuration->sections ?: [];
            $criteria = $evaluation_configuration->criteria ?: [];
            $max_score = 0;
    
            foreach($sections as &$section) {
                $section->criteria = [];
                $section->score = 0;
                $section->maxScore = 0;
    
                foreach($criteria as &$cri){
                    if(($cri->sid ?? false) == $section->id) {
                        unset($cri->sid);
                        $score = 0;
    
                        foreach($evaluations as $evaluation) {
                            $_score = $evaluation->evaluationData->{$cri->id};
                            $score += is_numeric($_score) ? $cri->weight * $_score : 0;
                        }
    
                        $cri->score = $score / count($evaluations);
                        $cri->maxScore = $cri->max * $cri->weight;
                        $section->score += $cri->score;
                        $section->maxScore += $cri->maxScore;
                        $section->criteria[] = $cri;
                    }
                }
    
                $max_score += $section->maxScore;
            }
    
            if($affirmative_policy = $registration->appliedPointReward){
                $affirmative_policy->roof = $evaluation_configuration->pointRewardRoof;
            }
            
        }
       
        return [
            'maxScore' => $max_score,
            'scores' => $sections,
            'appliedPointReward' => $affirmative_policy,
        ];
    }

    private function viabilityLabel($evaluation) {
        if (isset($evaluation->evaluationData->viability)) {
            $viability = $evaluation->evaluationData->viability;

            return $this->viability_status[$viability];
        }

        return '';
    }

    public function useCommitteeGroups(): bool {
        return false;
    }

    public function evaluateSelfApplication(): bool {
        return false;
    }
}