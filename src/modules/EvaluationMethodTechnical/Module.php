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
            'type' => 'boolean',
            'serialize' => function ($val){
                return ($val == "true") ? true : false;
            }
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

    function getRegistrationRegion($registration, $geo_quota_config) {
        $region = '';
        foreach($geo_quota_config->fieldNames as $field_name) {
            if($registration->$field_name) {
                $region = $registration->$field_name;
                break;
            }
        }

        if(isset($geo_quota_config->distribution->$region)) {
            return $region;
        } else {
            return 'OTHERS';
        }
    }

    function getAffirmativePoliciesFields($quota_config, $geo_quota_config) {
        $fields = ['appliedForQuota', ...$geo_quota_config->fieldNames];

        foreach($quota_config->rules as $rule) {
            $fields[] = $rule->fieldName;
        }

        return array_values(array_unique($fields));
    }

    function getRegistrationsForQuotaSorting($phase_opportunity, $fields) {
        $app = App::i();

        $cache_key = "$phase_opportunity:quota-registrations";

        if($app->cache->contains($cache_key)){
            return $app->cache->fetch($cache_key);
        }

        $conn = $app->em->getConnection();
        
        $selects = ['r.id', 'r.number', 'r.range', 'CAST(r.consolidated_result AS FLOAT) as score'];
        $joins = [];

        foreach($fields as $field) {
            $alias = $field . '_alias';
            $selects[] = "$alias.value AS $field";
            $joins[] = "LEFT JOIN 
                registration_meta $alias ON 
                    $alias.key = '$field' AND
                    $alias.object_id IN (SELECT id FROM registration WHERE number = r.number)";
        }

        $selects = implode(', ', $selects);
        $joins = implode("\n\t", $joins);

        $sql = "SELECT $selects 
        FROM registration r $joins
        WHERE r.opportunity_id = $phase_opportunity->id
        ORDER BY score DESC";
        $registrations = $conn->fetchAllAssociative($sql);

        $result = array_map(function ($reg) {return (object) $reg; }, $registrations);

        $app->cache->save($cache_key, $result);

        return $result;
    }

    public function getPhaseQuotaRegistrations(int $phase_id) {
        $app = App::i();
        
        $phase_opportunity = $app->repo('Opportunity')->find($phase_id);
        $phase_evaluation_config = $phase_opportunity->evaluationMethodConfiguration;
        $first_phase = $phase_opportunity->firstPhase;

        // número total de vagas no edital
        $vacancies = $first_phase->vacancies;
        $exclude_ampla_concorrencia = false;

        // configuração de faixas
        $ranges_config = [];
        foreach($first_phase->registrationRanges as $range) {
            $ranges_config[$range['label']] = $range['limit'];
        }

        $quota_config = $phase_evaluation_config->quotaConfiguration;

        $geo_quota_config = (object) [
            // 'geoDivision' => 'mesorregiao',
            // 25379 e 25377
            'fieldNames' => ['field_25379', 'field_25377'],
            'distribution' => (object) [
                'Região Metropolitana' => 100, // 40%
                'Zona da Mata' => 50, // 20%
                'Agreste' => 50, // 20%
                'Sertão' => 50, // 20%
            ]   
        ];



        $selected_global = [];
        $selected_by_quotas = [];
        $selected_by_geo = [];
        $selected_by_ranges = [];


        /** ===  inicializa as listas === */
        // cotas
        $total_quota = 0;
        foreach($quota_config->rules as $rule) {
            $field_name = $rule->fieldName;
            $rule_id = $field_name . ':' . implode(',', array_values($rule->eligibleValues));
            $selected_by_quotas[$rule_id] = $selected_by_quotas[$rule_id] ?? [];
            $total_quota += $rule->vacancies;
        }
        $total_ampla_concorrencia = $vacancies - $total_quota;

        // distribuição geográfica
        $total_distribution = 0;
        foreach($geo_quota_config->distribution as $region => $num) {
            $total_distribution += $num;
            $selected_by_geo[$region] = $selected_by_geo[$region] ?? [];
        }
        $geo_quota_config->distribution->OTHERS = $vacancies - $total_distribution;
    
        // distribuição nas faixas
        foreach($ranges_config as $range => $num) {
            $selected_by_ranges[$range] = $selected_by_ranges[$range] ?? [];
        }

        $fields = $this->getAffirmativePoliciesFields($quota_config, $geo_quota_config);
        $registrations = $this->getRegistrationsForQuotaSorting($phase_opportunity, $fields);
        /** gera as listas */

        // primeiro preenche as cotas
        foreach($quota_config->rules as $rule) {
            $field_name = $rule->fieldName;

            // fica algo como "raca:preto,pardo"
            $rule_id = $field_name . ':' . implode(',', array_values($rule->eligibleValues));
            
            foreach($registrations as $i => &$reg) {
                if($exclude_ampla_concorrencia && $i < $total_ampla_concorrencia) {
                    continue;
                }

                // se a pessoa não é elegível, ela não conta nas cotas (pode ser pq falou que não quer ser cotista ou pq nenhum critério configurado bateu)
                if(!$reg->appliedforquota) {
                    continue;
                }

                // para impedir que uma inscrição que se enquadre em mais de 1 critério ocupe 2 vagas
                if(in_array($reg, $selected_global)) {
                    continue;
                }

                $quota_count = count($selected_by_quotas[$rule_id]);

                /** @todo verificar se não excedeu o máximo de vagas em cada região ou faixa*/

                if($quota_count < $rule->vacancies && in_array($reg->$field_name, $rule->eligibleValues)) {
                    $selected_by_quotas[$rule_id][] = &$reg;
                    $selected_global[] = &$reg;
                    $region = $this->getRegistrationRegion($reg, $geo_quota_config);

                    $selected_by_geo[$region][] = &$reg;
                    $selected_by_ranges[$reg->range][] = &$reg;
                }
            }
        }

        foreach($registrations as &$reg) {
            if(in_array($reg, $selected_global)) {
                continue;
            }

            $selected = true;
            
            $region = $this->getRegistrationRegion($reg, $geo_quota_config);
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

        usort($selected_global, function($reg1, $reg2) {
            return ((float) $reg2->score) <=> ((float) $reg1->score);
        });

        $result = array_values($selected_global);

        foreach($registrations as $reg) {
            if(!in_array($reg, $result)){
                $result[] = $reg;
            }
        }

        return $result;

    }

    public function _init() {
        $app = App::i();

        $self = $this;

        $app->hook('entity(Registration).consolidateResult', function() {
            /** @var Registration $this */

            // salva o metadado appliedPointReward
            $metadata = $this->getMetadata('appliedPointReward', true);
            $metadata->save(true);

            $app = App::i();
            
            // limpa o cache das cotas
            $cache_key = "{$this->opportunity}:quota-registrations";
            $app->cache->delete($cache_key);
        }); 

        $quota_data = (object)[];

        $app->hook('ApiQuery(registration).params', function(&$params) use($self, &$quota_data) {
            /** @var ApiQuery $this */
            $order = $params['@order'] ?? '';
            preg_match('#EQ\((\d+)\)#', $params['opportunity'] ?? '', $matches);
            $phase_id = $matches[1] ?? null;
            if($phase_id && str_starts_with(strtolower(trim($order)), '@quota')){
                $quota_data->params = $params;

                unset($params['@order']);

                $quota_order = $self->getPhaseQuotaRegistrations((int) $phase_id);
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
            if($quota_data->ids ?? false) {
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

        $app->hook('API.find(registration).result', function() use($quota_data) {
            /** @var Controller $this */
            if($quota_data->ids ?? false) {
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

        $app->hook('POST(opportunity.appyTechnicalEvaluation)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'technical') {
                $this->errorJson(i::__('Somente para avaliações técnicas'), 400);
                die;
            }
            if ($this->data['to'] && (!is_numeric($this->data['to']) || !in_array($this->data['to'], [0,1,2,3,8,10]))) {
                $this->errorJson(i::__('os status válidos são 0, 1, 2, 3, 8 e 10'), 400);
                die;
            }
            $new_status = intval($this->data['to']);
            
            $apply_status = $this->data['status'];

            if ($apply_status == 'all') {
                $status = 'r.status IN (0,1,2,3,8,10)';
            } else {
                $status = 'r.status = 1';
            }
    
            $opp->checkPermission('@control');

            // pesquise todas as registrations da opportunity que esta vindo na request
            $mim = $this->data['from'][0];
            $max = $this->data['from'][1];

            $dql = "
            SELECT 
                r.id
            FROM
                MapasCulturais\Entities\Registration r
            WHERE 
                r.opportunity = $opp->id AND
                r.status <> $new_status AND
                $status AND
                CAST(r.consolidatedResult AS FLOAT) BETWEEN  $mim AND  $max 
            ";
            $query = $app->em->createQuery($dql);
          
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
                    case Registration::STATUS_SENT:
                        $registration->setStatusToSent();
                    break;
                    default:
                        $registration->_setStatusTo($new_status);
                }
                $app->disableAccessControl();
                $registration->save(true);
                $app->enableAccessControl();
            }

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
            $_result = number_format($result / $num, 2);
            return $this->applyAffirmativePolicies($_result, $registration);

        } else {
            return null;
        }
    }

    public function applyAffirmativePolicies($result, \MapasCulturais\Entities\Registration $registration)
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

       
        if(!$isActivePointReward || empty($affirmativePoliciesConfig)){
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

}