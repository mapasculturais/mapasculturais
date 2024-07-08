<?php

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$this->jsObject['config']['opportunityTable'] =[
    'seals' => $querySeals->getFindResult(),
];