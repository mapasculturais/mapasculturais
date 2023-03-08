<?php
$getFieldsAllPhases = function () use ($entity) {
    $previous_phases = $entity->previousPhases;

    if ($entity->firstPhase->id != $entity->id) {
        $previous_phases[] = $entity;
    }

    $fieldsPhases = [];
    foreach ($previous_phases as $phase) {
        foreach ($phase->registrationFieldConfigurations as $field) {
            $fieldsPhases[] = $field;
        }

        foreach ($phase->registrationFileConfigurations as $file) {
            $fieldsPhases[] = $file;
        }
    }

    return $fieldsPhases;
};

$evaluationMethodConfiguration = $entity->evaluationMethodConfiguration;

$app->view->jsObject['affirmativePoliciesFieldsList'] = $getFieldsAllPhases();
$app->view->jsObject['isActiveAffirmativePolicies'] = $evaluationMethodConfiguration->isActiveAffirmativePolicies;
$app->view->jsObject['affirmativePolicies'] = $evaluationMethodConfiguration->affirmativePolicies;
$app->view->jsObject['affirmativePoliciesRoof'] = $evaluationMethodConfiguration->affirmativePoliciesRoof;
?>

<div id="evaluations-config" class="aba-content ng-scope" ng-controller="EvaluationMethodConfigurationController" style="display: block;">
    <div ng-controller="TechnicalEvaluationMethodConfigurationController" class="technical-evaluation-configuration registration-fieldset">
        <?php $this->part('technical--affirmative-polices-configuration') ?>
    </div>
</div>