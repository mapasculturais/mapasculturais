<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
$entity = $this->controller->requestedEntity;

$cookie_key = "evaluation-status-filter-{$entity->opportunity->id}";
$evaluationStatusFilterCache = null;
if (isset($_SESSION[$cookie_key])) {
    $this->jsObject['config']['opportunityEvaluationsList']['evaluationStatusFilterCache'] = $_SESSION[$cookie_key];
}
