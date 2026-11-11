<?php

use MapasCulturais\App;
use MapasCulturais\i;

$app = App::i();

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$definitions = MapasCulturais\Entities\Event::getPropertiesMetadata();
$skipFields = ['subsite', 'occurrences', 'project'];
$additionalHeaders = [];
$visibleColumns = [];
foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_")) {
        $data = [
            'text' => $def['label'] ?? $field,
            'value' => $field,
            'slug' => $field
        ];

        if ($field == "owner") {
            $data['text'] = i::__('Responsável', 'event-table');
            $data['value'] = 'owner?.name';
        }

        $additionalHeaders[] = $data;
        $visibleColumns[] = $field;
    }
}

$app->applyHook('component(event-table).additionalHeaders', [$visibleColumns, &$additionalHeaders]);

$this->jsObject['config']['eventTable'] = [
    'additionalHeaders' => $additionalHeaders,
    'seals' => $querySeals->getFindResult(),
    'visibleColumns' => $visibleColumns
];
