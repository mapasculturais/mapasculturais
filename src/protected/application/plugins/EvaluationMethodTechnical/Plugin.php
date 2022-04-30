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

        $this->registerEvaluationMethodConfigurationMetadata('isActiveAffirmativePolicies', [
            'label' => i::__('Controla se as politicas afirmativas estão ou não ativadas'),
            'type' => 'boolean',
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

        $app->hook('GET(opportunity.edit):before', function() use ($app){
            
            $evaluationMethodConfiguration = $this->requestedEntity->evaluationMethodConfiguration;

            $app->view->jsObject['isActiveAffirmativePolicies'] = $evaluationMethodConfiguration->isActiveAffirmativePolicies;
            $app->view->jsObject['affirmativePolicies'] = $evaluationMethodConfiguration->affirmativePolicies;
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
            return number_format($result / $num, 2);
        } else {
            return null;
        }
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
