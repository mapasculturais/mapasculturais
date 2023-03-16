<?php

namespace EvaluationMethodQualification;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Registration;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';


class Plugin extends \MapasCulturais\EvaluationMethod
{

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

    protected function _getConsolidatedResult(Entities\Registration $registration)
    {
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
        $_result = "Habilitado";
        foreach ($evaluations as $eval){
            $_result = $this->getEvaluationResult($eval);
            if($_result == "Inabilitado"){
                $result = $_result;
            }
        }

        return $result;

    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation)
    {
        $approved = ['Habilitado', 'Não se aplica'];
        $result = "Habilitado";
        $cfg = $evaluation->getEvaluationMethodConfiguration();
        foreach($cfg->criteria as $cri){
            $key = $cri->id;
            if(!isset($evaluation->evaluationData->$key)){
                return null;
            } else {
                if(!in_array($evaluation->evaluationData->$key, $approved)){
                    $result = "Inabilitado";
                    break;
                }
            }
        }

        return $result;
    }

    public function valueToString($value)
    {
        if(is_null($value)){
            return i::__('Avaliação incompleta');
        } else {
            return $value;
        }
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
        $empty = true;
        foreach ($data as $prop => $val) {
            if ($val['evaluation']) {
                $empty = false;
            }
        }

        if ($empty) {
            $errors[] = i::__('Nenhum campo foi avaliado');
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
            'deleteAffirmativePolicy' => i::__('Deseja remover esta política afirmativa? Esta ação não poderá ser desfeita.')
        ]);

        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.qualification';
    }

    public function _init()
    {
        $app = App::i();
        $app->hook('evaluationsReport(technical).sections', function (Entities\Opportunity $opportunity, &$sections) {
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
                        'label' => $crit->title . ' ' . sprintf(i::__('(peso: %s)'), $crit->weight),
                        'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($crit) {
                            return isset($evaluation->evaluationData->{$crit->id}) ? $evaluation->evaluationData->{$crit->id} : '';
                        }
                    ];
                }

                $max = 0;
                foreach ($cfg->criteria as $crit) {
                    if ($crit->sid != $sec->id) {
                        continue;
                    }

                    $max += $crit->max * $crit->weight;
                }

                $section->columns[] = (object) [
                    'label' => sprintf(i::__('Subtotal (max: %s)'), $max),
                    'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($sec, $cfg) {
                        $result = 0;
                        foreach ($cfg->criteria as $crit) {
                            if ($crit->sid != $sec->id) {
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
                'getValue' => function (Entities\RegistrationEvaluation $evaluation) use ($crit) {
                    return isset($evaluation->evaluationData->obs) ? $evaluation->evaluationData->obs : '';
                }
            ];

            $viability = [
                'label' => i::__('Esta proposta apresenta exequibilidade?'),
                'getValue' => function (Entities\RegistrationEvaluation $evaluation) {
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
}
