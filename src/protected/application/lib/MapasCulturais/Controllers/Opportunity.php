<?php
namespace MapasCulturais\Controllers;

use Exception;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities;

/**
 * Opportunity Controller
 *
 * By default this controller is registered with the id 'opportunity'.
 *
 *  @property-read \MapasCulturais\Entities\Opportunity $requestedEntity The Requested Entity
 */
class Opportunity extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSealRelation,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerAPINested;

    function GET_create() {
        // @TODO: definir entitidade relacionada

        parent::GET_create();
    }

    function POST_index($data = null)
    {
        $app = App::i();

        $classes = [
            'agent' => Entities\AgentOpportunity::class,
            'event' => Entities\EventOpportunity::class,
            'space' => Entities\SpaceOpportunity::class,
            'project' => Entities\ProjectOpportunity::class,
        ];

        $class = $classes[$this->data['objectType']] ?? null;

        // objectType é agent,space,project ou event
        if($class){

            $self = $this;
            $app->hook('entity(Opportunity).insert:after', function () use($self, $app) {

                $definition = $app->getRegisteredEvaluationMethodBySlug($self->data['evaluationMethod']);

                $emconfig = new Entities\EvaluationMethodConfiguration;

                $emconfig->opportunity = $this;

                $emconfig->type = $definition->slug;

                $emconfig->save(true);
            });
            $this->entityClassName = $class;
            parent::POST_index($data);
        }
    }

    function ALL_sendEvaluations(){
        $this->requireAuthentication();

        $app = App::i();

        $opportunity = $this->requestedEntity;

        if(!$opportunity)
            $app->pass();

        $opportunity->sendUserEvaluations();

        if($app->request->isAjax()){
            $this->json($opportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function ALL_publishRegistrations(){
        $this->requireAuthentication();

        $app = App::i();

        $opportunity = $this->requestedEntity;

        $opportunity->registerRegistrationMetadata();

        if(!$opportunity)
            $app->pass();

        $opportunity->publishRegistrations();

        if($app->request->isAjax()){
            $this->json($opportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function GET_report(){
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity){
            $app->pass();
        }

        $entity->checkPermission('@control');

        $app->controller('Registration')->registerRegistrationMetadata($entity);

        $date = date('Y-m-d.Hi');

        $filename = sprintf(\MapasCulturais\i::__("oportunidade-%s--inscricoes--%s"), $entity->id, $date);

        //$this->reportOutput('report', ['entity' => $entity], $filename);
        $this->reportOutput('report-csv', ['entity' => $entity], $filename);
    }

    function GET_reportDrafts(){
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;
        $entity->checkPermission('@control');
        $app->controller('Registration')->registerRegistrationMetadata($entity);
        $registrationsDraftList = $entity->getRegistrationsByStatus(Entities\Registration::STATUS_DRAFT);

        $date = date('Y-m-d.Hi');

        $filename = sprintf(\MapasCulturais\i::__("oportunidade-%s--rascunhos--%s"), $entity->id, $date);

        $this->reportOutput('report-drafts-csv', ['entity' => $entity, 'registrationsDraftList' => $registrationsDraftList], $filename );
     }

    function GET_reportEvaluations(){
        $this->requireAuthentication();
        $app = App::i();

        if (is_array($this->urlData) && isset($this->urlData["id"])) {
            $ID = (int) $this->urlData["id"];
        }

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity->checkPermission('canUserViewEvaluations');

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $app->controller('Registration')->registerRegistrationMetadata($entity);

        $committee = $entity->getEvaluationCommittee();
        $users = [];
        foreach ($committee as $item) {
            $users[] = $item->agent->user->id;
        }

        $evaluations = $app->repo('RegistrationEvaluation')->findByOpportunityAndUsersAndStatus($entity, $users);

        $filename = sprintf(\MapasCulturais\i::__("oportunidade-%s--avaliacoes"), $entity->id);

        $all_evaluations = $this->API_findEvaluations($ID);

        $cfg = $entity->getEvaluationMethod()->getReportConfiguration($entity);

        $this->reportOutput('report-evaluations', ['cfg' => $cfg, 'evaluations' => $evaluations, 'pending_evaluations' => $all_evaluations], $filename);
    }

    protected function reportOutput($view, $view_params, $filename){
        $app = App::i();
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        if ($view == 'report-drafts-csv' || $view == 'report-csv') {

            $response = $app->response();
            $response['Content-Encoding'] = 'UTF-8';
            $response['Content-Type'] = 'application/force-download';
            $response['Content-Disposition'] = 'attachment; filename=' . $filename . '.csv';
            $response['Pragma'] = 'no-cache';

            $app->contentType('text/csv; charset=UTF-8');

            ob_start();
            $this->partial($view, $view_params);

            $output = ob_get_clean();

            /**
             * @todo criar regex para os replaces abaixo
             */
            $replaces = [
                '<!-- BaseV1/views/opportunity/report-drafts-csv.php # BEGIN -->',
                '<!-- BaseV1/views/opportunity/report-drafts-csv.php # END -->',
                '<!-- BaseV1/views/opportunity/report-csv.php # BEGIN -->',
                '<!-- BaseV1/views/opportunity/report-csv.php # END -->'
            ];

            foreach ($replaces as $replace) {
                $output = str_replace($replace, '', $output);
            }

            echo $output;

        } else {

            if (!isset($this->urlData['output']) || $this->urlData['output'] == 'xls') {
                $response = $app->response();
                $response['Content-Encoding'] = 'UTF-8';
                $response['Content-Type'] = 'application/force-download';
                $response['Content-Disposition'] = 'attachment; filename=' . $filename . '.xls';
                $response['Pragma'] = 'no-cache';

                $app->contentType('application/vnd.ms-excel; charset=UTF-8');
            }

            ob_start();
            $this->partial($view, $view_params);
            $output = ob_get_clean();
            echo mb_convert_encoding($output, "HTML-ENTITIES", "UTF-8");

        }

    }


    function API_findByUserApprovedRegistration(){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        $this->requireAuthentication();
        $app = App::i();

        $dql = "SELECT r
                FROM \MapasCulturais\Entities\Registration r
                JOIN r.opportunity p
                JOIN r.owner a
                WHERE a.user = :user
                AND r.status > 0";
        $query = $app->em->createQuery($dql)->setParameters(['user' => $app->user]);

        $registrations = $query->getResult();


        $opportunities = array_map(function($r){
            return $r->opportunity;
        }, $registrations);

        $this->apiResponse($opportunities);
    }

    /**
    * @return \MapasCulturais\Entities\Opportunity
    */
    protected function _getOpportunity($opportunity_id = null) {
        $app = App::i();
        if (!is_null($opportunity_id) && is_int($opportunity_id)) {
            $opportunity = $app->repo('Opportunity')->find($opportunity_id);
        } else {
            if(!isset($this->data['@opportunity'])){
                $this->apiErrorResponse('parameter @opportunity is required');
            }

            if(!is_numeric($this->data['@opportunity'])){
                $this->apiErrorResponse('parameter @opportunity must be an integer');
            }

            $opportunity = $app->repo('Opportunity')->find($this->data['@opportunity']);
        }

        if(!$opportunity){
            $this->apiErrorResponse('opportunity not found');
        }

        return $opportunity;
    }

    function getSelectFields(Entities\Opportunity $opportunity){
        $app = App::i();

        $fields = [];

        foreach($opportunity->registrationFieldConfigurations as $field){
            if($field->fieldType == 'select'){
                if(!isset($fields[$field->fieldName])){
                    $fields[$field->fieldName] = $field;
                }
            }
        }

        $app->applyHookBoundTo($this, 'controller(opportunity).getSelectFields', [$opportunity, &$fields]);

        return $fields;
    }

    function API_evaluationCommittee(){
        $this->requireAuthentication();

        $opportunity = $this->_getOpportunity();

        $opportunity->checkPermission('@control');

        $relations = $opportunity->getEvaluationCommittee();

        if(is_array($relations)){
            $result = array_map(function($e){
                $r = $e->simplify('id,hasControl,status,createdAt');
                $r->owner = $e->owner->id;
                $r->agent = $e->agent->simplify('id,name,type,singleUrl,avatar');
                $r->agentUserId = $e->agent->userId;
                return $r;
            }, $relations);
        } else {
            $result = [];
        }

        $this->apiAddHeaderMetadata($this->data, $result, count($result));
        $this->apiResponse($result);
    }

    function API_selectFields(){
        $app = App::i();

        $opportunity = $this->_getOpportunity();

        $fields = $this->getSelectFields($opportunity);

        $this->apiResponse($fields);
    }

    function API_findRegistrations() {
        $app = App::i();

        $app->registerFileGroup('registration', new \MapasCulturais\Definitions\FileGroup('zipArchive',[], '', true, null, true));

        $opportunity = $this->_getOpportunity();
        $data = $this->data;
        $data['opportunity'] = "EQ({$opportunity->id})";

        $_opportunity = $opportunity;
        $opportunity_tree = [];
        while($_opportunity && ($parent = $app->modules['OpportunityPhases']->getPreviousPhase($_opportunity))){
            $opportunity_tree[] = $parent;
            $_opportunity = $parent;
        }

        $opportunity_tree = array_reverse($opportunity_tree);

        $last_query_ids = null;

        $select_values = [];

        foreach($opportunity_tree as $current){
            $app->controller('registration')->registerRegistrationMetadata($current);
            $cdata = ['opportunity' => "EQ({$current->id})", '@select' => 'id,previousPhaseRegistrationId'];

            if($current->publishedRegistrations){
                $cdata['status'] = 'IN(10,8)';
            }

            foreach($current->registrationFieldConfigurations as $field){
                if($field->fieldType == 'select'){
                    $cdata['@select'] .= ",{$field->fieldName}";

                    if(isset($data[$field->fieldName])){
                        $cdata[$field->fieldName] = $data[$field->fieldName];
                        unset($data[$field->fieldName]);
                    }
                }
            }
            if(!is_null($last_query_ids)){
                if($last_query_ids){
                    $cdata['previousPhaseRegistrationId'] = "IN($last_query_ids)";
                } else {
                    $cdata['id'] = "IN(-1)";
                }
            }
            $_disable_access_control = $current->publishedRegistrations && !$current->canUser('viewEvaluations');
            $q = new ApiQuery('MapasCulturais\Entities\Registration', $cdata, false, false, $_disable_access_control);

            $regs = $q->find();

            foreach($regs as $reg){

                if($reg['previousPhaseRegistrationId'] && isset($select_values[$reg['previousPhaseRegistrationId']])){
                    $select_values[$reg['id']] = $reg + $select_values[$reg['previousPhaseRegistrationId']];
                } else {
                    $select_values[$reg['id']] = $reg;
                }
            }

            $ids = array_map(function ($r) { return $r['id']; }, $regs);
            $last_query_ids = implode(',', $ids);
        }

        $app->controller('registration')->registerRegistrationMetadata($opportunity);

        unset($data['@opportunity']);

        if(!is_null($last_query_ids)){
            if($last_query_ids){
                $data['previousPhaseRegistrationId'] = "IN($last_query_ids)";
            } else {
                $data['id'] = "IN(-1)";
            }
        }

        if($select_values){
            $data['@select'] = isset($data['@select']) ? $data['@select'] . ',previousPhaseRegistrationId' : 'previousPhaseRegistrationId';
        }

        if($opportunity->publishedRegistrations && !$opportunity->canUser('viewEvaluations')){

            if(isset($data['status'])){
                $data['status'] = 'AND(IN(10,8),' . $data['status'] . ')';
            } else {
                $data['status'] = 'IN(10,8)';
            }
        }
        $query = new ApiQuery('MapasCulturais\Entities\Registration', $data, false, false, $opportunity->publishedRegistrations);

        $registrations = $query->find();

        $em = $opportunity->getEvaluationMethod();
        foreach($registrations as &$reg) {
            if(in_array('consolidatedResult', $query->selecting)){
                $reg['evaluationResultString'] = $em->valueToString($reg['consolidatedResult']);
            }

            if(isset($reg['previousPhaseRegistrationId']) && $reg['previousPhaseRegistrationId'] && isset($select_values[$reg['previousPhaseRegistrationId']])){
                $values = $select_values[$reg['previousPhaseRegistrationId']];
                foreach($reg as $key => $val){
                    if(is_null($val) && isset($values[$key])){
                        $reg[$key] = $values[$key];
                    }
                }
            }
        }

        if(in_array('consolidatedResult', $query->selecting)){
            /* @TODO: considerar parâmetro @order da api */

            usort($registrations, function($e1, $e2) use($em){
                return $em->cmpValues($e1['consolidatedResult'], $e2['consolidatedResult']) * -1;
            });
        }

        $this->apiAddHeaderMetadata($this->data, $registrations, $query->count());
        $this->apiResponse($registrations);
    }

    protected function _getOpportunityCommittee($opportunity_id) {

        $opportunity = $this->_getOpportunity($opportunity_id);

        $committee_relation_query = new ApiQuery('MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation', [
            '@select' => 'id,agent',
            'owner' => "EQ({$opportunity->evaluationMethodConfiguration->id})",
        ]);
        $committee_relations = $committee_relation_query->find();

        $committee_ids = implode(
            ',',
            array_map(
                function($e){return $e['agent']; },
                array_filter( $committee_relations, function($e){ return empty($e['agent']) ? false : $e['agent']; })
            )
        );

        if($committee_ids){
            $vdata = [
                '@select' => 'id,name,user,singleUrl',
                'id' => "IN({$committee_ids})"
            ];

            if(!$opportunity->canUser('@control')){
                $vdata['@permissions'] = '@control';
            }

            foreach($this->data as $k => $v){
                if(strtolower(substr($k, 0, 7)) === 'valuer:'){
                    $vdata[substr($k, 7)] = $v;
                }
            }

            $committee_query = new ApiQuery('MapasCulturais\Entities\Agent', $vdata);

            $committee = $committee_query->find();
        } else {
            $committee = [];
        }

        return $committee;
    }

    function _getOpportunityValuerByUser($opportunity_id){
        $committee = $this->_getOpportunityCommittee($opportunity_id);

        $valuer_by_user = [];

        foreach($committee as $valuer){
            $valuer_by_user[$valuer['user']] = $valuer;
        }

        return $valuer_by_user;
    }

    function _getOpportunityRegistrations($opportunity, array $registration_ids){
        $app = App::i();

        if (empty($registration_ids)) {
            return [];
        }

        sort($registration_ids);

        $registration_ids = implode(',', $registration_ids);

        $committee = $this->_getOpportunityCommittee($opportunity->id);
        $params = [
            'opp' => $opportunity,
            'aids' => array_map(function ($el){ return $el['id']; }, $committee),
        ];
        $q = $app->em->createQuery("
            SELECT
                r.id AS registration, a.userId AS user, a.id AS valuer
            FROM
                MapasCulturais\Entities\RegistrationPermissionCache p
                JOIN p.owner r WITH r.opportunity = :opp
                JOIN p.user u
                INNER JOIN u.profile a WITH a.id IN (:aids)
            WHERE 
                p.action = 'viewPrivateData' AND
                r.id IN ({$registration_ids})

        ");

        $q->setParameters($params);

        $permissions = $q->getArrayResult();

        $registrations_by_valuer = [];

        foreach($permissions as $p){
            if(!isset($registrations_by_valuer[$p['valuer']])){
                $registrations_by_valuer[$p['valuer']] = [];
            }
            $registrations_by_valuer[$p['valuer']][$p['registration']] = true;
        }

        $registration_ids = array_map(function($r) { return $r['registration']; }, $permissions);
        $registration_idsSplittedUsingComma = implode(',', $registration_ids);
        if($registration_ids){
            $rdata = [
                '@select' => 'id,status,category,consolidatedResult,singleUrl,owner.name,previousPhaseRegistrationId',
                'id' => "IN({$registration_idsSplittedUsingComma})"
            ];

            foreach($this->data as $k => $v){
                if(strtolower(substr($k, 0, 13)) === 'registration:'){
                    $rdata[substr($k, 13)] = $v;
                }
            }

            $registrations_query = new ApiQuery('MapasCulturais\Entities\Registration', $rdata);
            $registrations = [];
            foreach($registrations_query->find() as $reg){
                $registrations[$reg['id']] = $reg;
            }

            return $registrations;
        } else {
            return [];
        }

    }

    function _getOpportunityEvaluations($opportunity, $evaluation_ids) {
        $app = App::i();

        if (empty($evaluation_ids)) {
            return [];
        }

        sort($evaluation_ids);
        $evaluation_ids = implode(',', $evaluation_ids);

        $edata = [
            '@select' => 'id,result,evaluationData,registration,user,status',
            'id' => "IN({$evaluation_ids})"
        ];

        foreach($this->data as $k => $v){
            if(strtolower(substr($k, 0, 11)) === 'evaluation:'){
                $edata[substr($k, 11)] = $v;
            }
        }

        $evaluations_query = new ApiQuery('MapasCulturais\Entities\RegistrationEvaluation', $edata);
        $evaluations = [];

        $valuer_by_user = $this->_getOpportunityValuerByUser($opportunity->id);
        foreach($evaluations_query->find() as $e){
            if(isset($valuer_by_user[$e['user']])){
                $e['agent'] = $valuer_by_user[$e['user']];
                $e['singleUrl'] = $app->createUrl('registration', 'view', [$e['registration'], 'uid' => $e['user']]);
                $e['resultString'] = $opportunity->getEvaluationMethod()->valueToString($e['result']);
                $evaluations[$e['id']] = $e;
            }
        }
        return $evaluations;

    }

    function API_findRegistrationsAndEvaluations() {
        $app = App::i();

        $opportunity = $this->_getOpportunity();
        $data = $this->data;

        $conn = $app->getEm()->getConnection();

        $resultLength = "
        SELECT
            count(r.id)
        FROM
            registration r
        INNER JOIN pcache pc
            ON pc.object_id = r.id
                AND pc.object_type = 'MapasCulturais\Entities\Registration'
                AND pc.action = 'evaluate'
                AND pc.user_id = :user_id
        WHERE r.status > 0
                AND r.opportunity_id = :opportunity_id
        ";

        $length = $conn->fetchAll($resultLength, [
            'user_id' => $app->user->id,
            'opportunity_id' => $opportunity->id,
            ]);

        if(isset($this->data['@pending'])){
            $sql = "
            SELECT
                r.id as registrationId,
                r.status as registrationStatus,
                r.consolidated_result as registrationConsolidated_result,
                r.number as registrationNumber,
                re.*,
                a.id as agentId,
                a.name as agentName
            FROM
                registration r
                INNER JOIN pcache pc ON
                    pc.object_id = r.id
                    AND pc.object_type = 'MapasCulturais\Entities\Registration'
                    AND pc.action = 'evaluate'
                    AND pc.user_id = :user_id
                LEFT JOIN registration_evaluation re ON
                    r.id = re.registration_id
                    AND re.user_id = :user_id
                INNER JOIN agent a ON 
                    a.id = r.agent_id
            WHERE
                r.status > 0
                AND r.opportunity_id = :opportunity_id
                AND r.id NOT IN (
                    SELECT registration_id 
                    FROM registration_evaluation 
                    WHERE user_id = :user_id
                )
            ORDER BY
                r.id
            LIMIT
                :limit OFFSET :offset
            ";
        }else{
            $sql = "
            SELECT
                r.id as registrationId,
                r.status as registrationStatus,
                r.consolidated_result as registrationConsolidated_result,
                r.number as registrationNumber,
                re.*,
                a.id as agentId,
                a.name as agentName
            FROM
                registration r
                INNER JOIN pcache pc ON
                    pc.object_id = r.id
                    AND pc.object_type = 'MapasCulturais\Entities\Registration'
                    AND pc.action = 'evaluate'
                    AND pc.user_id = :user_id
                LEFT JOIN registration_evaluation re ON
                    r.id = re.registration_id
                    AND re.user_id = :user_id
                INNER JOIN agent a ON 
                    a.id = r.agent_id
            WHERE
                r.status > 0
                AND r.opportunity_id = :opportunity_id
            ORDER BY
                r.id
            LIMIT
                :limit OFFSET :offset
            ";
        }

        

        $limit = isset($data['@limit']) ? $data['@limit'] : 50;
        $page = isset($data['@page'] ) ? $data['@page'] : 1;
        $offset = ($page -1) * $limit;

        $registrations = $conn->fetchAll($sql, [
            'user_id' => $app->user->id,
            'opportunity_id' => $opportunity->id,
            'limit' => $limit,
            'offset' => $offset
            ]);

        $registrationWithResultString = array_map(function($registration) use ($opportunity) {
            return [
                "registrationid" => $registration['registrationid'],
                "registrationstatus" => $registration['registrationstatus'],
                "registrationconsolidated_result" => $registration['registrationconsolidated_result'],
                "registrationnumber" => $registration['registrationnumber'],
                "id" => $registration['id'],
                "registration_id" => $registration['registration_id'],
                "user_id" => $registration['user_id'],
                "result" => $registration['result'],
                "evaluation_data" => $registration['evaluation_data'],
                "status" => $registration['status'],
                "agentid" => $registration['agentid'],
                "agentname" => $registration['agentname'],
                "resultString" => $opportunity->getEvaluationMethod()->valueToString($registration['result'])
            ];
        },$registrations);

        $this->apiAddHeaderMetadata($this->data, $registrationWithResultString, $length[0]['count']);
        $this->apiResponse($registrationWithResultString);
    }

    function API_findEvaluations($opportunity_id = null) {
        $this->requireAuthentication();

        $app = App::i();
        $conn = $app->em->getConnection();

        $opportunity = $this->_getOpportunity($opportunity_id);

        $committee = $this->_getOpportunityCommittee($opportunity_id);

        foreach($committee as $valuer){
            $valuer_by_id[$valuer['user']] = $valuer;
        }

        $users = implode(',', array_map(function ($el){ return $el['user']; }, $committee));

        if(empty($users)){
            $this->apiAddHeaderMetadata($this->data, [], 0);
            $this->apiResponse([]);
            return;
        }

        $params = ['opp' => $opportunity->id];

        $queryNumberOfResults = $conn->fetchColumn("
            SELECT count(*) 
            FROM evaluations 
            WHERE 
                opportunity_id = :opp AND
                valuer_user_id IN({$users})
        ", $params);

        $valuer_by_id = [];

        foreach($committee as $valuer){
            $valuer_by_id[$valuer['id']] = $valuer;
        }

        $sql_limit = "";
        if (isset($this->data['@limit'])) {
            $limit = intval($this->data['@limit']);

            $sql_limit = "LIMIT $limit";

            if (isset($this->data['@page'])) {
                $page = intval($this->data['@page']);
                $offset = ($page - 1) * $limit;
                $sql_limit .= " OFFSET {$offset}";
            }
        }

        $sql_status = "";
        if (isset($this->data['status'])) {
            if(preg_match('#EQ\( *(-?\d) *\)#', $this->data['status'], $matches)) {
                $status = $matches[1];
                $sql_status = " AND evaluation_status = {$status}";
            }
        }

        $rdata = [
            '@select' => 'id',
            'opportunity' => "EQ({$opportunity->id})",
            '@permissions' => 'viewPrivateData'
        ];

        foreach($this->data as $k => $v){
            if(strtolower(substr($k, 0, 13)) === 'registration:'){
                $rdata[substr($k, 13)] = $v;
            }
        }
      
        if(isset($this->data['valuer:id'])){
            if(preg_match('#EQ\( *(\d+) *\)#', $this->data['valuer:id'], $matches)) {
                $valuer_id = $matches[1];
                $valuer = $app->repo("Agent")->find($valuer_id);
                $rdata['@permissionsuser'] = $valuer->userId;
            }
        }

        $registrations_query = new ApiQuery('MapasCulturais\Entities\Registration', $rdata);

        $registration_ids = implode(",", $registrations_query->findIds() ?: [-1]);

        $evaluations = $conn->fetchAll("
            SELECT 
                registration_id, 
                evaluation_id, 
                valuer_agent_id
            FROM evaluations
            WHERE
                opportunity_id = :opp AND
                valuer_user_id IN({$users}) AND
                registration_id IN ({$registration_ids})
                $sql_status
            ORDER BY registration_sent_timestamp ASC
            $sql_limit
        ", $params);
        
        
        $registration_ids = array_filter(array_unique(array_map(function($r) { return $r['registration_id']; }, $evaluations)));
        $evaluations_ids = array_filter(array_unique(array_map(function($r) { return $r['evaluation_id']; }, $evaluations)));

        $_registrations = $this->_getOpportunityRegistrations($opportunity, $registration_ids);
        $_evaluations = $this->_getOpportunityEvaluations($opportunity, $evaluations_ids);

        $_result = [];

        foreach($evaluations as $eval) {
            $_result[] = [
                'registration_id' => $eval['registration_id'],
                'evaluation' => $_evaluations[$eval['evaluation_id']] ?? null,
                'registration' => $_registrations[$eval['registration_id']] ?? null,
                'valuer' => $valuer_by_id[$eval['valuer_agent_id']] ?? null
            ];
        }

        if(!$opportunity->canUser("@control")){
            $avaliableEvaluationFields = (!empty($opportunity->avaliableEvaluationFields) || $opportunity->avaliableEvaluationFields != "") ? $opportunity->avaliableEvaluationFields : [];
            foreach($_result as $key => $res){
                if(!in_array("agentsSummary", array_keys($avaliableEvaluationFields))){
                    $_result[$key]['registration']['owner'] =  [];
                }
            }
        }

        if (!is_null($opportunity_id) && is_int($opportunity_id)) {
            return $_result;
        }

        $this->apiAddHeaderMetadata($this->data, $_result, $queryNumberOfResults);
        $this->apiResponse($_result);
    }

    function ALL_reconsolidateResults() {
        $this->requireAuthentication();

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $opportunity = $this->requestedEntity;

        $opportunity->checkPermission('@control');

        $app = App::i();

        $conn = $app->em->getConnection();

        $conn->executeQuery("
            UPDATE 
                registration
            SET
                consolidated_result = '0'
            WHERE
                opportunity_id = :opp AND
                id IN (
                    SELECT registration_id 
                    FROM registration_evaluation
                )
        ", ['opp' => $opportunity->id]);

        $evaluations = $conn->fetchAll("
            SELECT re.id, r.number 
            FROM registration_evaluation re, registration r
            WHERE
                r.id = re.registration_id AND
                re.registration_id IN (
                    SELECT id FROM registration WHERE opportunity_id = :opp
                )
            ORDER BY re.id ASC
        ", ['opp' => $opportunity->id]);

        $repo = $app->repo('RegistrationEvaluation');
        $c = 0;
        $num = count($evaluations);

        $app->applyHookBoundTo($this, 'controller(opportunity).reconsolidateResult', [$opportunity, &$evaluations]);

        foreach ($evaluations as $ev) {
            $c++;
            $ev = (object) $ev;
            $eval = $repo->find($ev->id);
            $app->log->debug("({$c}/{$num}) reconsolidando avaliação da inscrição {$ev->number} (ID: {$ev->id})");
            $eval->setEvaluationData($eval->getEvaluationData());
            $eval->registration->__skipQueuingPCacheRecreation = true;
            $eval->save(true);

            $app->em->clear();
        }

        $url = $app->createUrl('oportunidade', $opportunity->id);
        $app->redirect($url);
    }

    function GET_exportFields() {
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }

        $fields = $app->repo("RegistrationFieldConfiguration")->findBy(array('owner' => $this->urlData['id']));
        $files = $app->repo("RegistrationFileConfiguration")->findBy(array('owner' => $this->urlData['id']));

        $opportunity =  $app->repo("Opportunity")->find($this->urlData['id']);

        if (!$opportunity->canUser('modify'))
            return false; //TODO return error message?

        $opportunityMeta = array(
            'registrationCategories',
            'useAgentRelationColetivo',
            'registrationLimitPerOwner',
            'registrationCategDescription',
            'registrationCategTitle',
            'useAgentRelationInstituicao',
            'introInscricoes',
            'useSpaceRelationIntituicao',
            'registrationSeals',
            'registrationLimit'
        );

        $metadata = [];

        foreach ($opportunityMeta as $key) {
            $metadata[$key] = $opportunity->{$key};
        }

        $result = array(
            'files' => $files,
            'fields' => $fields,
            'meta' => $metadata
        );

        header('Content-disposition: attachment; filename=opportunity-'.$this->urlData['id'].'-fields.txt');
        header('Content-type: text/plain');
        echo json_encode($result);
    }

    function POST_importFields() {
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }

        $opportunity_id = $this->urlData['id'];

        if (isset($_FILES['fieldsFile']) && isset($_FILES['fieldsFile']['tmp_name']) && is_readable($_FILES['fieldsFile']['tmp_name'])) {

            $importFile = fopen($_FILES['fieldsFile']['tmp_name'], "r");
            $importSource = fread($importFile,filesize($_FILES['fieldsFile']['tmp_name']));
            $importSource = json_decode($importSource);

            $opportunity =  $app->repo("Opportunity")->find($opportunity_id);

            $opportunity->importFields($importSource);

        }

        $app->redirect($opportunity->editUrl.'#tab=inscricoes');

    }

    function POST_saveFieldsOrder() {

        $this->requireAuthentication();

        $app = App::i();

        $owner = $this->requestedEntity;

        if(!$owner){
            $app->pass();
        }

        $owner->checkPermission('modify');

        $savedFields = array();

        $savedFields['fields'] = $owner->registrationFieldConfigurations;
        $savedFields['files'] = $owner->registrationFileConfigurations;

        if (!is_array($this->postData['fields'])){
            return false;
        }

        foreach ($this->postData['fields'] as $field) {

            $type = $field['fieldType'] == 'file' ? 'files' : 'fields';

            foreach ($savedFields[$type] as $savedField) {

                if ($field['id'] == $savedField->id) {

                    $savedField->displayOrder = (int) $field['displayOrder'];
                    $savedField->save(true);

                    break;

                }

            }

        }

    }
}