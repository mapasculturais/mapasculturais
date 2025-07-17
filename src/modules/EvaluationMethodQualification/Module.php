<?php

namespace EvaluationMethodQualification;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';


class Module extends \MapasCulturais\EvaluationMethod
{

    protected function _getDefaultStatuses(EvaluationMethodConfiguration $evaluation_method_configuration): array
    {
        return [
            'invalid' => i::__('Inabilitado'),
            'valid'   => i::__('Habilitado')
        ];
    }

    public function getSlug()
    {
        return 'qualification';
    }

    public function getName()
    {
        return i::__('Avaliação de habilitação documental');
    }

    public function getDescription()
    {
    }

    protected function _getConsolidatedResult(Entities\Registration $registration, array $evaluations)
    {
        if(empty($evaluations)){
            $statuses = [
                Entities\Registration::STATUS_DRAFT => 0,
                Entities\Registration::STATUS_SENT => 0,
                Entities\Registration::STATUS_INVALID => 'invalid',
                Entities\Registration::STATUS_NOTAPPROVED => 'invalid',
                Entities\Registration::STATUS_WAITLIST => 'invalid',
                Entities\Registration::STATUS_APPROVED => 'valid',
            ];

            return $statuses[$registration->status] ?? 0;
        }
        
        $committee = $registration->opportunity->getEvaluationCommittee();
        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $result = 'valid';
        foreach ($evaluations as $eval){
            if($this->getEvaluationResult($eval) == 'invalid'){
                $result = 'invalid';
            }
        }

        return $result;

    }

    public function getEvaluationStatues()
    {
        $status = [
            'valid' => i::__('Habilitado'),
            'invalid' => i::__('Inabilitado')
        ];

        return $status;
    }

    public function parseLegacyResult($result) {
        switch($result) {
            case i::__('Habilitado'):
                $result = ['valid'];
                break;
            case i::__('Inabilitado'):
                $result = ['invalid'];
                break;
            case i::__('Não se aplica'):
                $result = ['not-applicable'];
                break;
            default:
                $result = [$result];
                break;
        }
        return $result;
    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation)
    {
        $approved = ['valid', 'not-applicable'];
        $result = 'valid';
        $cfg = $evaluation->getEvaluationMethodConfiguration();
        
        foreach(($cfg->sections ?? []) as $section) {
            $number_max_non_liminatory = $section->numberMaxNonEliminatory ?? 0;
            $non_eliminatory_count = 0;

            foreach(($cfg->criteria ?? []) as $cri){
                if($cri->sid != $section->id){
                    continue;
                }
                $key = $cri->id;

                $non_eliminatory = ($cri->nonEliminatory ?? false) == 'true';

                $val = isset($evaluation->evaluationData->$key) ? $evaluation->evaluationData->$key : null;

                if(!is_array($val)){
                    // para as avaliações legadas antes da versão 7.6
                    $val = $this->parseLegacyResult($val);
                }
                if(!isset($val)){
                    return null;
                } else {
                    if(($non_eliminatory)) {
                        if(array_diff($val, $approved)){
                            $non_eliminatory_count++;
                        }
                    } else {
                        if(array_diff($val, $approved)){
                            $result = 'invalid';
                            break;
                        }
                    }
                }

                if($non_eliminatory_count > $number_max_non_liminatory){
                    $result = 'invalid';
                    break;
                }
            }

        }

        return $result;
    }

    protected function _valueToString($value)
    {
        $statuses = $this->getEvaluationStatues();
        if(is_null($value)){
            return '';
        } else {
            return $statuses[$value] ?? $value;
        }
    }

