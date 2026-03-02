<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$states_cities_filter_enabled = $app->config['events.filter.statesAndCities'] ?? false;
$seals_filter_enabled = $app->config['events.filter.seals'] ?? false;

// Carrega os dados de estado/cidade quando o filtro está habilitado e estamos numa instalação BR
if ($states_cities_filter_enabled && ($app->config['statesAndCities.enable'] ?? false)) {
    if (empty($app->view->jsObject['config']['statesAndCities'])) {
        $file_name = $app->config['statesAndCities.file'];
        $content = $this->resolveFilename('states-and-cities', $file_name);
        include $content;
        $app->view->jsObject['config']['statesAndCities'] = $data;
        $app->view->jsObject['config']['statesAndCitiesEnable'] = true;
        $app->view->jsObject['config']['statesAndCitiesCountryCode'] = $app->config['statesAndCities.countryCode'];
    }
}

$seals = [];
if ($seals_filter_enabled) {
    $query = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, [
        '@select' => 'id,name',
        '@order'  => 'name ASC',
    ]);
    $seals = $query->getFindResult();
}

$this->jsObject['config']['searchFilterEvent'] = [
    'statesAndCitiesFilterEnabled' => $states_cities_filter_enabled,
    'sealsFilterEnabled'           => $seals_filter_enabled,
    'seals'                        => $seals,
];
