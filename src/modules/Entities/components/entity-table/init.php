<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$entities_status = ['Opportunity', 'Agent', 'Space', 'Event', 'Project', 'Seal'];
$from_toStatus = [];
foreach($entities_status as $entity) {
    $class = "MapasCulturais\\Entities\\{$entity}";
    $from_toStatus[$entity] = $class::getStatusesNames(); 
}

$sort_options = [
    [ 'value' => 'createTimestamp DESC',  'label' => i::__('mais recentes primeiro') ],
    [ 'value' => 'createTimestamp ASC',   'label' => i::__('mais antigas primeiro') ],
    [ 'value' => 'updateTimestamp DESC',  'label' => i::__('modificadas recentemente') ],
    [ 'value' => 'updateTimestamp ASC',   'label' => i::__('modificadas há mais tempo') ],
];

$this->applyTemplateHook('entityTableSortOptions', args: [&$sort_options]);

$loadColumnsConfigDir = static function (string $dir, array &$tables): void {
    if (!is_dir($dir)) {
        return;
    }

    $files = glob(rtrim($dir, '/') . '/*.json') ?: [];
    foreach ($files as $file) {
        $tableKey = basename($file, '.json');
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $tableKey)) {
            continue;
        }
        $decoded = json_decode((string) file_get_contents($file), true);
        if (!is_array($decoded)) {
            continue;
        }
        $tables[$tableKey] = [
            'order' => $decoded['order'] ?? [],
            'visible' => $decoded['visible'] ?? [],
            'updatedAt' => $decoded['updatedAt'] ?? null,
            'updatedBy' => $decoded['updatedBy'] ?? null,
        ];
    }
};

$columnConfig = ['version' => 1, 'tables' => []];
// 1) defaults versionados no módulo (ex.: padrão inicial da tabela de inscrições)
$loadColumnsConfigDir(__DIR__ . '/../../defaults/entity-table-columns', $columnConfig['tables']);
// 2) overrides em runtime (por oportunidade / salvos por @control ou saasSuperAdmin)
$loadColumnsConfigDir(BASE_PATH . 'entity-table-columns', $columnConfig['tables']);

$this->jsObject['config']['entityTable'] =[
    'sortOptions' => $sort_options,
    'seals' => $querySeals->getFindResult(),
    'fromToStatus' => $from_toStatus,
    'columnsConfig' => $columnConfig,
    'canManageColumnsGlobal' => $app->user && $app->user->is('saasSuperAdmin'),
];
