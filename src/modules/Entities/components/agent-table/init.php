<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$file_name = $app->config['statesAndCities.file'];
$content = $this->resolveFilename('states-and-cities',$file_name);
include $content;

$app->view->jsObject['config']['statesAndCities'] = $data;
$app->view->jsObject['config']['statesAndCitiesEnable'] = $app->config['statesAndCities.enable'];

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$this->jsObject['config']['agentTable'] =[
    'seals' => $querySeals->getFindResult(),
];