    function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): array {
        $evaluation_configuration = $evaluation->registration->opportunity->evaluationMethodConfiguration;

        $sections = $evaluation_configuration->sections ?: [];
        $criteria = $evaluation_configuration->criteria ?: [];

        foreach($sections as &$section) {
            $section->criteria = [];

            foreach($criteria as &$cri){
                if(($cri->sid ?? false) == $section->id) {
                    unset($cri->sid);
                    $result = isset($evaluation->evaluationData->{$cri->id}) ? $evaluation->evaluationData->{$cri->id} : [];
                    
                    // para as avaliações legadas antes da versão 7.6
                    if(!is_array($result)){
                        $result = $this->parseLegacyResult($result);
                    }

                    $cri->result = $result;
                    if (is_array($result) && in_array('others', $result) && isset($evaluation->evaluationData->{$cri->id . '_reason'})) {
                        $cri->result[] = $evaluation->evaluationData->{$cri->id . '_reason'};
                        $key = array_search('others', $cri->result);
                        unset($cri->result[$key]);
                        $cri->result = array_values($cri->result);
                    }
                    $section->criteria[] = $cri;
                }
            }
        }

        return [
            'result' => $evaluation->result,
            'scores' => $sections,
            'obs' => $evaluation->evaluationData->obs
        ];
    }

    function _getConsolidatedDetails(Entities\Registration $registration): array {
        $evaluation_configuration = $registration->opportunity->evaluationMethodConfiguration;
        $sections =  [];
        $criteria = [];

        if($registration->sentEvaluations){
            $sections = $evaluation_configuration->sections ?: [];
            $criteria = $evaluation_configuration->criteria ?: [];
    
            foreach($sections as &$section) {
                $section->criteria = [];
    
                foreach($criteria as &$cri){
                    if(($cri->sid ?? false) == $section->id) {
                        unset($cri->sid);
                        $section->criteria[] = $cri;
                    }
                }
            }
        }
        
        return [
            'scores' => $sections,
        ];
    }

    protected function _register()
    {
        $app = App::i();

        $this->registerEvaluationMethodConfigurationMetadata('sections', [
            'label' => i::__('Seções'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function ($val) {
                return $val ? json_decode($val) : $val;
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('criteria', [
            'label' => i::__('Critérios'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function ($val) {
                return $val ? json_decode($val) : $val;
            }
        ]);

        $app->registerJobType(new JobTypes\Spreadsheet('qualification-spreadsheets'));
    }

    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data)
    {
        $errors = [];
              
        foreach($evaluation_method_configuration->criteria as $key => $c){
            if(isset($data[$c->id])){
                $values = $data[$c->id];
                $options = ['valid', 'invalid', 'not-applicable', 'others'];
                
                if($c->options ?? false) {
                    $options = array_merge($c->options, $options);
                }

                if(is_array($values)) {
                    foreach($values as $val) {
                        if(!in_array($val, $options)){
                            $errors[] = i::__("O valor do critério {$c->name} é inválido");
                            break;
                        } 
                    }
                }
            }
        }

        if(!$errors){
            foreach($data as $key => $val){
                // if($key === i::__('obs') && !trim($val)) {
                //     $errors[] = i::__('O campo Observações é obrigatório');
                // }
            }
        }

        return $errors;
    }

    function enqueueScriptsAndStyles()
    {
        $app = App::i();

        $app = App::i();
        $app->view->enqueueScript('app', 'qualification-evaluation-form', 'js/ng.evaluationMethod.qualification.js', ['entity.module.opportunity']);
        $app->view->enqueueStyle('app', 'qualification-evaluation-method', 'css/qualification-evaluation-method.css');

        $app->view->localizeScript('qualificationEvaluationMethod', [
            'sectionNameAlreadyExists' => i::__('Já existe uma seção com o mesmo nome'),
            'changesSaved' => i::__('Alteraçṍes salvas'),
            'deleteSectionConfirmation' => i::__('Deseja remover a seção? Esta ação não poderá ser desfeita e também removerá todas os critérios desta seção.'),
            'deleteCriterionConfirmation' => i::__('Deseja remover este critério de avaliação? Esta ação não poderá ser desfeita.'),
            'deleteAffirmativePolicy' => i::__('Deseja remover esta política afirmativa? Esta ação não poderá ser desfeita.'),
            'disabled' => i::__('Inabilitado'),
            'enabled' => i::__('Habilitado'),
            'notApplicable' => i::__('Não se aplica'),
            'notAvaliable' => i::__('Não avaliada'),
        ]);

        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.qualification';

        $app->view->jsObject['evaluationStatus']['qualification'] = $this->evaluationStatues;
    }

    public function _init()
    {
        $app = App::i();
        $app->hook('evaluationsReport(qualification).sections', function (Entities\Opportunity $opportunity, &$sections) {
            $i = 0;
            $get_next_color = function ($last = false) use (&$i) {
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
            foreach ($cfg->sections as $sec) {
                $section = (object) [
                    'label' => $sec->name,
                    'color' => $get_next_color(),
                    'columns' => []
                ];

                foreach ($cfg->criteria as $crit) {
                    if ($crit->sid != $sec->id) {
                        continue;
                    }

                    $section->columns[] = (object) [
                        'label' => $crit->name,
                        'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($crit) {
                            $_result = "";

                            if(isset($evaluation->evaluationData->{$crit->id})){
                                switch ($evaluation->evaluationData->{$crit->id}) {
                                    case i::__('Habilitado'):
                                        $_result = i::__('Habilitado');
                                        break;
                                    case i::__('Não se aplica'):
                                        $_result = i::__('Não se aplica');
                                        break;
                                    default:
                                        $_result = i::__('Inabilitado');
                                        break;
                                }
                            }

                            return $_result;
                        }
                    ];
                }

                $result[] = $section;
            }

            $result['evaluation'] = $sections['evaluation'];

            $result['evaluation']->columns[] = (object) [
                'label' => i::__('Motivo(s) da inabilitação'),
                'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($crit) {
                    $_result = "";
                    if($evaluationData = (array) $evaluation->evaluationData){
                        $reasons = [];
                        foreach($evaluationData as $cri => $eval){
                            if($cri != "obs" && !in_array($eval, [i::__('Não se aplica'), i::__('Habilitado')])){
                                $reasons[] = $eval;
                            }
                        }                        
                        $_result = implode("; ", $reasons);
                    }

                    return $_result;
                }
            ];

            // adiciona coluna do parecer técnico
            $result['evaluation']->columns[] = (object) [
                'label' => i::__('Observações'),
                'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($crit) {
                    return isset($evaluation->evaluationData->obs) ? $evaluation->evaluationData->obs : '';
                }
            ];
            

            $sections = $result;

            $this->viability_status = [
                'valid' => i::__('Válido'),
                'invalid' => i::__('Inválido')
            ];
        });
    }
}
