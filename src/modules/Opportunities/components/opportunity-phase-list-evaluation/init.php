<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$opportunity = $this->controller->requestedEntity;
$evaluation_configuration = $opportunity->evaluationMethodConfiguration;

$status_names = $opportunity->statusLabels ?: $evaluation_configuration->defaultStatuses;

$this->jsObject['config']['opportunityPhaseListEvaluation'] = [
    'statusNames' => $status_names,
];