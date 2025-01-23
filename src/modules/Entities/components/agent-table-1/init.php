<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$additionalHeaders = [];
$skipFieldsAdditionalHeaders = [
    'id',
    'area',
    'documento',
    'id',
    'name',
    'seals',
    'tag',
    'type',
    'user',
    'userId',
    'parent',
    'subsite',
];

$can_see = function ($def) use ($app) {
    if ($app->user->is('admin')) {
        return true;
    }

    if (isset($def['private']) && $def['private']) {
        return false;
    }
};

foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFieldsAdditionalHeaders) && !str_starts_with($field, "_") && $can_see($def)) {
        $data = [
            'text' => $def['label'],
            'value' => $field,
            'slug' => $field
        ];
        $additionalHeaders[] = $data;
    }
}

$app->applyHook('component(agent-table-1).additionalHeaders', [&$additionalHeaders]);

$this->jsObject['config']['agentTable1'] = [
    'additionalHeaders' => $additionalHeaders,
];
