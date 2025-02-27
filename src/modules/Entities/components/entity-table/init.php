<?php

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

$this->jsObject['config']['entityTable'] =[
    'seals' => $querySeals->getFindResult(),
    'fromToStatus' => $from_toStatus
];