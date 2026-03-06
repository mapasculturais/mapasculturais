<?php

namespace Tests;

use Laminas\Diactoros\ServerRequest;
use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\TestCase;
use Tests\Traits\RequestFactory;
use Tests\Traits\SealDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

/**
 * Testa os filtros de estado, cidade e selos na busca de eventos.
 *
 * Cobre:
 * - Filtragem de espaços por En_Estado via ApiQuery (camada usada por apiFindOccurrences)
 * - Filtragem de espaços por En_Municipio via ApiQuery
 * - Filtragem combinada de estado + cidade
 * - Filtragem de selos via ApiQuery
 * - Endpoint /api/event/findOccurrences aceita os novos parâmetros sem erros (HTTP 200)
 */
class EventOccurrencesFilterTest extends TestCase
{
    use RequestFactory,
        UserDirector,
        SpaceDirector,
        SealDirector;

    // ─── helpers ──────────────────────────────────────────────────────────────

    /** Cria um request GET para um endpoint de API (/api/controller/action) */
    private function apiGet(string $controller, string $action, array $query_params = []): ServerRequest
    {
        $uri = '/api/' . $controller . '/' . $action;
        return new ServerRequest(method: 'GET', uri: $uri, queryParams: $query_params);
    }

    /** Cria um espaço com os metadados de estado e cidade informados */
    private function createSpaceWithLocation(string $estado, string $municipio): Space
    {
        $this->app->disableAccessControl();

        $space = $this->spaceDirector->createSpace(disable_access_control: true);
        $space->En_Estado    = $estado;
        $space->En_Municipio = $municipio;
        $space->save(true);

        $this->app->enableAccessControl();

        return $space;
    }

    // ─── filtro por estado ────────────────────────────────────────────────────

    function testSpaceApiFilterBySingleState()
    {
        $spaceSP = $this->createSpaceWithLocation('SP', 'São Paulo');
        $spaceRJ = $this->createSpaceWithLocation('RJ', 'Rio de Janeiro');

        $query = new ApiQuery(Space::class, [
            '@select' => 'id,En_Estado',
            'En_Estado' => 'EQ(SP)',
        ]);

        $result    = $query->find();
        $resultIds = array_column($result, 'id');

        $this->assertContains($spaceSP->id, $resultIds,
            'ApiQuery com En_Estado=EQ(SP) deve retornar o espaço de SP.');
        $this->assertNotContains($spaceRJ->id, $resultIds,
            'ApiQuery com En_Estado=EQ(SP) não deve retornar o espaço de RJ.');
    }

    function testSpaceApiFilterByMultipleStates()
    {
        $spaceSP = $this->createSpaceWithLocation('SP', 'São Paulo');
        $spaceRJ = $this->createSpaceWithLocation('RJ', 'Rio de Janeiro');
        $spaceMG = $this->createSpaceWithLocation('MG', 'Belo Horizonte');

        $query = new ApiQuery(Space::class, [
            '@select' => 'id,En_Estado',
            'En_Estado' => API::IN(['SP', 'RJ']),
        ]);

        $result    = $query->find();
        $resultIds = array_column($result, 'id');

        $this->assertContains($spaceSP->id, $resultIds,
            'Deve retornar espaço de SP.');
        $this->assertContains($spaceRJ->id, $resultIds,
            'Deve retornar espaço de RJ.');
        $this->assertNotContains($spaceMG->id, $resultIds,
            'Não deve retornar espaço de MG com filtro IN(SP,RJ).');
    }

    // ─── filtro por município ─────────────────────────────────────────────────

    function testSpaceApiFilterByCity()
    {
        $spaceSP       = $this->createSpaceWithLocation('SP', 'São Paulo');
        $spaceCampinas = $this->createSpaceWithLocation('SP', 'Campinas');

        $query = new ApiQuery(Space::class, [
            '@select' => 'id,En_Estado,En_Municipio',
            'En_Estado'    => 'EQ(SP)',
            'En_Municipio' => 'EQ(São Paulo)',
        ]);

        $result    = $query->find();
        $resultIds = array_column($result, 'id');

        $this->assertContains($spaceSP->id, $resultIds,
            'Deve retornar espaço em São Paulo.');
        $this->assertNotContains($spaceCampinas->id, $resultIds,
            'Não deve retornar espaço em Campinas com filtro Para São Paulo.');
    }

    // ─── filtro por selos ─────────────────────────────────────────────────────

    function testSealEntityIsQueryable()
    {
        $this->app->disableAccessControl();
        $seal = $this->sealDirector->createSeal(disable_access_control: true);
        $this->app->enableAccessControl();

        $query = new ApiQuery(Seal::class, [
            '@select' => 'id,name',
            'id' => "EQ({$seal->id})",
        ]);

        $result = $query->find();

        $this->assertCount(1, $result,
            'A ApiQuery de Seal deve retornar o selo criado quando filtrado por id.');
        $this->assertEquals($seal->id, $result[0]['id'],
            'O id do selo retornado deve corresponder ao criado.');
    }

    // ─── endpoint findOccurrences: compatibilidade com novos parâmetros ───────

    function testFindOccurrencesEndpointReturns200WithoutFilters()
    {
        $request = $this->apiGet('event', 'findOccurrences', [
            '@from' => date('Y-m-d'),
            '@to'   => date('Y-m-d', strtotime('+7 days')),
        ]);

        $this->assertStatus200($request,
            'O endpoint findOccurrences deve retornar HTTP 200 sem filtros adicionais.');
    }

    function testFindOccurrencesEndpointAcceptsStateFilter()
    {
        $request = $this->apiGet('event', 'findOccurrences', [
            '@from'           => date('Y-m-d'),
            '@to'             => date('Y-m-d', strtotime('+7 days')),
            'space:En_Estado' => 'IN(SP)',
        ]);

        $this->assertStatus200($request,
            'O endpoint findOccurrences deve retornar HTTP 200 com filtro space:En_Estado.');
    }

    function testFindOccurrencesEndpointAcceptsCityFilter()
    {
        $request = $this->apiGet('event', 'findOccurrences', [
            '@from'              => date('Y-m-d'),
            '@to'                => date('Y-m-d', strtotime('+7 days')),
            'space:En_Estado'    => 'IN(SP)',
            'space:En_Municipio' => 'IN(São Paulo)',
        ]);

        $this->assertStatus200($request,
            'O endpoint findOccurrences deve retornar HTTP 200 com filtros de estado e município.');
    }

    function testFindOccurrencesEndpointAcceptsSealFilter()
    {
        $this->app->disableAccessControl();
        $seal = $this->sealDirector->createSeal(disable_access_control: true);
        $this->app->enableAccessControl();

        $request = $this->apiGet('event', 'findOccurrences', [
            '@from'  => date('Y-m-d'),
            '@to'    => date('Y-m-d', strtotime('+7 days')),
            '@seals' => (string) $seal->id,
        ]);

        $this->assertStatus200($request,
            'O endpoint findOccurrences deve retornar HTTP 200 com filtro @seals.');
    }
}
