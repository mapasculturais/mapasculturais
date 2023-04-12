<?php

$entity = $this->controller->requestedEntity;

$data = [];

if($entity instanceof MapasCulturais\Entities\EvaluationMethodConfiguration){
    $conn = $app->em->getConnection();

    $complement = "";

    if(!$app->user->is('admin')){
        $agent = $app->user->profile;
        $complement.= " AND valuer_agent_id = {$agent->id}";
    }
    
    $buildQuery = function($colluns = "*", $params = "", $type = "fetchAll") use ($conn, $entity){
        return $conn->$type("SELECT {$colluns} FROM evaluations e WHERE opportunity_id = {$entity->id} {$params}");
    };
    
    $pending = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status IS NULL {$complement}", "fetchAssoc");
    $data['pending'] = $pending['qtd'];
    
    $completed = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status = 1 {$complement}", "fetchAssoc");
    $data['completed'] = $completed['qtd'];
    
    $send = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status = 2 {$complement}", "fetchAssoc");
    $data['send'] = $send['qtd'];
}

$this->jsObject['config']['summaryEvaluate'] = $data;

