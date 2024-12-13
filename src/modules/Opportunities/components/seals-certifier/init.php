<?php

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar,singleUrl',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$this->jsObject['config']['sealsCertifier'] =[
    'seals' => $querySeals->getFindResult(),
];