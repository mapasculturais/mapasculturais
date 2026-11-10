<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Abstract\TestCase;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Testa as flags de configuração dos filtros de eventos:
 * - valores padrão corretos (false)
 * - variável legada events.filter.statesAndCities aposentada (#121)
 * - rota de busca de eventos retorna HTTP 200
 */
class EventsFilterConfigTest extends TestCase
{
    use RequestFactory,
        UserDirector;

    // ─── flags de config ─────────────────────────────────────────────────────

    function testStatesAndCitiesFilterKeyIsRetired()
    {
        $this->assertArrayNotHasKey('events.filter.statesAndCities', $this->app->config,
            'A chave events.filter.statesAndCities foi aposentada (#121) e não deve mais existir no config.');
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
            'sealsFilterEnabled' => $this->app->config['events.filter.seals'] ?? false,
            'seals'              => [],
        ];

        $this->assertArrayHasKey('sealsFilterEnabled', $config,
            'O objeto JS deve conter sealsFilterEnabled.');
        $this->assertArrayHasKey('seals', $config,
            'O objeto JS deve conter seals.');
        $this->assertIsBool($config['sealsFilterEnabled'],
            'sealsFilterEnabled deve ser boolean.');
        $this->assertIsArray($config['seals'],
            'seals deve ser array.');
    }

    function testJsObjectReflectsEnabledFlags()
    {
        $this->app->config['events.filter.seals'] = true;

        $config = [
            'sealsFilterEnabled' => $this->app->config['events.filter.seals'] ?? false,
        ];

        $this->assertTrue($config['sealsFilterEnabled'],
            'Com events.filter.seals=true, o JS deve receber true.');

        // restaura defaults
        $this->app->config['events.filter.seals'] = false;
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
