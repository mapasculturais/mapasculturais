<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */


$opportunity = $this->controller->requestedEntity;
$evaluation_method_configuration = $opportunity->evaluationMethodConfiguration;

if ($evaluation_method_configuration && !$evaluation_method_configuration->statusLabels) {
    $evaluation_method_configuration->statusLabels = $evaluation_method_configuration->defaultStatuses;

    $app->disableAccessControl();
    $evaluation_method_configuration->save();
    $app->enableAccessControl();
}

$this->jsObject['config']['opportunityPhaseConfigStatus'] = [
    'statuses' => $evaluation_method_configuration->defaultStatuses,
];