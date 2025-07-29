<?php

$data['isActive'] = false;

if($entity instanceof MapasCulturais\Entities\EvaluationMethodConfiguration){
    $evaluation_method_configuration = $entity;
}else if($entity instanceof MapasCulturais\Entities\Opportunity){
    $evaluation_method_configuration = $entity->evaluationMethodConfiguration;
}else if($entity instanceof MapasCulturais\Entities\Registration){
    $evaluation_method_configuration = $entity->opportunity->evaluationMethodConfiguration;
}

if($this->controller->action == 'allEvaluations') {
    $data = $evaluation_method_configuration->getValuerSummary();
} else {
    $user = isset($this->controller->data['user']) ? $app->repo("User")->find($this->controller->data['user']) : $app->user;
    $data = $evaluation_method_configuration->getValuerSummary($user);
}
    
$data['isActive'] =  true;  
$this->jsObject['config']['summaryEvaluations'] = $data;