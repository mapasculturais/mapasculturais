<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use Laminas\Diactoros\ServerRequest;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\EventOccurrence;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\EventDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\ProjectDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

/**
 * Testa o RF-B7 (épica #99): filtros territoriais (En_Estado/En_Municipio) no
 * nível de API, na forma como a busca da UI os envia (operador IIN).
 *
 * Cobre:
 * - Agent, Space, Project e Opportunity: ApiQuery com En_Estado=IIN(...) e
 *   refinamento por En_Municipio=IIN(...)
 * - Event: os dois fluxos da busca por território —
 *   /api/event/findOccurrences (ocorrências filtradas pelo endereço do espaço)
 *   e /api/space/findByEvents (fix #120: chaves space:* aplicadas no ApiQuery
 *   de Space, com interseção AND quando há space:id explícito)
 * - IIN é unaccent+case-insensitive: IIN(sao paulo) casa dado "São Paulo"
 */
class SearchTerritorialFilterApiTest extends TestCase
{
    use UserDirector,
        AgentDirector,
        SpaceDirector,
        EventDirector,
        ProjectDirector,
        OpportunityBuilder;

    // janela futura determinística para as ocorrências (evita colidir com dados do seed)
    private const OCC_WINDOW_FROM = '+1 year';
    private const OCC_WINDOW_TO   = '+1 year +7 days';
    private const OCC_STARTS_ON   = '+1 year +2 days';

    // ─── helpers ──────────────────────────────────────────────────────────────

