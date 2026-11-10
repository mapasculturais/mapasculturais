<?php

namespace Tests;

use MapasCulturais\App;
use Search\Module as SearchModule;
use Tests\Abstract\TestCase;

class SearchTerritorialFiltersTest extends TestCase
{
    private array $data;
    private array $originalLogHandlers;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__) . '/src/modules/Entities/states-and-cities/brasil.php';
        $this->data = (function () use ($path): array {
            include $path;
            return $data;
        })();

        // silencia o logger para que os warnings esperados do parser não vazem
        // para o output do processo isolado (PHPUnit trata output como erro)
        $this->originalLogHandlers = $this->app->log->getHandlers();
        $this->app->log->setHandlers([new \Monolog\Handler\NullHandler()]);
    }

    protected function tearDown(): void
    {
        $this->app->log->setHandlers($this->originalLogHandlers);

        parent::tearDown();
    }

    public function testEmptyWhenNoFilterIsConfigured(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, '', '');

        $this->assertSame(['states' => [], 'cities' => [], 'statesLabels' => []], $result);
    }

    public function testOnlyStates(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, 'Acre, São Paulo', '');

        $this->assertSame(['AC', 'SP'], $result['states']);
        $this->assertSame([], $result['cities']);
        $this->assertSame(['AC' => 'Acre', 'SP' => 'São Paulo'], $result['statesLabels']);
    }

    public function testStatesAndCitiesIgnoresCitiesOutsideConfiguredStates(): void
    {
        $result = SearchModule::parseTerritorialFilters(
            $this->data,
            'São Paulo, Rio de Janeiro',
            'São Paulo, Rio de Janeiro, Porto Alegre'
        );

        $this->assertSame(['SP', 'RJ'], $result['states']);
        $this->assertSame(['São Paulo', 'Rio de Janeiro'], $result['cities']);
    }

    public function testHomonymousCityStaysConfinedToConfiguredStates(): void
    {
        // "Belém" existe como município em AL, PA e PB
        $result = SearchModule::parseTerritorialFilters($this->data, 'Alagoas', 'Belém');
        $this->assertSame(['AL'], $result['states']);
        $this->assertSame(['Belém'], $result['cities']);

        // sem nenhum dos estados que contêm o município, ele é ignorado
        $result = SearchModule::parseTerritorialFilters($this->data, 'Acre', 'Belém');
        $this->assertSame(['AC'], $result['states']);
        $this->assertSame([], $result['cities']);
    }

    public function testOnlyCitiesHasNoEffect(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, '', 'São Paulo, Rio Branco');

        $this->assertSame(['states' => [], 'cities' => [], 'statesLabels' => []], $result);
    }

    public function testStateMatchingIsAccentAndCaseInsensitive(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, 'sao paulo', '');

        $this->assertSame(['SP'], $result['states']);
        $this->assertSame(['SP' => 'São Paulo'], $result['statesLabels']);
    }

    public function testCityMatchingIsAccentAndCaseInsensitive(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, 'Acre', 'rio branco');

        $this->assertSame(['Rio Branco'], $result['cities']);
    }

    public function testStateVersusCityIsResolvedByTheOriginVariable(): void
    {
        // "São Paulo" na variável de estados resolve para a UF
        $asState = SearchModule::parseTerritorialFilters($this->data, 'São Paulo', '');
        $this->assertSame(['SP'], $asState['states']);
        $this->assertSame([], $asState['cities']);

        // "São Paulo" na variável de cidades resolve para o município
        $asCity = SearchModule::parseTerritorialFilters($this->data, 'São Paulo', 'São Paulo');
        $this->assertSame(['SP'], $asCity['states']);
        $this->assertSame(['São Paulo'], $asCity['cities']);
    }

    public function testUnknownStatesAreIgnored(): void
    {
        $result = SearchModule::parseTerritorialFilters($this->data, 'Nárnia, Acre', 'Rio Branco');

        $this->assertSame(['AC'], $result['states']);
        $this->assertSame(['Rio Branco'], $result['cities']);
        $this->assertSame(['AC' => 'Acre'], $result['statesLabels']);
    }

    public function testGetTerritorialFiltersReadsFromAppConfig(): void
    {
        $app = App::i();
        $originalStates = $app->config['search.filters.states'] ?? null;
        $originalCities = $app->config['search.filters.cities'] ?? null;

        try {
            $app->config['search.filters.states'] = 'sao paulo';
            $app->config['search.filters.cities'] = 'campinas';

            $result = SearchModule::getTerritorialFilters();

            $this->assertSame(['SP'], $result['states']);
            $this->assertSame(['Campinas'], $result['cities']);
            $this->assertSame(['SP' => 'São Paulo'], $result['statesLabels']);
        } finally {
            $app->config['search.filters.states'] = $originalStates;
            $app->config['search.filters.cities'] = $originalCities;
        }
    }
}
