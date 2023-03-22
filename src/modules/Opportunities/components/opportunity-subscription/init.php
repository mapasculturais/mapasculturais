<?php
$q = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Agent::class, [
    '@select' => 'id,name,files.avatar', 
    '@permissions' => '@control', 
    '@limit' => '2',
    'type' => 'EQ(1)',
]);

$result = $q->getFindResult();

$this->jsObject['config']['opportunitySubscription']['agents'] = $result;