    /** Cria admin, loga e devolve o usuário dono das entidades do teste */
    private function adminUser(): User
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        return $admin;
    }

    /** Preenche endereço brasileiro completo nos campos En_* */
    private function fillAddress(Agent|Space|Project|Opportunity $entity, string $estado, string $municipio): void
    {
        $entity->address_level0 = 'BR';
        $entity->En_CEP = '01310-100';
        $entity->En_Nome_Logradouro = 'Avenida Paulista';
        $entity->En_Num = '1000';
        $entity->En_Bairro = 'Bela Vista';
        $entity->En_Municipio = $municipio;
        $entity->En_Estado = $estado;

        // em agent/project/opportunity o En_* é privado sem publicLocation
        // (em space o metadado já é público por definição)
        if (!$entity instanceof Space) {
            $entity->publicLocation = true;
        }
    }

    private function createAgentAt(User $owner, string $estado, string $municipio): Agent
    {
        $this->app->disableAccessControl();
        $agent = $this->agentDirector->createAgent($owner, 1, disable_access_control: true);
        $this->fillAddress($agent, $estado, $municipio);
        $agent->save(true);
        $this->app->enableAccessControl();

        return $agent;
    }

    private function createSpaceAt(User $owner, string $estado, string $municipio): Space
    {
        $this->app->disableAccessControl();
        $space = $this->spaceDirector->createSpace(owner: $owner->profile, disable_access_control: true);
        $this->fillAddress($space, $estado, $municipio);
        $space->save(true);
        $this->app->enableAccessControl();

        return $space;
    }

    private function createProjectAt(User $owner, string $estado, string $municipio): Project
    {
        $this->app->disableAccessControl();
        $project = $this->projectDirector->createProject(owner: $owner->profile, disable_access_control: true);
        $this->fillAddress($project, $estado, $municipio);
        $project->save(true);
        $this->app->enableAccessControl();

        return $project;
    }

    private function createOpportunityAt(User $owner, Project $parent, string $estado, string $municipio): Opportunity
    {
        $this->app->disableAccessControl();
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $parent)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();
        $this->fillAddress($opportunity, $estado, $municipio);
        $opportunity->save(true);
        $this->app->enableAccessControl();

        return $opportunity;
    }

    /** Eventos com ocorrência futura em espaços de territórios distintos */
    private function createEventFixture(): array
    {
        $owner = $this->adminUser();

        $space_sp = $this->createSpaceAt($owner, 'SP', 'Santos');
        $space_rj = $this->createSpaceAt($owner, 'RJ', 'Angra dos Reis');

        $event_sp = $this->eventDirector->createEvent(owner: $owner->profile, disable_access_control: true);
        $event_rj = $this->eventDirector->createEvent(owner: $owner->profile, disable_access_control: true);

        $this->createFutureOccurrence($event_sp, $space_sp);
        $this->createFutureOccurrence($event_rj, $space_rj);

        return [
            'space_sp' => $space_sp,
            'space_rj' => $space_rj,
            'event_sp' => $event_sp,
            'event_rj' => $event_rj,
        ];
    }

    private function createFutureOccurrence(Event $event, Space $space): void
    {
        $occurrence = new EventOccurrence;
        $occurrence->event = $event;
        $occurrence->space = $space;
        $occurrence->status = EventOccurrence::STATUS_ENABLED;
        $occurrence->description = 'Sessão da fixture territorial';
        $occurrence->price = 'Gratuito';
        $occurrence->setRule([
            'spaceId' => $space->id,
            'startsOn' => date('Y-m-d', strtotime(self::OCC_STARTS_ON)),
            'startsAt' => '10:00',
            'duration' => 60,
            'frequency' => 'once',
            'until' => null,
            'day' => null,
        ]);

        $this->app->disableAccessControl();
        $occurrence->save(true);
        $this->app->enableAccessControl();
    }

    /** Executa a ApiQuery como anônima e devolve apenas os ids encontrados */
    private function findIds(string $entity_class, array $filters): array
    {
        $this->logout();

        $query = new ApiQuery($entity_class, ['@select' => 'id'] + $filters);
        return array_column($query->find(), 'id');
    }

    /** GET em endpoint de API (/api/controller/action); devolve [http_status, corpo decodificado] */
    private function apiGetJson(string $controller, string $action, array $query_params): array
    {
        $this->logout();

        $app = App::i();
        $request = new ServerRequest(
            method: 'GET',
            uri: '/api/' . $controller . '/' . $action,
            queryParams: $query_params,
        );
        $app->run($request, false);

        return [
            $app->response->getStatusCode(),
            json_decode((string) $app->response->getBody(), true),
        ];
    }

    private function occurrenceWindow(): array
    {
        return [
            '@from' => date('Y-m-d', strtotime(self::OCC_WINDOW_FROM)),
            '@to'   => date('Y-m-d', strtotime(self::OCC_WINDOW_TO)),
        ];
    }

    // ─── agent ────────────────────────────────────────────────────────────────

    function testAgentSearchFiltersByStateAndRefinesByCity()
    {
        $owner = $this->adminUser();
        $agent_santos   = $this->createAgentAt($owner, 'SP', 'Santos');
        $agent_campinas = $this->createAgentAt($owner, 'SP', 'Campinas');
        $agent_angra    = $this->createAgentAt($owner, 'RJ', 'Angra dos Reis');

        $by_state = $this->findIds(Agent::class, ['En_Estado' => 'IIN(SP)']);
        $this->assertContains($agent_santos->id, $by_state, 'Agentes de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertContains($agent_campinas->id, $by_state, 'Agentes de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertNotContains($agent_angra->id, $by_state, 'Agente do RJ não deve ser encontrado com En_Estado=IIN(SP)');

        $refined = $this->findIds(Agent::class, [
            'En_Estado'    => 'IIN(SP)',
            'En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertContains($agent_santos->id, $refined, 'Estado+Município deve manter o agente de Santos');
        $this->assertNotContains($agent_campinas->id, $refined, 'Estado+Município deve excluir o agente de Campinas');
        $this->assertNotContains($agent_angra->id, $refined, 'Estado+Município deve excluir o agente do RJ');
    }

    // ─── space ────────────────────────────────────────────────────────────────

    function testSpaceSearchFiltersByStateAndRefinesByCity()
    {
        $owner = $this->adminUser();
        $space_santos   = $this->createSpaceAt($owner, 'SP', 'Santos');
        $space_campinas = $this->createSpaceAt($owner, 'SP', 'Campinas');
        $space_angra    = $this->createSpaceAt($owner, 'RJ', 'Angra dos Reis');

        $by_state = $this->findIds(Space::class, ['En_Estado' => 'IIN(SP)']);
        $this->assertContains($space_santos->id, $by_state, 'Espaços de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertContains($space_campinas->id, $by_state, 'Espaços de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertNotContains($space_angra->id, $by_state, 'Espaço do RJ não deve ser encontrado com En_Estado=IIN(SP)');

        $refined = $this->findIds(Space::class, [
            'En_Estado'    => 'IIN(SP)',
            'En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertContains($space_santos->id, $refined, 'Estado+Município deve manter o espaço de Santos');
        $this->assertNotContains($space_campinas->id, $refined, 'Estado+Município deve excluir o espaço de Campinas');
        $this->assertNotContains($space_angra->id, $refined, 'Estado+Município deve excluir o espaço do RJ');
    }

    // ─── project ──────────────────────────────────────────────────────────────

    function testProjectSearchFiltersByStateAndRefinesByCity()
    {
        $owner = $this->adminUser();
        $project_santos   = $this->createProjectAt($owner, 'SP', 'Santos');
        $project_campinas = $this->createProjectAt($owner, 'SP', 'Campinas');
        $project_angra    = $this->createProjectAt($owner, 'RJ', 'Angra dos Reis');

        $by_state = $this->findIds(Project::class, ['En_Estado' => 'IIN(SP)']);
        $this->assertContains($project_santos->id, $by_state, 'Projetos de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertContains($project_campinas->id, $by_state, 'Projetos de SP devem ser encontrados com En_Estado=IIN(SP)');
        $this->assertNotContains($project_angra->id, $by_state, 'Projeto do RJ não deve ser encontrado com En_Estado=IIN(SP)');

        $refined = $this->findIds(Project::class, [
            'En_Estado'    => 'IIN(SP)',
            'En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertContains($project_santos->id, $refined, 'Estado+Município deve manter o projeto de Santos');
        $this->assertNotContains($project_campinas->id, $refined, 'Estado+Município deve excluir o projeto de Campinas');
        $this->assertNotContains($project_angra->id, $refined, 'Estado+Município deve excluir o projeto do RJ');
    }

    // ─── opportunity ──────────────────────────────────────────────────────────

    function testOpportunitySearchFiltersByStateAndRefinesByCity()
    {
        $owner = $this->adminUser();
        $parent = $this->createProjectAt($owner, 'SP', 'Santos');

        $opp_santos   = $this->createOpportunityAt($owner, $parent, 'SP', 'Santos');
        $opp_campinas = $this->createOpportunityAt($owner, $parent, 'SP', 'Campinas');
        $opp_angra    = $this->createOpportunityAt($owner, $parent, 'RJ', 'Angra dos Reis');

        $by_state = $this->findIds(Opportunity::class, ['En_Estado' => 'IIN(SP)']);
        $this->assertContains($opp_santos->id, $by_state, 'Oportunidades de SP devem ser encontradas com En_Estado=IIN(SP)');
        $this->assertContains($opp_campinas->id, $by_state, 'Oportunidades de SP devem ser encontradas com En_Estado=IIN(SP)');
        $this->assertNotContains($opp_angra->id, $by_state, 'Oportunidade do RJ não deve ser encontrada com En_Estado=IIN(SP)');

        $refined = $this->findIds(Opportunity::class, [
            'En_Estado'    => 'IIN(SP)',
            'En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertContains($opp_santos->id, $refined, 'Estado+Município deve manter a oportunidade de Santos');
        $this->assertNotContains($opp_campinas->id, $refined, 'Estado+Município deve excluir a oportunidade de Campinas');
        $this->assertNotContains($opp_angra->id, $refined, 'Estado+Município deve excluir a oportunidade do RJ');
    }

    // ─── event: fluxo findOccurrences ────────────────────────────────────────

    function testFindOccurrencesFiltersBySpaceState()
    {
        $fixture = $this->createEventFixture();

        [$status, $body] = $this->apiGetJson('event', 'findOccurrences',
            $this->occurrenceWindow() + ['space:En_Estado' => 'IIN(SP)']);
        $this->assertSame(200, $status, 'findOccurrences com space:En_Estado deve retornar 200');
        $event_ids = array_column($body, 'event_id');
        $this->assertContains($fixture['event_sp']->id, $event_ids,
            'Ocorrências do evento do espaço SP devem aparecer com space:En_Estado=IIN(SP)');
        $this->assertNotContains($fixture['event_rj']->id, $event_ids,
            'Ocorrências do evento do espaço RJ não devem aparecer com space:En_Estado=IIN(SP)');

        [$status, $body] = $this->apiGetJson('event', 'findOccurrences',
            $this->occurrenceWindow() + ['space:En_Estado' => 'IIN(RJ)']);
        $this->assertSame(200, $status, 'findOccurrences com space:En_Estado=IIN(RJ) deve retornar 200');
        $event_ids = array_column($body, 'event_id');
        $this->assertContains($fixture['event_rj']->id, $event_ids,
            'Ocorrências do evento do espaço RJ devem aparecer com space:En_Estado=IIN(RJ)');
        $this->assertNotContains($fixture['event_sp']->id, $event_ids,
            'Ocorrências do evento do espaço SP não devem aparecer com space:En_Estado=IIN(RJ)');
    }

    function testFindOccurrencesRefinesBySpaceStateAndCity()
    {
        $fixture = $this->createEventFixture();

        [$status, $body] = $this->apiGetJson('event', 'findOccurrences', $this->occurrenceWindow() + [
            'space:En_Estado'    => 'IIN(SP)',
            'space:En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertSame(200, $status, 'findOccurrences com estado+município deve retornar 200');
        $event_ids = array_column($body, 'event_id');
        $this->assertContains($fixture['event_sp']->id, $event_ids,
            'Estado+Município do espaço deve manter o evento de Santos');
        $this->assertNotContains($fixture['event_rj']->id, $event_ids,
            'Estado+Município do espaço deve excluir o evento de Angra dos Reis');

        // município sem espaço na janela: refinamento entrega vazio
        [, $body] = $this->apiGetJson('event', 'findOccurrences', $this->occurrenceWindow() + [
            'space:En_Estado'    => 'IIN(SP)',
            'space:En_Municipio' => 'IIN(Campinas)',
        ]);
        $this->assertSame([], $body,
            'Município sem espaço com ocorrência na janela não deve retornar ocorrências');
    }

    // ─── event: fluxo findByEvents (fix #120) ────────────────────────────────

    function testFindByEventsFiltersBySpaceState()
    {
        $fixture = $this->createEventFixture();

        [$status, $body] = $this->apiGetJson('space', 'findByEvents',
            $this->occurrenceWindow() + ['@select' => 'id,name', 'space:En_Estado' => 'IIN(SP)']);
        $this->assertSame(200, $status, 'findByEvents com space:En_Estado deve retornar 200');
        $space_ids = array_column($body, 'id');
        $this->assertContains($fixture['space_sp']->id, $space_ids,
            'findByEvents com space:En_Estado=IIN(SP) deve retornar o espaço de Santos');
        $this->assertNotContains($fixture['space_rj']->id, $space_ids,
            'findByEvents com space:En_Estado=IIN(SP) não deve retornar o espaço de Angra dos Reis');

        [$status, $body] = $this->apiGetJson('space', 'findByEvents',
            $this->occurrenceWindow() + ['@select' => 'id,name', 'space:En_Estado' => 'IIN(RJ)']);
        $this->assertSame(200, $status, 'findByEvents com space:En_Estado=IIN(RJ) deve retornar 200');
        $space_ids = array_column($body, 'id');
        $this->assertContains($fixture['space_rj']->id, $space_ids,
            'findByEvents com space:En_Estado=IIN(RJ) deve retornar o espaço de Angra dos Reis');
        $this->assertNotContains($fixture['space_sp']->id, $space_ids,
            'findByEvents com space:En_Estado=IIN(RJ) não deve retornar o espaço de Santos');
    }

    function testFindByEventsRefinesBySpaceStateAndCity()
    {
        $fixture = $this->createEventFixture();

        [$status, $body] = $this->apiGetJson('space', 'findByEvents', $this->occurrenceWindow() + [
            '@select'            => 'id,name',
            'space:En_Estado'    => 'IIN(SP)',
            'space:En_Municipio' => 'IIN(Santos)',
        ]);
        $this->assertSame(200, $status, 'findByEvents com estado+município deve retornar 200');
        $space_ids = array_column($body, 'id');
        $this->assertContains($fixture['space_sp']->id, $space_ids,
            'Estado+Município deve manter o espaço de Santos');
        $this->assertNotContains($fixture['space_rj']->id, $space_ids,
            'Estado+Município deve excluir o espaço de Angra dos Reis');

        // município sem espaço na janela: refinamento entrega vazio
        [, $body] = $this->apiGetJson('space', 'findByEvents', $this->occurrenceWindow() + [
            '@select'            => 'id,name',
            'space:En_Estado'    => 'IIN(SP)',
            'space:En_Municipio' => 'IIN(Campinas)',
        ]);
        $this->assertSame([], $body,
            'Município sem espaço com ocorrência na janela não deve retornar espaços');
    }

    function testFindByEventsIntersectsExplicitSpaceIdWithTerritory()
    {
        $fixture = $this->createEventFixture();

        // space:id do RJ + recorte SP: a interseção AND (fix #120) deve entregar vazio
        [$status, $body] = $this->apiGetJson('space', 'findByEvents', $this->occurrenceWindow() + [
            '@select'         => 'id,name',
            'space:id'        => "EQ({$fixture['space_rj']->id})",
            'space:En_Estado' => 'IIN(SP)',
        ]);
        $this->assertSame(200, $status, 'findByEvents com space:id + space:En_Estado deve retornar 200');
        $this->assertSame([], $body,
            'space:id de Angra dos Reis intersectado com En_Estado=IIN(SP) deve ser vazio');

        // space:id do SP dentro do recorte SP: mantém o espaço
        [, $body] = $this->apiGetJson('space', 'findByEvents', $this->occurrenceWindow() + [
            '@select'         => 'id,name',
            'space:id'        => "EQ({$fixture['space_sp']->id})",
            'space:En_Estado' => 'IIN(SP)',
        ]);
        $this->assertSame([$fixture['space_sp']->id], array_column($body, 'id'),
            'space:id de Santos dentro do recorte SP deve retornar apenas o espaço de Santos');
    }

    // ─── IIN: unaccent + case-insensitive ────────────────────────────────────

    function testIinFilterMatchesAccentedCityIgnoringAccentAndCase()
    {
        $owner = $this->adminUser();
        $space_sp = $this->createSpaceAt($owner, 'SP', 'São Paulo');
        $space_sa = $this->createSpaceAt($owner, 'SP', 'Santo André');

        // filtro sem acento e em minúsculas casa o dado gravado acentuado
        $ids = $this->findIds(Space::class, ['En_Municipio' => 'IIN(sao paulo)']);
        $this->assertContains($space_sp->id, $ids,
            'IIN(sao paulo) deve casar o município gravado como "São Paulo"');
        $this->assertNotContains($space_sa->id, $ids,
            'IIN(sao paulo) não deve casar "Santo André"');

        // filtro acentuado e em maiúsculas também casa (lower nos dois lados)
        $ids = $this->findIds(Space::class, ['En_Municipio' => 'IIN(SÃO PAULO)']);
        $this->assertContains($space_sp->id, $ids,
            'IIN(SÃO PAULO) deve casar o município gravado como "São Paulo"');
        $this->assertNotContains($space_sa->id, $ids,
            'IIN(SÃO PAULO) não deve casar "Santo André"');

        // UF gravada em maiúsculas casa filtro em minúsculas
        $ids = $this->findIds(Space::class, ['En_Estado' => 'IIN(sp)']);
        $this->assertContains($space_sp->id, $ids, 'IIN(sp) deve casar a UF gravada como "SP"');
        $this->assertContains($space_sa->id, $ids, 'IIN(sp) deve casar a UF gravada como "SP"');
    }
}
