
<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\ApiOutputs\Dump;

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections;
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria;
$enabledViability = $entity->opportunity->evaluationMethodConfiguration->enableViability;

$data = [];

foreach ($sections as $section) {
    $sid = $section->id;
    $data[$sid]['name'] = $section->name;

    foreach ($criteria as $crit) {
        if ($sid === $crit->sid) {
            $data[$sid]['criteria'][] = $crit;
        }
    }
}

$this->jsObject['config']['technicalEvaluationForm'] = [
    "section" => $data,
    "enableViability" => $enabledViability === "true" ? true : false ,
];

?>
