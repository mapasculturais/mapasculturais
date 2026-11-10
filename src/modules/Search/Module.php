<?php

namespace Search;
use MapasCulturais\App;
use MapasCulturais\Utils;

class Module extends \MapasCulturais\Module
{
    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init()
    {
    }

    function register()
    {        
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        $app->registerController('search', Controller::class);
        if (!isset($controllers['search'])) {
        }
    }

    /**
     * Returns the territorial search filters resolved from the
     * `search.filters.states` / `search.filters.cities` configs (ADR 0019).
     *
     * @return array{states: string[], cities: string[], statesLabels: array<string,string>}
     */
    public static function getTerritorialFilters(): array
    {
        $app = App::i();

        $file = basename((string) ($app->config['statesAndCities.file'] ?? 'brasil.php'));
        $path = __DIR__ . "/../Entities/states-and-cities/{$file}";
        $data = (function () use ($path): array {
            include $path;
            return $data ?? [];
        })();

        return self::parseTerritorialFilters(
            $data,
            (string) ($app->config['search.filters.states'] ?? ''),
            (string) ($app->config['search.filters.cities'] ?? '')
        );
    }

    /**
     * Parses the territorial filter configs against the states-and-cities data
     * (see src/modules/Entities/states-and-cities/brasil.php).
     *
     * Both configs are comma-separated lists of the names displayed to the user.
     * Comparison is accent/case-insensitive on both sides. Unknown states are
     * ignored with a warning. Cities are only processed when at least one state
     * matched, and only against the cities of the matched states; cities outside
     * them are ignored with a warning.
     *
     * @param array $statesAndCitiesData ['UF' => ['label' => string, 'cities' => string[]], ...]
     * @return array{states: string[], cities: string[], statesLabels: array<string,string>}
     */
    public static function parseTerritorialFilters(array $statesAndCitiesData, string $statesFilter = '', string $citiesFilter = ''): array
    {
        $app = App::i();

        $result = ['states' => [], 'cities' => [], 'statesLabels' => []];

        $requestedStates = array_filter(array_map('trim', explode(',', $statesFilter)), fn($name) => $name !== '');
        foreach ($requestedStates as $stateName) {
            $normalized = Utils::sanitizeString($stateName);
            $matchedUf = null;
            foreach ($statesAndCitiesData as $uf => $stateData) {
                if (Utils::sanitizeString((string) ($stateData['label'] ?? '')) === $normalized) {
                    $matchedUf = $uf;
                    break;
                }
            }

            if ($matchedUf === null) {
                $app->log->warning("Search: estado desconhecido em 'search.filters.states' ignorado: \"{$stateName}\"");
                continue;
            }

            if (!in_array($matchedUf, $result['states'])) {
                $result['states'][] = $matchedUf;
                $result['statesLabels'][$matchedUf] = $statesAndCitiesData[$matchedUf]['label'] ?? $matchedUf;
            }
        }

        if (!$result['states']) {
            return $result;
        }

        $cityIndex = [];
        foreach ($result['states'] as $uf) {
            foreach ($statesAndCitiesData[$uf]['cities'] ?? [] as $city) {
                $normalized = Utils::sanitizeString($city);
                if ($normalized !== '' && !isset($cityIndex[$normalized])) {
                    $cityIndex[$normalized] = $city;
                }
            }
        }

        $requestedCities = array_filter(array_map('trim', explode(',', $citiesFilter)), fn($name) => $name !== '');
        foreach ($requestedCities as $cityName) {
            $normalized = Utils::sanitizeString($cityName);
            if (isset($cityIndex[$normalized])) {
                $canonical = $cityIndex[$normalized];
                if (!in_array($canonical, $result['cities'])) {
                    $result['cities'][] = $canonical;
                }
            } elseif ($normalized !== '') {
                $app->log->warning("Search: município fora dos estados configurados em 'search.filters.cities' ignorado: \"{$cityName}\"");
            }
        }

        return $result;
    }
}