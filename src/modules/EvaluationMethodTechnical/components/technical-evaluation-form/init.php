
<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\ApiOutputs\Dump;

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections ?? [];
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria ?? [];
$enabledViability = $entity->opportunity->evaluationMethodConfiguration->enableViability;

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

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
    'currentEvaluation' => $entity->getUserEvaluation($user),
    "sections" => $data,
    "enableViability" => $enabledViability === "true" ? true : false ,
];

?>
