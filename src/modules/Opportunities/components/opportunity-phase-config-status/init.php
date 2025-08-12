<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */


$opportunity = $this->controller->requestedEntity;
$default_statuses = $opportunity->evaluationMethodConfiguration->defaultStatuses ?: $opportunity->defaultStatuses;
$missing_labels = array_diff(array_values($default_statuses), array_values($opportunity->statusLabels ?: []) );

if($opportunity && (!$opportunity->statusLabels || $missing_labels)) {
    $opportunity->statusLabels = $default_statuses;

    $app->disableAccessControl();
    $opportunity->save();
    $app->enableAccessControl();
}

$this->jsObject['config']['opportunityPhaseConfigStatus'] = [
    'statuses' => $opportunity->defaultStatuses,
];