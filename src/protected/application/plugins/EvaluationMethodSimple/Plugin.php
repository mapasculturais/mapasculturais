<?php

namespace EvaluationMethodSimple;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;

class Plugin extends \MapasCulturais\EvaluationMethod {

    public function getSlug() {
        return 'simple';
    }

    public function getName() {
        return i::__('Avaliação Simplificada');
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
        ;
    }

    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'simple-evaluation-form', 'js/ng.evaluationMethod.simple.js', ['entity.module.opportunity']);
        $app->view->enqueueStyle('app', 'simple-evaluation-method', 'css/simple-evaluation-method.css');

        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.simple';
    }

    public function _init()
    {
        $app = App::i();
        $app->hook('evaluationsReport(simple).sections', function (Entities\Opportunity $opportunity, &$sections) use ($app) {
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
    }

    public function _getConsolidatedResult(Entities\Registration $registration) {
        $app = App::i();

        $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration]);

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

    public function valueToString($value) {
        switch ($value) {
            case 2:
                return i::__('Inválida');
                break;
            case 3:
                return i::__('Não selecionada');
                break;
            case 8:
                return i::__('Suplente');
                break;
            case 10:
                return i::__('Selecionada');
                break;
            default:
                return '';

        }
    }
    
    public function fetchRegistrations() {
        return true;
    }

    public function getResultStatusList(){
        return [
             (object) ['label' => i::__('Selecionada') , 'value' => 10 ], 
             (object) ['label' => i::__('Suplente') , 'value' => 8 ],
             (object) ['label' => i::__('Não Selecionada') , 'value' => 3 ],
             (object) ['label' => i::__('Inválida') , 'value' => 2],
             (object) ['label' => i::__('Pendente') , 'value' => 0],
        ];
    }

}
