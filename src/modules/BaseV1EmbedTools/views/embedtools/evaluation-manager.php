<?php
$this->jsObject['isEditable'] = true;

if ($evaluation_method->slug == "technical") {

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

    $app->view->jsObject['pointsByInductionFieldsList'] = $getFieldsAllPhases();
    $app->view->jsObject['isActivePointReward'] = $evaluationMethodConfiguration->isActivePointReward;
    $app->view->jsObject['pointReward'] = $evaluationMethodConfiguration->pointReward;
    $app->view->jsObject['pointRewardRoof'] = $evaluationMethodConfiguration->pointRewardRoof;
}

$configuration = $entity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$propertiesMetadata = $configuration->getPropertiesMetadata();
$this->jsObject['entity']['definition']['evaluationFrom']  = $propertiesMetadata['evaluationFrom'];
$this->jsObject['entity']['definition']['evaluationTo']  = $propertiesMetadata['evaluationTo'];

?>
<div id="evaluations-config" class="aba-content" ng-controller="EvaluationMethodConfigurationController">
<?php
if (is_object($definition) && property_exists($definition, 'evaluationMethod')) :
    $evaluationMethod = $definition->evaluationMethod;
    $config_form_part_name = $evaluationMethod->getConfigurationFormPartName();
?>
    <?php if ($config_form_part_name) : ?>
        <div> <?php $this->part($config_form_part_name, ['entity' => $entity]) ?> </div>
    <?php endif; ?>
<?php else : ?>
    <?php i::_e('As inscrições para esta oportunidade já foram encerradas. Não é mais possível configurar a avaliação.'); ?>
<?php endif; ?>
</div>