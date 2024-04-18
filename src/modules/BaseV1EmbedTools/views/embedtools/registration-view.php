<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$avaliable_evaluationFields = $entity->opportunity->avaliableEvaluationFields ?? [];
$avaliable_evaluationFields['proponentType'] = true;
$avaliable_evaluationFields['range'] = true;
$avaliable_evaluationFields['category'] = true;

$app->view->jsObject['avaliableEvaluationFields'] = $avaliable_evaluationFields;

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>

<?php $this->part('singles/registration-single--fields', $_params) ?>