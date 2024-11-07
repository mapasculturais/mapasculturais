<?php
namespace EvaluationMethodDocumentary;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Registration;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';

class Module extends \MapasCulturais\EvaluationMethod {


    public function getSlug() {
        return 'documentary';
    }

    public function getName() {
        return i::__('Avaliação Documental');
    }

    public function getDescription() {
        return i::__('Consiste num checkbox e um textarea para cada campo do formulário de inscrição.');
    }

    public function cmpValues($value1, $value2){
        $value1 = (float) $value1;
        $value2 = (float) $value2;
        
        return parent::cmpValues($value1, $value2);
    }

    public function getConfigurationFormPartName() {
        return ;
    }

    protected function _register() {
        $app = App::i();
        
        $app->registerJobType(new JobTypes\Spreadsheet('documentary-spreadsheets'));
    }

    public function getEvaluationStatues()
    {
        $status = [
            'valid' => ['1'],
            'invalid' => ['-1']
        ];

        return $status;
    }


    function getValidationErrors(Entities\EvaluationMethodConfiguration $evaluation_method_configuration, array $data){
        $errors = [];
        $empty = true;
        foreach($data as $prop => $val){
            if($val['evaluation']){
                $empty = false;
            }
        }

        if($empty){
            $errors[] = i::__('Nenhum campo foi avaliado');
        }

        return $errors;
    }


    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'documentary-evaluation', 'js/ng.evaluationMethod.documentary.js', ['entity.module.opportunity']);
        $app->view->enqueueScript('app', 'documentary-evaluation-form', 'js/evaluation-form--documentary.js', ['entity.module.opportunity']);
        $app->view->enqueueStyle('app', 'documentary-evaluation-method', 'css/documentary-evaluation-method.css');
        
        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.documentary';

        $app->view->jsObject['evaluationStatus']['documentary'] = $this->evaluationStatues;
    }

    public function _init() {
        $app = App::i();

        $self = $this;
        
        $app->hook('template(opportunity.registrations.registration-list-actions-entity-table):begin', function($entity){
            if($em = $entity->evaluationMethodConfiguration){
                if($em->getEvaluationMethod()->slug == "documentary"){
                    $this->part('documentary--evaluation-result-apply');
                }
            }
        });

        $app->hook('evaluationsReport(documentary).sections', function(Entities\Opportunity $opportunity, &$sections) use($app) {
            $columns = [];
            $evaluations = $opportunity->getEvaluations();

            foreach($evaluations as $eva){
                $evaluation = $eva['evaluation'];
                $data = (array) $evaluation->evaluationData;
                foreach($data as $id => $d){
                    $columns[$id] = $d['label'];
                }
            }

            $result = [
                'registration' => $sections['registration'],
                'committee' => $sections['committee'],
            ];


            foreach($columns as $id => $col){
                $result[$id] = (object) [
                    'label' => $col,
                    'color' => '#EEEEEE',
                    'columns' => [
                        'val' => (object) [
                            'label' => i::__('Avaliação'),
                            'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($id) {
                                $evaluation_data = (array) $evaluation->evaluationData;

                                if(isset($evaluation_data[$id])){
                                     $data = $evaluation_data[$id];

                                     if($data['evaluation'] == 'valid'){
                                         return i::__('Válida');
                                     } else if($data['evaluation'] == 'invalid') {
                                         return i::__('Inválida');
                                     } else {
                                         return '';
                                     }
                                } else {
                                    return '';
                                }
                            }
                        ],
                        'obs' => (object) [
                            'label' => i::__('Observações'),
                            'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($id) {

                                $evaluation_data = (array) $evaluation->evaluationData;
                                if (isset($evaluation_data[$id]) && isset($evaluation_data[$id]['obs'])) {
                                    return $evaluation_data[$id]['obs'];
                                } else {
                                    return '';
                                }
                            }
                        ],
                        'obs_items' => (object) [
                            'label' => i::__('Descumprimento do(s) item(s) do edital'),
                            'getValue' => function(Entities\RegistrationEvaluation $evaluation) use($id) {

                                $evaluation_data = (array) $evaluation->evaluationData;
                                if (isset($evaluation_data[$id]) && isset($evaluation_data[$id]['obs_items'])) {
                                    return $evaluation_data[$id]['obs_items'];
                                } else {
                                    return '';
                                }
                            }
                        ],
                    ]
                ];
            }

            $result['evaluation'] = $sections['evaluation'];

            $sections = $result;
        });

        $app->hook('POST(opportunity.applyEvaluationsDocumentary)', function() {
            $this->requireAuthentication();

            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
    
            $app = App::i();
    
            $opp = $this->requestedEntity;
    
            $type = $opp->evaluationMethodConfiguration->getDefinition()->slug;
    
            if($type != 'documentary') {
                $this->errorJson(i::__('Somente para avaliações documentais'), 400);
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
            
            // faça um foreach em cada registration e pegue as suas avaliações
            foreach ($registrations as $reg) {
                $count++;
                /** @var Registration $registration */
                $registration = $app->repo('Registration')->find($reg['id']);
                $registration->skipSync = true;
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

                $app->em->clear();
            }


            if($next_phase = $opp->nextPhase) {
                $next_phase->enqueueRegistrationSync();
            }

            if ($total > 0) {
                $opp->enqueueToPCacheRecreation();
            }
    
            $this->finish(sprintf(i::__("Avaliações aplicadas à %s inscrições"), count($registrations)), 200);
    
        });

        $app->hook('template(opportunity.single.header-inscritos):actions', function() use($app, $self) {
            $opportunity = $this->controller->requestedEntity;
            
            if ($opportunity->evaluationMethodConfiguration->getDefinition()->slug != 'documentary') {
                return;
            }

            $consolidated_results = $self->findConsolidatedResult($opportunity);
            
            $this->part('documentary--apply-results', ['entity' => $opportunity, 'consolidated_results' => $consolidated_results]);
        });
    }

    public function findConsolidatedResult($opportunity)
    {
        $app = App::i();
        
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

        return $consolidated_results;
    }

    public function _getConsolidatedResult(Entities\Registration $registration, array $evaluations) {
        $app = App::i();

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

    public function getEvaluationResult(Entities\RegistrationEvaluation $evaluation) {
        $data = (array) $evaluation->evaluationData;
        
        if(is_array($data) && count($data) == 0){
            return 1; // valid
        }

        foreach ($data as $id => $value) {
            if(isset($value['evaluation']) && $value['evaluation'] === STATUS_INVALID){
                return -1;
            }
        }

        return 1;
    }

    protected function _valueToString($value) {

        if($value == 1){
            return i::__('Válida');
        } else if($value == -1){
            return i::__('Inválida');
        }

        return $value ?: '';

    }

    function _getEvaluationDetails(Entities\RegistrationEvaluation $evaluation): ?array {
        $result = [];
        if($data = (array) $evaluation->evaluationData){
            foreach($data as $id => $value) {
                if(isset($value['evaluation']) && $value['evaluation'] != ""){
                    $data = [
                        "fieldId" => $id,
                        "label" => $value['label'],
                        "obs" => $value['obs'],
                        "obs_items" => $value['obsItems'],
                        "evaluation" => $value['evaluation'],
                    ];

                    $result['evaluations'][] = $data;
                }
            }
        }
        $result['evaluationResult'] = $evaluation->result;
        return $result;
    }

    function _getConsolidatedDetails(Entities\Registration $registration): ?array {
        return null;
    }

}
