<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

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

$skipFields = ["previousPhaseRegistrationId", "nextPhaseRegistrationId", "id"];
$default_headers = [];

// Carrega metadados
$definitions = Registration::getPropertiesMetadata();
$can_see = function ($def) use ($app) {
    return $app->user->is('admin') ? true : !(isset($def['private']) && $def['private']);
};

foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_") && $can_see($def) && $def['label']) {
        $header = [
            'text' => str_starts_with($field, 'geo') ? $def['label'] . " - Divisão geográfica" : $def['label'],
            'value' => $field,
            'slug' => $field
        ];
        $default_headers[] = $header;
    }
}

// Função para separar campos no select
function splitSelectFields($str) {
    $len = strlen($str);
    $fields = [];
    $current = '';
    $depth = 0;
    for ($i = 0; $i < $len; $i++) {
        $char = $str[$i];
        if ($char === ',' && $depth === 0) {
            $fields[] = trim($current);
            $current = '';
        } else {
            if ($char === '{') $depth++;
            elseif ($char === '}') $depth--;
            $current .= $char;
        }
    }
    if ($current !== '') $fields[] = trim($current);
    return $fields;
}

// Processa campo para parent/children
function parseField($field) {
    $parent = $field;
    $children = [];
    if (strpos($field, '.{') !== false) {
        [$parentPart, $childrenPart] = explode('.{', $field, 2);
        $childrenPart = rtrim($childrenPart, '}');
        $parent = rtrim($parentPart, '?');
        $children = array_map('trim', explode(',', $childrenPart));
    } elseif (strpos($field, '.') !== false) {
        [$parentPart, $child] = explode('.', $field, 2);
        $parent = rtrim($parentPart, '?');
        $children = [trim($child)];
    } else {
        $parent = rtrim($field, '?');
    }
    return ['parent' => $parent, 'children' => $children];
}

// Processa o default_select original
$default_select = 'agentsData';
$original_fields = splitSelectFields($default_select);
$merged = [];
$parentOrder = [];
foreach ($original_fields as $field) {
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];
    if (!isset($merged[$parent])) {
        $merged[$parent] = [];
        $parentOrder[] = $parent;
    }
    $merged[$parent] = array_unique(array_merge($merged[$parent], $children));
}

// Processa headers para adicionar ao select
foreach ($default_headers as $header) {
    $field = $header['slug'] ?? $header['value'];
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];
    if (!isset($merged[$parent])) {
        $merged[$parent] = [];
        $parentOrder[] = $parent;
    }
    $merged[$parent] = array_unique(array_merge($merged[$parent], $children));
}

// Reconstrói o select final
$final_select = [];
foreach ($parentOrder as $parent) {
    $children = $merged[$parent];
    if ($children) {
        $final_select[] = $parent . '.{' . implode(',', $children) . '}';
    } else {
        $final_select[] = $parent;
    }
}
$default_select = implode(',', $final_select);

$this->jsObject['config']['opportunityEvaluationsTable'] = [
    "isAdmin" => $app->user->is("admin"),
    "committee" => $committee,
    "valuersMetadata" => $valuersMetadata,
    'defaultHeaders' => $default_headers,
    'defaultSelect' => $default_select,
];
