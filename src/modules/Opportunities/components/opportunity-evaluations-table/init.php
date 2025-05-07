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
    foreach($comm as $member) {
        $user_id = $member->agent->owner->user->id;
        if (empty($committee[$user_id])) {
            $committee[$user_id] = [
                "value" => $user_id,
                "label" => $member->agent->name,
            ];
            $valuersMetadata[$user_id] = $member->metadata;
        }
    }
    $committee = array_values($committee);
}

usort($committee, fn($a, $b) => strtolower($a['label']) <=> strtolower($b['label']));

array_unshift($committee, [
    "value" => 'all',
    "label" => i::__('Todos')
]);

$this->jsObject['config']['opportunityEvaluationsTable'] = [
    "isAdmin" => $app->user->is("admin"),
    "committee" => $committee,
    "valuersMetadata" => $valuersMetadata
];
