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
    }
    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation)
    {
        $app = App::i();

        $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration]);

        if(is_array($evaluations) && count($evaluations) === 0){
            return 0;
        }

        $result = 1;

        foreach ($evaluations as $eval){
            if($eval->status === \MapasCulturais\Entities\RegistrationEvaluation::STATUS_DRAFT){
                return 0;
            }

            $result = ($result === 1 && $this->getEvaluationResult($eval) === 1) ? 1 : -1;
        }

        return $result;
    }

    public function valueToString($value)
    {
    }



    protected function _register()
    {
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
