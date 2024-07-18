<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections;
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria;

$data = [];

foreach ($sections as $section) {
    $sectionData = [
        'id' => $section->id,
        'name' => $section->name,
        'criteria' => []
    ];

    foreach ($criteria as $crit) {
        if ($crit->sid === $section->id) {
            $sectionData['criteria'][] = [
                'id' => $crit->id,
                'sid' => $crit->sid,
                'name' => $crit->name,
                'description' => $crit->description,
                'options' => $crit->options,
            ];
        }
    }

    $data[] = $sectionData;
}

$this->jsObject['config']['qualificationEvaluationForm'] = ['sections' => $data];