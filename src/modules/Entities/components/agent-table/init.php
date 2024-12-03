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

$geoDivisionsHierarchy = [];
if($result = $app->config['app.geoDivisionsHierarchy']){
    foreach ($result as $key => $values) {
        $field = 'geo'.ucfirst($key);

        $_data = [
            'name' => $values['name'],
            'field' => $field,
        ];

        $geoDivisionsHierarchy[] = $_data;
    }
}

$this->jsObject['config']['agentTable'] =[
    'geoDivisionsHierarchy' => $geoDivisionsHierarchy,
    'seals' => $querySeals->getFindResult(),
];