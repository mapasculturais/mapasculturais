<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$seals_filter_enabled = $app->config['events.filter.seals'] ?? false;

$seals = [];
if ($seals_filter_enabled) {
    $query = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, [
        '@select' => 'id,name',
        '@order'  => 'name ASC',
    ]);
    $seals = $query->getFindResult();
}

$this->jsObject['config']['searchFilterEvent'] = [
    'statesAndCitiesFilterEnabled' => $app->config['events.filter.statesAndCities'] ?? false,
    'sealsFilterEnabled'           => $seals_filter_enabled,
    'seals'                        => $seals,
];
