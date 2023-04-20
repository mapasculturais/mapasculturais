<?php

$requestedEntity = $this->controller->requestedEntity;

$data['isActive'] = false;

if($requestedEntity instanceof MapasCulturais\Entities\EvaluationMethodConfiguration){
    $entity = $requestedEntity;
}else if($requestedEntity instanceof MapasCulturais\Entities\Opportunity){
    $entity = $requestedEntity->opportunity->EvaluationMethodConfiguration;
}else if($requestedEntity instanceof MapasCulturais\Entities\Registration){
    $entity = $requestedEntity->EvaluationMethodConfiguration;
}

    $conn = $app->em->getConnection();

    $complement = function($collun) use ($app){
        $str = "";
        if(!$app->user->is('admin')){
            $user = $app->user;
            $str.= " AND e.{$collun} = {$user->id}";
        }
        return $str;
    };

    $buildQuery = function($colluns = "*", $params = "", $type = "fetchAll") use ($conn, $entity){
        return $conn->$type("SELECT {$colluns} FROM evaluations e WHERE opportunity_id = {$entity->opportunity->id} {$params}");
    };

    $pending = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status IS NULL {$complement('valuer_user_id')}", "fetchAssoc");
    $data['pending'] = $pending['qtd'];
    
    $completed = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status = 1 {$complement('valuer_user_id')}", "fetchAssoc");
    $data['completed'] = $completed['qtd'];
    
    $send = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status = 2 {$complement('valuer_user_id')}", "fetchAssoc");
    $data['send'] = $send['qtd'];
  
    $started = $conn->fetchAssoc("SELECT DISTINCT count(e.registration_id) as qtd FROM registration_evaluation e WHERE e.status = 0 and e.registration_id IN (select r.id from registration r where r.opportunity_id = {$entity->opportunity->id}) {$complement('user_id')}");
    $data['started'] = $started['qtd'];
    $data['isActive'] =  true;

$this->jsObject['config']['summaryEvaluate'] = $data;

