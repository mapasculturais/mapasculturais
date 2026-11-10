<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use Search\Module as SearchModule;

if (isset($this->data['initial_pseudo_query'])) {
    $this->jsObject['initialPseudoQuery'] = $this->data['initial_pseudo_query'];
}

// Recorte territorial das buscas (ADR 0019 — docs/reference/decisions/0019)
$states_and_cities_enabled = ($app->config['statesAndCities.enable'] ?? false)
    && ($app->config['statesAndCities.countryCode'] ?? '') === 'BR';

if ($states_and_cities_enabled) {
    $file_name = $app->config['statesAndCities.file'];
    $content = $this->resolveFilename('states-and-cities', $file_name);
    include $content;

    $territorial = SearchModule::parseTerritorialFilters(
        $data,
        (string) ($app->config['search.filters.states'] ?? ''),
        (string) ($app->config['search.filters.cities'] ?? '')
    );

    // pré-filtra o dataset pelas UFs (e cidades) configuradas
    if ($territorial['states']) {
        $filtered_data = [];
        foreach ($territorial['states'] as $uf) {
            $state = $data[$uf];
            if ($territorial['cities']) {
                $state['cities'] = array_values(array_intersect($state['cities'], $territorial['cities']));
            }
            $filtered_data[$uf] = $state;
        }
        $data = $filtered_data;
    }

    $app->view->jsObject['config']['statesAndCities'] = $data;
    $app->view->jsObject['config']['statesAndCitiesEnable'] = true;
    $app->view->jsObject['config']['statesAndCitiesCountryCode'] = $app->config['statesAndCities.countryCode'];
}

// Publicado sempre: consumidores da UI precisam da chave mesmo com
// statesAndCities desligado (senão leriam `undefined`)
$app->view->jsObject['config']['searchTerritorialFilters'] = $states_and_cities_enabled ? [
    'statesForced'    => $territorial['states'],
    'citiesForced'    => $territorial['cities'],
    // Seletor de estado visível travado via locked-states — decisão do Product 2026-10-28
    'showStateFilter' => true,
    'showCityFilter'  => !($territorial['states'] && $territorial['cities']),
] : [
    'statesForced'    => [],
    'citiesForced'    => [],
    'showStateFilter' => false,
    'showCityFilter'  => false,
];
