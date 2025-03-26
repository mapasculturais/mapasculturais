<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

$committee = [];
$valuersMetadata = [];

if ($comm = $entity->getEvaluationCommittee()) {
    foreach($comm as $value) {
        array_push($committee, [
            "value" => $value->agent->owner->user->id,
            "label" => $value->agent->name,
        ]);

        $valuersMetadata[$value->agent->owner->user->id] = $value->metadata;
    }
}

usort($committee, fn($a, $b) => $a['label'] <=> $b['label']);

array_unshift($committee, [
    "value" => 'all',
    "label" => i::__('Todos')
]);

$this->jsObject['config']['opportunityEvaluationsTable'] = [
    "isAdmin" => $app->user->is("admin"),
    "committee" => $committee,
    "valuersMetadata" => $valuersMetadata
];