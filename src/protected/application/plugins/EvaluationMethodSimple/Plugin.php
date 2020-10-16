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

        $app->hook('GET(opportunity.applyEvaluationsSimple)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'simple') {
                throw new Exception('Ação somente disponivel para avaliações do tipo simples');
            }
    
            $opp->checkPermission('@control');

            // pesquise todas as registrations da opportunity que esta vindo na request
            $query = App::i()->getEm()->createQuery("
            SELECT 
                r
            FROM
                MapasCulturais\Entities\Registration r
            WHERE 
                r.opportunity = :opportunity_id
                    AND
                r.status > 0
            ");
        
            $params = [
                'opportunity_id' => $opp,
            ];
    
            $query->setParameters($params);
    
            $registrations = $query->getResult();
    
            // faça um foreach em cada registration e pegue as suas avaliações
            foreach ($registrations as $registration) {
                $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration'=>$registration->id]);
    
                $allEvaluationsAreStatus10 = true;
                //verifique se TODAS as avaliações estão selecionadas, se sim, registration foi aprovada
                //se não, verifique se a registration tem SOMENTE UMA avaliação, se tiver uma, então o status da registration é o mesmo da avaliação, se tiver mais de uma, o status é "nao selecionado"
                foreach ($evaluations as $evaluation) {
                    $evaluationStatus = $evaluation->getResult();
                    if($evaluationStatus != 10) {
                        $allEvaluationsAreStatus10 = false;
                    }
                }
                if($allEvaluationsAreStatus10 == true) {
                    $registration->setStatus(10); //selecionada
                    $registration->consolidatedResult = 10; //selecionada
                } 
                if($allEvaluationsAreStatus10 == false) {
                    if(count($evaluations) > 1) {
                        $registration->setStatus(3); // não selecionada
                        $registration->consolidatedResult = 3; // não selecionada
                    }
                    if(count($evaluations) == 1) {
                        $registration->setStatus( (int)$evaluations[0]->getResult() );
                        $registration->consolidatedResult = (int)$evaluations[0]->getResult();
                    }
                } 
    
                $registration->save(true);
            }

    
            $this->finish("Processo finalizado", 200);
    
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function(){
            $this->part('simple--apply-results', ['entity' => $this->controller->requestedEntity]);

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
                return $value ?: '';

        }
    }
    
    public function fetchRegistrations() {
        return true;
    }

}
