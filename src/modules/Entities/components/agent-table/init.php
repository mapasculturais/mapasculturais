<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$file_name = $app->config['statesAndCities.file'];
$content = $this->resolveFilename('states-and-cities', $file_name);
include $content;

$app->view->jsObject['config']['statesAndCities'] = $data;
$app->view->jsObject['config']['statesAndCitiesEnable'] = $app->config['statesAndCities.enable'];

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];

$app->applyHook('component(agent-table).querySeals', [&$queryParams]);

$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$additionalHeaders = [];
$skipFields = ['parent', 'user', 'subsite', 'id', 'name', 'area', 'tag', 'seals', 'type', 'geoEstado_cod', 'geoMesorregiao_cod', 'geoMicrorregiao_cod', 'geoMunicipio_cod', 'geoPais_cod'];

$default_select = "name,type,shortDescription,files.avatar,seals,endereco,terms,orientacaoSexual,genero,raca";

$defaultHeaders = [
    [
        'text' => i::__('id', 'agent-table'),
        'value' => 'id',
        'sticky' => true,
        'width' => '80px',
    ],
    [
        'text' => i::__('Nome', 'agent-table'),
        'value' => 'name',
        'width' => '160px',
    ],
    [
        'text' => i::__('Área de atuação', 'agent-table'),
        'value' => 'terms?.area?.join(\', \')',
        'slug' => 'area',
    ],
    [
        'text' => i::__('Tags', 'agent-table'),
        'value' => 'terms?.tag?.join(\', \')',
        'slug' => 'tag',
    ],
    [
        'text' => i::__('Segmento', 'agent-table'),
        'value' => 'terms?.segmento?.join(\', \')',
        'slug' => 'segmento',
    ],
    [
        'text' => i::__('Função', 'agent-table'),
        'value' => 'terms?.funcao?.join(\', \')',
        'slug' => 'funcao',
    ],
    [
        'text' => i::__('Selos', 'agent-table'),
        'value' => 'seals.map((seal) => seal.name).join(\', \')',
        'slug' => 'seals',
    ],
    [
        'text' => i::__('Endereço', 'agent-table'),
        'value' => 'endereco',
        'slug' => 'endereco',
    ],
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
            $data['value'] = $def['label'] . " - Divisão geográfica";
        }

        $additionalHeaders[] = $data;
    }
}

$app->applyHook('component(agent-table).additionalHeaders', [&$defaultHeaders, &$additionalHeaders, &$default_select]);

$this->jsObject['config']['agentTable'] = [
    'seals' => $querySeals->getFindResult(),
    'additionalHeaders' => $additionalHeaders,
    'defaultHeaders' => $defaultHeaders,
    'default_select' => $default_select
];
