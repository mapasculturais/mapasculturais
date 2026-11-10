<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$seals_filter_enabled = $app->config['events.filter.seals'] ?? false;

// O recorte territorial das buscas é controlado pelo componente <search>
// (ADR 0019): flags unificadas $MAPAS.config.statesAndCitiesEnable e
// $MAPAS.config.searchTerritorialFilters, publicadas em search/init.php.
// A variável antiga events.filter.statesAndCities não tem mais efeito (#118).

$seals = [];
if ($seals_filter_enabled) {
    $query = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, [
        '@select' => 'id,name',
        '@order'  => 'name ASC',
    ]);
    $seals = $query->getFindResult();
}

$this->jsObject['config']['searchFilterEvent'] = [
    'sealsFilterEnabled' => $seals_filter_enabled,
    'seals'              => $seals,
];
