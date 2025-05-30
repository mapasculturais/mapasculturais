<?php

use MapasCulturais\App;

use MapasCulturais\i;

$app = App::i();

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$definitions = MapasCulturais\Entities\Space::getPropertiesMetadata();
$skipFields = ['subsite', 'eventOccurrences', 'parent'. 'owner'];
$visibleColumns = [];
foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_")) {
        $data = [
            'text' => $def['label'],
            'value' => $field,
            'slug' => $field
        ];

        if($field == "owner") {
            $data['text'] = i::__('Responsável', 'space-table');
            $data['value'] = 'owner?.name';
        }

        if($field == "location") {
            $data['text'] = i::__('Localização', 'space-table');
        }

        if($field == "public") {
            $data['text'] = i::__('Endereço público', 'space-table');
        }

        $additionalHeaders[] = $data;
        $visibleColumns[] = $field;
    }
}

$app->applyHook('component(space-table).additionalHeaders', [$visibleColumns, &$additionalHeaders]);

$this->jsObject['config']['spaceTable'] = [
    'additionalHeaders' => $additionalHeaders,
    'seals' => $querySeals->getFindResult(),
    'visibleColumns' => $visibleColumns
];