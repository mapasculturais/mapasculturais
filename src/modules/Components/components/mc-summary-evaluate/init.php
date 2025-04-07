<?php

$requestedEntity = $this->controller->requestedEntity;

$data['isActive'] = false;

if($requestedEntity instanceof MapasCulturais\Entities\EvaluationMethodConfiguration){
    $evaluation_method_configuration = $requestedEntity;
}else if($requestedEntity instanceof MapasCulturais\Entities\Opportunity){
    $evaluation_method_configuration = $requestedEntity->evaluationMethodConfiguration;
}else if($requestedEntity instanceof MapasCulturais\Entities\Registration){
    $evaluation_method_configuration = $requestedEntity->opportunity->evaluationMethodConfiguration;
}

if($this->controller->action == 'allEvaluations') {
    $data = $evaluation_method_configuration->getValuerSummary();
} else {
    $user = isset($this->controller->data['user']) ? $app->repo("User")->find($this->controller->data['user']) : $app->user;
    $data = $evaluation_method_configuration->getValuerSummary($user);
}
    
$data['isActive'] =  true;  
$this->jsObject['config']['summaryEvaluations'] = $data;