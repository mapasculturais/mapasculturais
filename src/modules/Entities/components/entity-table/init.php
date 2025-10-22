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

$this->jsObject['config']['entityTable'] =[
    'sortOptions' => $sort_options,
    'seals' => $querySeals->getFindResult(),
    'fromToStatus' => $from_toStatus
];