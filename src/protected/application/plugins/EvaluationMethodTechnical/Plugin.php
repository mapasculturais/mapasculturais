<?php

namespace EvaluationMethodTechnical;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;

class Plugin extends \MapasCulturais\EvaluationMethod {
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
                return json_decode($val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('criteria', [
            'label' => i::__('Critérios'),
            'type' => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode($val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('affirmativePolicies', [
            'label' => i::__('Políticas Afirmativas'),
            'type' => 'json',
            'serialize' => function ($val){
                return (!empty($val)) ? json_encode($val) : "[]";
            },
            'unserialize' => function($val){
                return json_decode($val);
            }
        ]);

        $this->registerRegistrationMetadata('appliedAffirmativePolicy', [
            'label' => i::__('Políticas Afirmativas aplicadas a inscrição'),
            'type' => 'json',
            'private' => true,
            'serialize' => function ($val){
                $val = (!empty($val)) ? $val : ['raw' => null, 'percentage' => null, 'rules' => []];
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode($val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('isActiveAffirmativePolicies', [
            'label' => i::__('Controla se as politicas afirmativas estão ou não ativadas'),
            'type' => 'boolean',
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('affirmativePoliciesRoof', [
            'label' => i::__('Define o valor máximo das políticas afirmativas'),
            'type' => 'string',
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('quota', [
            'label' => i::__('Cotas'),
            'type' => 'json',
            'serialize' => function ($val){
                return json_encode($val);
            },
            'unserialize' => function($val){
                return json_decode($val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('enableViability',[
            'label' => i::__('Habilitar Análise de Exiquibilidade das inscrições?'),
            'type' => 'radio',
            'options' => array(
                'true' => i::__('Habilitar Análise de Exiquibilidade'),
                'false' => i::__('Não habilitar'),
            ),
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

    public function _init() {
        $app = App::i();

        $plugin = $this;

         // Reconsolida a avaliação da inscrição caso em fases posteriores exista avaliação técnica com políticas afirmativas aplicadas
         $app->hook('entity(Registration).update:after', function() use ($app){
            /** @var \MapasCulturais\Entities\Registration $this */
            $phase = $this;
            
            do{
                $em = $phase->getEvaluationMethod();

                if($em->getSlug() == "technical"){
                    $app->disableAccessControl();
                    $phase->consolidateResult();
                    $app->enableAccessControl();
                }
            }while($phase = $phase->nextPhase);
        });


        // Insere valores das políticas afirmativas aplicadas na planilha de inscritos
        $app->hook('opportunity.registrations.reportCSV', function(\MapasCulturais\Entities\Opportunity $opportunity, $registrations, &$header, &$body) use ($app){
            
            $isActiveAffirmativePolicies = filter_var($opportunity->evaluationMethodConfiguration->isActiveAffirmativePolicies, FILTER_VALIDATE_BOOL);

            if($isActiveAffirmativePolicies){

                $header[] = 'POLITICAS-AFIRMATIVAS';
                            
                foreach($body as $i => $line){    
                    $reg = $app->repo("Registration")->findOneBy(['number' => $line[0], 'opportunity' => $opportunity]);

                    $policies = $reg->appliedAffirmativePolicy;

                    if(!$policies->rules){
                        continue;
                    }

                    $valuePencentage = (($policies->raw * $policies->percentage)/100);
                    $cell = "";
                    $cell.= "Políticas afirmativas atribuídas \n\n";
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

        // passa os dados de configuração das políticas afirmativas para JS
        $app->hook('GET(opportunity.edit):before', function() use ($app, $plugin){
            $entity = $this->requestedEntity;

            $app->view->jsObject['affirmativePoliciesFieldsList'] = $plugin->getFieldsAllPhases($entity);
           
            $evaluationMethodConfiguration = $entity->evaluationMethodConfiguration;

            $app->view->jsObject['isActiveAffirmativePolicies'] = $evaluationMethodConfiguration->isActiveAffirmativePolicies;
            $app->view->jsObject['affirmativePolicies'] = $evaluationMethodConfiguration->affirmativePolicies;
            $app->view->jsObject['affirmativePoliciesRoof'] = $evaluationMethodConfiguration->affirmativePoliciesRoof;
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

        $affirmativePoliciesConfig = $registration->opportunity->evaluationMethodConfiguration->affirmativePolicies;
        $affirmativePoliciesRoof = $registration->opportunity->evaluationMethodConfiguration->affirmativePoliciesRoof;
        $isActiveAffirmativePolicies = filter_var($registration->opportunity->evaluationMethodConfiguration->isActiveAffirmativePolicies, FILTER_VALIDATE_BOOL);
        $metadata = $registration->getRegisteredMetadata();

       
        if(!$isActiveAffirmativePolicies || empty($affirmativePoliciesConfig)){
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
        
        $percentage = (($affirmativePoliciesRoof > 0) && $totalPercent > $affirmativePoliciesRoof) ? $affirmativePoliciesRoof : $totalPercent;

        $registration->appliedAffirmativePolicy = [
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
