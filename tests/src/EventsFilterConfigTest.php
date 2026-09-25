<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Abstract\TestCase;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Testa as flags de configuração dos filtros de eventos:
 * - valores padrão corretos (false)
 * - rota de busca de eventos retorna HTTP 200
 */
class EventsFilterConfigTest extends TestCase
{
    use RequestFactory,
        UserDirector;

    // ─── flags de config ─────────────────────────────────────────────────────

    function testStatesAndCitiesFilterDefaultIsFalse()
    {
        $value = $this->app->config['events.filter.statesAndCities'] ?? 'AUSENTE';

        $this->assertNotSame('AUSENTE', $value,
            'A chave events.filter.statesAndCities deve existir no config.');
        $this->assertFalse($value,
            'A flag events.filter.statesAndCities deve ter o valor padrão false.');
    }

    function testSealsFilterDefaultIsFalse()
    {
        $value = $this->app->config['events.filter.seals'] ?? 'AUSENTE';

        $this->assertNotSame('AUSENTE', $value,
            'A chave events.filter.seals deve existir no config.');
        $this->assertFalse($value,
            'A flag events.filter.seals deve ter o valor padrão false.');
    }

    // ─── objeto JS exposto pelo init.php ─────────────────────────────────────

    function testJsObjectStructure()
    {
        $config = [
            'statesAndCitiesFilterEnabled' => $this->app->config['events.filter.statesAndCities'] ?? false,
            'sealsFilterEnabled'           => $this->app->config['events.filter.seals'] ?? false,
        ];

        $this->assertArrayHasKey('statesAndCitiesFilterEnabled', $config,
            'O objeto JS deve conter statesAndCitiesFilterEnabled.');
        $this->assertArrayHasKey('sealsFilterEnabled', $config,
            'O objeto JS deve conter sealsFilterEnabled.');
        $this->assertIsBool($config['statesAndCitiesFilterEnabled'],
            'statesAndCitiesFilterEnabled deve ser boolean.');
        $this->assertIsBool($config['sealsFilterEnabled'],
            'sealsFilterEnabled deve ser boolean.');
    }

    function testJsObjectReflectsEnabledFlags()
    {
        $this->app->config['events.filter.statesAndCities'] = true;
        $this->app->config['events.filter.seals']           = true;

        $config = [
            'statesAndCitiesFilterEnabled' => $this->app->config['events.filter.statesAndCities'] ?? false,
            'sealsFilterEnabled'           => $this->app->config['events.filter.seals'] ?? false,
        ];

        $this->assertTrue($config['statesAndCitiesFilterEnabled'],
            'Com events.filter.statesAndCities=true, o JS deve receber true.');
        $this->assertTrue($config['sealsFilterEnabled'],
            'Com events.filter.seals=true, o JS deve receber true.');

        // restaura defaults
        $this->app->config['events.filter.statesAndCities'] = false;
        $this->app->config['events.filter.seals']           = false;
    }

    // ─── rota HTTP da busca de eventos ───────────────────────────────────────

    function testEventSearchRouteReturns200()
    {
        $request = $this->requestFactory->GET('search', 'events');

        $this->assertStatus200($request, 'A rota de busca de eventos deve retornar HTTP 200.');
    }

    function testEventSearchRouteReturns200WhenLoggedIn()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $request = $this->requestFactory->GET('search', 'events');

        $this->assertStatus200($request, 'A rota de busca de eventos deve retornar HTTP 200 com usuário logado.');
    }
}
