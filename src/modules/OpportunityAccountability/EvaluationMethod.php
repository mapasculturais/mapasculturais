<?php
namespace OpportunityAccountability;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Registration;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';

class EvaluationMethod extends \MapasCulturais\EvaluationMethod {

    protected $module;

    protected $internal = true;

    public function getSlug() {
        return 'accountability';
    }

    public function getName() {
        return i::__('Prestação de Contas');
    }

    public function getDescription() {
        return i::__('Método de avaliação interno da prestação de contas');
    }

    public function cmpValues($value1, $value2){
        $value1 = (float) $value1;
        $value2 = (float) $value2;
        
        return parent::cmpValues($value1, $value2);
    }

    public function getConfigurationFormPartName() {
        return ;
    }

    protected function _register() {    }


    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data){
        $errors = [];

        if(empty($data['result']) || empty($data['obs'])) {
            $errors[] = i::__('É necessário escrever o parecer técnico e informar o resultado');
        }

        return $errors;
    }


    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'accountability-evaluation', 'js/ng.evaluationMethod.accountability.js', ['entity.module.opportunity']);
        $app->view->enqueueStyle('app', 'accountability-evaluation-method', 'css/accountability-evaluation-method.css');
        
        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.accountability';
    }

    public function _init() {
        $app = App::i();

        /** 
         * @todo implementar o hook
         * $app->hook('evaluationsReport(accountability).sections', function(Entities\Opportunity $opportunity, &$sections) use($app) {
        */
         
        // acplicação dos pareceres
        $app->hook('POST(opportunity.applyEvaluationsAccountability)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'accountability') {
                $this->errorJson(i::__('Somente para pareceres da prestação de contas'), 400);
                die;
            }

            if (!is_numeric($this->data['to']) || !in_array($this->data['to'], [0,2,3,8,10])) {
                $this->errorJson(i::__('os status válidos são 0, 2, 3, 8 e 10'), 400);
                die;
            }
            $new_status = intval($this->data['to']);
            
            $apply_status = $this->data['status'] ?? false;
            if ($apply_status == 'all') {
                $status = 'r.status > 0';
            } else {
                $status = 'r.status = 1';
            }
    
            $opp->checkPermission('@control');

            // pesquise todas as registrations da opportunity que esta vindo na request
            $dql = "
            SELECT 
                r.id
            FROM
                MapasCulturais\Entities\Registration r
            WHERE 
                r.opportunity = :opportunity_id AND
                r.consolidatedResult = :consolidated_result AND
                r.status <> $new_status AND
                $status
            ";
            $query = $app->em->createQuery($dql);
        
            $params = [
                'opportunity_id' => $opp->id,
                'consolidated_result' => $this->data['from']
            ];
    
            $query->setParameters($params);
    
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
                    default:
                        $registration->_setStatusTo($new_status);
                    
                }
                $app->disableAccessControl();
                $registration->save(true);
                $app->enableAccessControl();
            }


    
            $this->finish(sprintf(i::__("Pareceres aplicados à %s prestações de contas"), count($registrations)), 200);
    
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function() use($app) {
            $opportunity = $this->controller->requestedEntity;
            
            if ($opportunity->evaluationMethodConfiguration->getDefinition()->slug != 'accountability') {
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
            
            $this->part('accountability/apply-results', ['entity' => $opportunity, 'consolidated_results' => $consolidated_results]);
        });
    }

    public function _getConsolidatedResult(Entities\Registration $registration) {
        $app = App::i();

        $evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration]);

        if(is_array($evaluations) && count($evaluations) === 0){
            return 0;
        }

        $result = 0;

        foreach ($evaluations as $eval){
            if($eval->status === \MapasCulturais\Entities\RegistrationEvaluation::STATUS_DRAFT){
                return 0;
            }

            if(($evaluation_result = $this->getEvaluationResult($eval)) > $result){
                $result = $evaluation_result;
            }
        }

        return $result;
    }

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation) {
        $data = (array) $evaluation->evaluationData;
        
        return $data['result'] ?? 0;
    }

    public function valueToString($value) {

        if ($value == 10){
            return i::__('Aprovado');

        } else if ($value == 8){
            return i::__('Aprovado com ressalvas');

        } else if ($value == 3) {
            return i::__('Não aprovado');

        } else {
            return $value ?: '';
        }
    }
    
    public function fetchRegistrations() {
        return true;
    }

}
