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

        $app->hook('repo(Registration).getIdsByKeywordDQL.where', function(&$where, $keyword) {
            $key = trim(strtolower(str_replace('%','',$keyword)));
            
            $value = null;
            if (in_array($key, explode(',', i::__('inválida,invalida,inválido,invalido')))) {
                $value = '2';
            } else if (in_array($key, explode(',', i::__('não selecionado,nao selecionado,não selecionada,nao selecionada')))) {
                $value = '3';
            } else if ($key == i::__('suplente')) {
                $value = '8';
            } else if (in_array($key, explode(',', i::__('selecionado,selecionada')))) {
                $value = '10';
            }

            if ($value) {
                $where .= " OR e.consolidatedResult = '$value'";
            } 
            
            $where .= " OR unaccent(lower(e.consolidatedResult)) LIKE unaccent(lower(:keyword))";
        });

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

        $app->hook('POST(opportunity.applyEvaluationsSimple)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'simple') {
                $this->errorJson(i::__('Somente para avaliações simplificadas'), 400);
                die;
            }

            if (!is_numeric($this->data['to']) || !in_array($this->data['to'], [0,2,3,8,10])) {
                $this->errorJson(i::__('os status válidos são 0, 2, 3, 8 e 10'), 400);
                die;
            }
            $new_status = intval($this->data['to']);
    
            $opp->checkPermission('@control');

            // pesquise todas as registrations da opportunity que esta vindo na request
            $query = App::i()->getEm()->createQuery("
            SELECT 
                r
            FROM
                MapasCulturais\Entities\Registration r
            WHERE 
                r.opportunity = :opportunity_id AND
                r.consolidatedResult = :consolidated_result AND
                r.status > 0
            ");
        
            $params = [
                'opportunity_id' => $opp->id,
                'consolidated_result' => $this->data['from']
            ];
    
            $query->setParameters($params);
    
            $registrations = $query->getResult();
            
            // faça um foreach em cada registration e pegue as suas avaliações
            foreach ($registrations as $registration) {
                $app->log->debug("Alterando status da inscrição {$registration->number} para {$new_status}");
                $app->disableAccessControl();
                $registration->consolidatedResult = "$new_status";
                $registration->setStatus($new_status);
                $registration->save(true);
                $app->enableAccessControl();
            }

    
            $this->finish("Processo finalizado", 200);
    
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function() use($app) {
            $opportunity = $this->controller->requestedEntity;
    
            if ($opportunity->evaluationMethodConfiguration->getDefinition()->slug != 'simple') {
                return;
            }
            
            $consolidated_results = $app->em->getConnection()->fetchAll("
                SELECT 
                    consolidated_result evaluation,
                    COUNT(*) as num
                FROM 
                    registration
                WHERE 
                    opportunity_id = :opportunity AND
                    status > 0 
                GROUP BY consolidated_result
                ORDER BY num DESC", ['opportunity' => $opportunity->id]);
            
            $this->part('simple--apply-results', ['entity' => $opportunity, 'consolidated_results' => $consolidated_results]);
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
            case '2':
                return i::__('Inválida');
                break;
            case '3':
                return i::__('Não selecionada');
                break;
            case '8':
                return i::__('Suplente');
                break;
            case '10':
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