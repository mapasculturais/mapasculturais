<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);


$avaliable_evaluationFields = $entity->opportunity->avaliableEvaluationFields ?? [];
$app->view->jsObject['avaliableEvaluationFields'] = $avaliable_evaluationFields;


$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>
<?php 
if($entity->opportunity->evaluationMethodConfiguration){
    if($entity->opportunity->evaluationMethodConfiguration->committee){
        $this->part('singles/registration--valuers-list', $_params); 
    }
}
?>
