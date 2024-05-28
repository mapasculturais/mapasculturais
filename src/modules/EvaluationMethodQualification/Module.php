<?php

namespace EvaluationMethodQualification;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Registration;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';


class Module extends \MapasCulturais\EvaluationMethod
{

    public function getSlug()
    {
        return i::__('qualification');
    }

    public function getName()
    {
        return i::__('Avaliação de habilitação documental');
    }

    public function getDescription()
    {
    }

    protected function _getConsolidatedResult(Entities\Registration $registration)
    {
        $app = App::i();
        $status = [
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_DRAFT,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_EVALUATED,
            \MapasCulturais\Entities\RegistrationEvaluation::STATUS_SENT
        ];

        $committee = $registration->opportunity->getEvaluationCommittee();
        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByRegistrationAndUsersAndStatus($registration, $users, $status);

        $result = i::__("Habilitado");
        foreach ($evaluations as $eval){
            $_result = $this->getEvaluationResult($eval);
            if($_result == i::__("Inabilitado")){
                $result = $_result;
            }
        }

        return $result;

    }

    public function getEvaluationStatues()
    {
        $status = [
            'valid' => i::__(['Habilitado']),
            'invalid' => i::__(['Inabilitado'])
        ];

        return $status;
    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation)
    {
        $approved = [i::__('Habilitado'), i::__('Não se aplica')];
        $result = i::__("Habilitado");
        $cfg = $evaluation->getEvaluationMethodConfiguration();
        foreach($cfg->criteria as $cri){
            $key = $cri->id;
            if(!isset($evaluation->evaluationData->$key)){
                return null;
            } else {
                if(!in_array($evaluation->evaluationData->$key, $approved)){
                    $result = i::__("Inabilitado");
                    break;
                }
            }
        }

        return $result;
    }

    public function valueToString($value)
    {
        if(is_null($value)){
            return i::__('');
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

            foreach($criteria as &$cri){
                if(($cri->sid ?? false) == $section->id) {
                    unset($cri->sid);
                    $result = isset($evaluation->evaluationData->{$cri->id}) ? $evaluation->evaluationData->{$cri->id} : '';
                    
                    $cri->result = $result;
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
        $this->registerEvaluationMethodConfigurationMetadata('sections', [
            'label' => i::__('Seções'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function ($val) {
                return json_decode($val);
            }
        ]);

        $this->registerEvaluationMethodConfigurationMetadata('criteria', [
            'label' => i::__('Critérios'),
            'type' => 'json',
            'serialize' => function ($val) {
                return json_encode($val);
            },
            'unserialize' => function ($val) {
                return json_decode($val);
            }
        ]);
    }

    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data)
    {
        $errors = [];
              
        foreach($evaluation_method_configuration->criteria as $key => $c){
            if(isset($data[$c->id])){
                $val = $data[$c->id];
                $options = ['Habilitado', 'Inabilitado', 'Não se aplica'];
                if($c->options) {
                    $options = array_merge($c->options, $options);
                }
                if(!in_array($val, $options)){
                    $errors[] = i::__("O valor do critério {$c->name} é inválido");
                    break;
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
