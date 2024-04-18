<?php

namespace EvaluationMethodTechnical;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Controller;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

class Module extends \MapasCulturais\EvaluationMethod {
    function __construct(array $config = []) {
        $config += ['step' => '0.1'];
        parent::__construct($config);
    }

    private $viability_status;

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
        if($max_value){
            for($i=0;$i<5;$i++){
                $min = $i * $max_value / 5;
                $max = ($i+1) * $max_value / 5;
                foreach($data as $val => $sum) {
                    if(!is_numeric($val)) {
                        $non_numeric[$val] = $non_numeric[$val] ?? 0;
                        $non_numeric[$val] += $sum;

                    } else if($val >= $min && $val < $max) {

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
            'type' => 'integer',
        ]);

        $this->registerOpportunityMetadata('enableQuotasQuestion', [
            'label' => i::__('Habilitar opção para o candidato declarar interesse nas cotas ou políticas afirmativas'),
            'description' => i::__('Ao habilitar esta configuração, será liberada a opção para o candidato se autoidentificar para cotas ou políticas afirmativas.'),
            'type' => 'boolean',
            'private' => false,
            'field_type' => 'checkbox'
        ]);

        $this->registerOpportunityMetadata('considerQuotasInGeneralList', [
            'label' => i::__('Considerar os cotistas dentro da listagem da ampla concorrência'),
            'description' => i::__('Ao habilitar esta configuração, os cotistas seráo considerados na listagem da ampla concorrência.'),
            'type' => 'boolean',
            'private' => false,
            'default' => true,
        ]);
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

    function getRegistrationRegion($registration, $geo_quota_config, Opportunity $first_phase) {
        $app = App::i();

        /** @var Opportunity $first_phase */
        $opportunity_proponent_types = $first_phase->registrationProponentTypes;
        $proponent_types2agents_map = $app->config['registration.proponentTypesToAgentsMap'];
        
        $proponent_type = $registration->proponentType;

        if(!$opportunity_proponent_types) {
            $agent_data = $registration->agentsData['owner'];
        } else {
            $agent_key = $proponent_types2agents_map[$proponent_type] ?? null;
            $agent_data = $registration->agentsData[$agent_key] ?? null;
        }

        // ISSO NÃO DEVERIA SER POSSÍVEL
        if(!$agent_data) {
            $agent_data = $registration->agentsData['owner'];
        }

        $meta = $geo_quota_config->geoDivision;
        $region =  $agent_data[$meta] ?? '';

        if(isset($geo_quota_config->distribution->$region)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    function getAffirmativePoliciesFields($quota_config, $geo_quota_config) {
        $fields = ['appliedForQuota'];

        foreach($quota_config->rules as $rule) {
            foreach($rule->fields as $field) {
                $fields[] = $field->fieldName;
            }
        }

        return array_values(array_unique($fields));
    }

    function getTiebreakerConfigurationFields($tiebreaker_config) {
        $fields = [];

        foreach($tiebreaker_config as $rule) {
            if(str_starts_with($rule->criterionType, 'field_' ) ) {
                $fields[] = $rule->criterionType;
            } else {
                // @TODO colocar o metadado que salvará a consolidação da avaliação técnica
            }
        }

        return array_values(array_unique($fields));
    }

    function getRegistrationsForQuotaSorting(Opportunity $phase_opportunity, $fields, $params = null) {
        $app = App::i();
        if($params) {
            unset(
                $params['@select'], 
                $params['@order'], 
                $params['@limit'], 
                $params['@page'],
                $params['opportunity'],
            );
        } else {
            $params = [];
        }
        
        $cache_key = "$phase_opportunity:quota-registrations:" . md5(serialize($params));
        
        if(false && $app->config['app.useQuotasCache'] && $app->cache->contains($cache_key)){
            return $app->cache->fetch($cache_key);
        }

        $result = $app->controller('opportunity')->apiFindRegistrations($phase_opportunity, [
            '@select' => implode(',', ['number,range,proponentType,agentsData,consolidatedResult,eligible,score', ...$fields]),
            '@order' => 'score DESC',
            '@quotaQuery' => true,
            ...$params
        ]);

        $registrations = array_map(function ($reg) {
            return (object) $reg; 
        }, $result->registrations);

        if($app->config['app.useQuotasCache']){
            $app->cache->save($cache_key, $registrations, $app->config['app.quotasCache.lifetime']);
        }

        return $registrations;
    }

    protected function generateRuleId($rule) {
        $app = App::i();
        return isset($rule->title) ? $app->slugify($rule->title) : md5(json_encode($rule));
    }

    public function getPhaseQuotaRegistrations(int $phase_id, $params = null) {
        $app = App::i();
        
        $phase_opportunity = $app->repo('Opportunity')->find($phase_id);
        $phase_evaluation_config = $phase_opportunity->evaluationMethodConfiguration;
        $first_phase = $phase_opportunity->firstPhase;

        // número total de vagas no edital
        $vacancies = $first_phase->vacancies;
        $exclude_ampla_concorrencia = !$first_phase->considerQuotasInGeneralList;

        // configuração de faixas
        $registration_ranges = $first_phase->registrationRanges ?: [];
        $ranges_config = [];
        foreach($registration_ranges as $range) {
            $ranges_config[$range['label']] = $range['limit'];
        }

        $tiebreaker_config = $phase_evaluation_config->tiebreakerCriteriaConfiguration ?: [];
        $quota_config = $phase_evaluation_config->quotaConfiguration ?: (object) ['rules' => (object) []];
        $geo_quota_config = $phase_evaluation_config->geoQuotaConfiguration ?: (object) ['distribution' => (object) [], 'geoDivision' => null];
        $geo_quota_config->distribution = (object) $geo_quota_config->distribution;
        
        $selected_global = [];
        $selected_by_quotas = [];
        $selected_by_geo = [];
        $selected_by_ranges = [];

        /** ===  INICIALIZANDO AS LISTAS === */
        // cotas
        $total_quota = 0;
        foreach($quota_config->rules as $rule) {
            $rule_id = $this->generateRuleId($rule);
            $selected_by_quotas[$rule_id] = $selected_by_quotas[$rule_id] ?? [];
            $total_quota += $rule->vacancies;
        }
        $total_ampla_concorrencia = $vacancies - $total_quota;

        // distribuição geográfica
        $total_distribution = 0;
        foreach($geo_quota_config->distribution as $region => $num) {
            if($num > 0){
                $total_distribution += $num;
                $selected_by_geo[$region] = $selected_by_geo[$region] ?? [];
            } else {
                unset($geo_quota_config->distribution->$region);
            }
        }

        $geo_quota_config->distribution->OTHERS = $vacancies - $total_distribution;
    
        // distribuição nas faixas
        foreach($ranges_config as $range => $num) {
            $selected_by_ranges[$range] = $selected_by_ranges[$range] ?? [];
        }

        $fields_affirmative_policies = $this->getAffirmativePoliciesFields($quota_config, $geo_quota_config);
        $fields_tiebreaker = $this->getTiebreakerConfigurationFields($tiebreaker_config);
        $fields = array_unique([...$fields_affirmative_policies, ...$fields_tiebreaker]);
        $registrations = $this->getRegistrationsForQuotaSorting($phase_opportunity, $fields, $params);
        $registrations = $this->tiebreaker($tiebreaker_config, $registrations);
        
        /** === POPULANDO AS LISTAS === */
        // primeiro preenche as cotas
        foreach($quota_config->rules as $rule) {

            $rule_id = $this->generateRuleId($rule);
            
            foreach($registrations as $i => &$reg) {
                if($exclude_ampla_concorrencia && $i < $total_ampla_concorrencia) {
                    continue;
                }

                // se a pessoa não é elegível, ela não conta nas cotas (pode ser pq falou que não quer ser cotista ou pq nenhum critério configurado bateu)
                if(!$reg->eligible) {
                    continue;
                }

                // para impedir que uma inscrição que se enquadre em mais de 1 critério ocupe 2 vagas
                if(in_array($reg, $selected_global)) {
                    continue;
                }
                
                $quota_count = count($selected_by_quotas[$rule_id]);
                
                $region = $this->getRegistrationRegion($reg, $geo_quota_config, $first_phase);

                /** @todo verificar se não excedeu o máximo de vagas em cada região ou faixa*/
                foreach($rule->fields as $field){
                    $field_name = $field->fieldName;

                    if($quota_count < $rule->vacancies && in_array($reg->$field_name, $field->eligibleValues)) {
                        $selected_by_quotas[$rule_id][] = &$reg;
                        $selected_global[] = &$reg;

                        $selected_by_geo[$region][] = &$reg;
                        $selected_by_ranges[$reg->range][] = &$reg;
                    }
                }
            }
        }

        foreach($registrations as &$reg) {
            if(in_array($reg, $selected_global)) {
                continue;
            }

            $selected = true;
            
            $region = $this->getRegistrationRegion($reg, $geo_quota_config, $first_phase);
            $geo_count = count($selected_by_geo[$region] ?? []);
            if(isset($geo_quota_config->distribution->$region) && $geo_count >= $geo_quota_config->distribution->$region) {
                // var_dump([$region, $geo_quota_config->distribution->$region, $reg->regiao]);
                $selected = false;
            }

            $range = $reg->range;
            $range_count = count($selected_by_ranges[$range] ?? []);
            if(isset($ranges_config[$range]) && $range_count >= $ranges_config[$range]) {
                $selected = false;
            }

            if($selected) {
                $selected_by_geo[$region][] = &$reg;
                $selected_by_ranges[$range][] = &$reg;
                $selected_global[] = $reg;
            }
        }
        
        $selected_global = $this->tiebreaker($tiebreaker_config, $selected_global);

        $result = array_values($selected_global);
        
        foreach(array_values($registrations) as &$reg) {
            if(!in_array($reg, $result)){
                $result[] = &$reg;
            }
        }

        return $result;

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

            $allPhases = $registration->opportunity->allPhases;
            $appliedForQuota = true;
            foreach($allPhases as $phase) {
                if($phase->enableQuotasQuestion && !$registration->appliedForQuota) {
                    $appliedForQuota = false;
                }
            }

            if(!$appliedForQuota) {
                return false;
            }
            
            $_rules = [];
            if($em->isActivePointReward) {
                if($pointRewards = $em->pointReward) {
                    foreach($pointRewards as $pointReward) {
                        if(isset($pointReward->value)) {
                            $field_name = "field_" . $pointReward->field;
                            $data = [
                                'fieldName' => $field_name,
                                'eligibleValues' => $pointReward->value
                            ];
    
                            $_rules['fields'][] =  $data;
                        }
                    }
                    
                    if($_rules && $self->qualifiesForQuotaRule(json_decode(json_encode($_rules)), $registration)) {
                        return true;
                    }
                }
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


        $quota_data = null;

        $app->hook('ApiQuery(registration).params', function(&$params) use($app, $self, &$quota_data) {
            /** @var ApiQuery $this */

            if(is_null($quota_data)) {
                $quota_data = (object) [];
            } else {
                return;
            }

            $order = $params['@order'] ?? '';
            preg_match('#EQ\((\d+)\)#', $params['opportunity'] ?? '', $matches);
            $phase_id = $matches[1] ?? null;
            if($phase_id && str_starts_with(strtolower(trim($order)), '@quota')){
                $quota_data->objectId = spl_object_id($this);
                $quota_data->params = $params;

                unset($params['@order']);
                $quota_order = $self->getPhaseQuotaRegistrations((int) $phase_id, $params);
                $opportunity = $app->repo('Opportunity')->find($phase_id);
                $opportunity->registerRegistrationMetadata();

                $ids = array_map(function($reg) { return $reg->id; }, $quota_order);
                if($limit = (int) ($params['@limit'] ?? 0)) {
                    $page = $params['@page'] ?? 1;
                    $offset = ($page - 1) * $limit;
                    $ids = array_slice($ids, $offset, $limit);
                    unset($params['@page'], $params['@limit']);
                }

                $params['id'] = API::IN($ids);

                $quota_data->order = $quota_order;
                $quota_data->ids = $ids;
            }
        });

        $app->hook('ApiQuery(registration).findResult', function(&$result) use(&$quota_data) {
            /** @var ApiQuery $this */
            if(($quota_data->objectId ?? false) == spl_object_id($this)) {
                $_new_result = [];
                foreach($quota_data->ids as $id) {
                    foreach($result as $registration) {
                        if($registration['id'] == $id) {
                            $_new_result[] = $registration;
                        }
                    }
                }
                $result = $_new_result;
                $quota_data->result = $result;
            }
        });

        $app->hook('ApiQuery(registration).countResult', function(&$result) use(&$quota_data) {
            if(($quota_data->objectId ?? false) == spl_object_id($this)) {
                $result = count($quota_data->order);
            }
        });

        $app->hook('API.find(registration).result', function() use($quota_data) {
            /** @var Controller $this */
            if(($quota_data->objectId ?? false) == spl_object_id($this)) {
                $this->apiAddHeaderMetadata($quota_data->params, $quota_data->result, count($quota_data->order));
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
            
            $isActivePointReward = filter_var($opportunity->evaluationMethodConfiguration->isActivePointReward, FILTER_VALIDATE_BOOL);

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

    public function _getConsolidatedResult(\MapasCulturais\Entities\Registration $registration) {
        $app = App::i();
        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();
        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

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
            if(empty($metadata)){
                continue;
            }
            
            $fieldName = "field_".$rules->field;
            $applied = false;
            $field_conf = $metadata[$fieldName]->config['registrationFieldConfiguration'];

            if($field_conf->categories && !in_array($registration->category, $field_conf->categories)){
                continue;
            }

            if(isset($field_conf->config['require']['condition']) && $field_conf->config['require']['condition']){
                $_field_name = $field_conf->config['require']['field'];
                if(trim($registration->$_field_name) != trim($field_conf->config['require']['value'])){
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

    public function valueToString($value) {
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

    public function fetchRegistrations() {
        return true;
    }

    private function viabilityLabel($evaluation) {
        if (isset($evaluation->evaluationData->viability)) {
            $viability = $evaluation->evaluationData->viability;

            return $this->viability_status[$viability];
        }

        return '';
    }

    public static function tiebreaker($tiebreaker_configuration, $registrations) {
        $app = App::i();
        $self = $app->modules['EvaluationMethodTechnical']; 

        usort($registrations, function($registration1, $registration2) use($tiebreaker_configuration, $self) {
            $result = $registration2->score <=> $registration1->score;
            if($result != 0) {
                return $result;
            }

            foreach($tiebreaker_configuration as $tiebreaker) {
                $selected = $tiebreaker->selected;
                if($selected !== null && $selected->fieldType == 'select') {
                    $registration1Has = in_array($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = in_array($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                if($selected !== null && in_array($selected->fieldType, ['integer', 'numeric', 'number', 'float', 'currency', 'date'])) {
                    $registration1Has = $registration1->{$tiebreaker->criterionType};
                    $registration2Has = $registration2->{$tiebreaker->criterionType};

                    $result = $registration1Has <=> $registration2Has;

                    if($tiebreaker->preferences == 'smallest') {
                        if ($result !== 0) {
                            return $result;
                        }
                    }

                    if($tiebreaker->preferences == 'largest') {
                        if ($result !== 0) {
                            return -$result;
                        }
                    }
                }

                if($selected !== null && in_array($selected->fieldType, ['multiselect', 'checkboxes'])) {
                    $registration1Has = array_intersect($registration1->{$tiebreaker->criterionType}, $tiebreaker->preferences);
                    $registration2Has = array_intersect($registration2->{$tiebreaker->criterionType}, $tiebreaker->preferences);

                    $registration1Has = !empty($registration1Has);
                    $registration2Has = !empty($registration2Has);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }
                
                if($selected !== null && in_array($selected->fieldType, ['boolean', 'checkbox'])) {
                    $registration1Has = $registration1->{$tiebreaker->criterionType};
                    $registration2Has = $registration2->{$tiebreaker->criterionType};

                    $result = $registration1Has <=> $registration2Has;

                    if($tiebreaker->preferences == 'marked') {
                        if ($result !== 0) {
                            return -$result;
                        }
                    }

                    if($tiebreaker->preferences == 'unmarked') {
                        if ($result !== 0) {
                            return $result;
                        }
                    }
                }

                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'criterion') {
                    $registration1Has = $self->tiebreakerCriterion($tiebreaker->preferences, $registration1->id);
                    $registration2Has = $self->tiebreakerCriterion($tiebreaker->preferences, $registration2->id);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

                if(isset($tiebreaker->criterionType) && $tiebreaker->criterionType == 'sectionCriteria') {
                    $registration1Has = $self->tiebreakerSectionCriteria($tiebreaker->preferences, $registration1->id);
                    $registration2Has = $self->tiebreakerSectionCriteria($tiebreaker->preferences, $registration2->id);

                    if($registration1Has != $registration2Has) {
                        return $registration2Has <=> $registration1Has;
                    }
                }

            }
        });
        
        return $registrations;
    }

    public function tiebreakerCriterion($criteriaId, $registrationId) {
        $app = App::i();
        
        $registration = $app->repo('Registration')->find($registrationId);
        $criteria = $registration->evaluationMethodConfiguration->criteria;

        $findCriteria = [];
        foreach($criteria as $criterion) {
            if($criterion->id === $criteriaId) {
                $findCriteria[] = $criterion;
            }
        }

        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();

        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

        $result = 0;
        foreach ($evaluations as $eval) {
            foreach($eval->evaluationData as $key => $data) {
                foreach($findCriteria as $cri) {
                    if($key === $criteriaId) {
                        $result += $data * $cri->weight;
                    }
                }
            }
        }

        $num = count($evaluations);

        return $num ? number_format($result / $num, 2) : null;
    }

    public function tiebreakerSectionCriteria($sectionId, $registrationId) {
        $app = App::i();
        
        $registration = $app->repo('Registration')->find($registrationId);
        $criteria = $registration->evaluationMethodConfiguration->criteria;

        $findCriteria = [];
        foreach($criteria as $criterion) {
            if($criterion->sid === $sectionId) {
                $findCriteria[] = $criterion;
            }
        }

        $status = [ \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();

        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

        $result = 0;
        foreach ($evaluations as $eval) {
            foreach($eval->evaluationData as $key => $data) {
                foreach($findCriteria as $cri) {
                    if($key === $cri->id) {
                        $result += $data * $cri->weight;
                    }
                }
            }
        }

        return number_format($result, 2);
    }
}