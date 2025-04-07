<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$additionalHeaders = [];
$skipFields = [
    'area',
    'comunidadesTradicional',
    'cpf',
    'documento',
    'escolaridade',
    'genero',
    'id',
    'idoso',
    'name',
    'nomeSocial',
    'orientacaoSexual',
    'parent',
    'pessoaDeficiente',
    'raca',
    'renda',
    'seals',
    'subsite',
    'tag',
    'type',
    'user',
    'geoEstado_cod',
    'geoMesorregiao_cod',
    'geoMicrorregiao_cod',
    'geoMunicipio_cod',
    'geoPais_cod'
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
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_") && $can_see($def)) {
        $data = [
            'text' => $def['label'],
            'value' => $field,
            'slug' => $field
        ];

        if(str_starts_with($field, 'geo')) {
            $data['text'] = $def['label'] . " - Divisão geográfica";
        }

        $additionalHeaders[] = $data;
    }
}

$app->applyHook('component(agent-table-2).additionalHeaders', [&$additionalHeaders]);

$this->jsObject['config']['agentTable2'] = [
    'additionalHeaders' => $additionalHeaders
];
