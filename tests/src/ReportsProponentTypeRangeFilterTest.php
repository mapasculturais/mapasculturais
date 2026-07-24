<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use Reports\QueryFilters;
use Tests\Abstract\TestCase;
use Tests\Enums\ProponentTypes;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;
use Tests\Builders\PhasePeriods\Open;

/**
 * Cobre a adição do filtro de tipo de proponente e de faixa/linha aos
 * relatórios de oportunidade (módulo Reports), incluindo a correção do bug
 * de deduplicação de gráficos salvos (POST /reports/saveGraphic).
 */
class ReportsProponentTypeRangeFilterTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector,
        RequestFactory;

    // =================== QueryFilters (unidade) ===================

    function testStatusOperatorMapsKnownValues()
    {
        $this->assertEquals('>= 0', QueryFilters::statusOperator('all'));
        $this->assertEquals('= 0', QueryFilters::statusOperator('draft'));
        $this->assertEquals('>= 1', QueryFilters::statusOperator('send'));
        $this->assertEquals('= 2', QueryFilters::statusOperator('invalid'));
        $this->assertEquals('= 3', QueryFilters::statusOperator('notapproved'));
        $this->assertEquals('= 8', QueryFilters::statusOperator('waitlist'));
        $this->assertEquals('= 10', QueryFilters::statusOperator('approved'));
    }

    function testStatusOperatorDefaultsToGreaterThanZeroForUnknownValue()
    {
        $this->assertEquals('> 0', QueryFilters::statusOperator('valor-desconhecido'));
    }

    function testProponentTypeClauseIsEmptyWhenNoValues()
    {
        [$sql, $params] = QueryFilters::proponentTypeClause(null);
        $this->assertSame('', $sql);
        $this->assertSame([], $params);

        [$sql, $params] = QueryFilters::proponentTypeClause([]);
        $this->assertSame('', $sql);
        $this->assertSame([], $params);
    }

    function testProponentTypeClauseBuildsInClauseWithNamedParams()
    {
        [$sql, $params] = QueryFilters::proponentTypeClause(['Pessoa Física', 'MEI']);
        $this->assertSame(' AND r.proponent_type IN (:proponentType0,:proponentType1)', $sql);
        $this->assertSame(['proponentType0' => 'Pessoa Física', 'proponentType1' => 'MEI'], $params);
    }

    function testProponentTypeClauseIgnoresEmptyAndNullValues()
    {
        [$sql, $params] = QueryFilters::proponentTypeClause(['', null, 'MEI']);
        $this->assertSame(' AND r.proponent_type IN (:proponentType0)', $sql);
        $this->assertSame(['proponentType0' => 'MEI'], $params);
    }

    function testRangeClauseBuildsInClauseWithNamedParams()
    {
        [$sql, $params] = QueryFilters::rangeClause(['Faixa A']);
        $this->assertSame(' AND r.range IN (:range0)', $sql);
        $this->assertSame(['range0' => 'Faixa A'], $params);
    }

    function testRangeClauseIsEmptyWhenNoValues()
    {
        [$sql, $params] = QueryFilters::rangeClause(null);
        $this->assertSame('', $sql);
        $this->assertSame([], $params);
    }

    // =================== Helpers de integração ===================

    private function createOpportunityWithProponentTypesAndRanges(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        return $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->addProponentType(ProponentTypes::PESSOA_FISICA)
            ->addProponentType(ProponentTypes::MEI)
            ->addRange('Faixa A')
            ->addRange('Faixa B')
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();
    }

    private function callReportsEndpoint(string $method, string $action, array $query = [], array $payload = []): array
    {
        $app = App::i();
        $request = $method === 'GET'
            ? $this->requestFactory->GET(controller_id: 'reports', action: $action, query_params: $query, ajax: true)
            : $this->requestFactory->POST(controller_id: 'reports', action: $action, payload: $payload, ajax: true);

        $app->reset();
        $app->run($request, false);

        $this->assertEquals(200, $app->response->getStatusCode(), "reports/{$action} deveria responder 200");

        return json_decode((string) $app->response->getBody(), true) ?? [];
    }

    private function fetchStaticCharts(Opportunity $opportunity, array $extraQuery = []): array
    {
        return $this->callReportsEndpoint('GET', 'staticCharts', array_merge(['opportunity_id' => $opportunity->id], $extraQuery));
    }

    // =================== GET /reports/staticCharts ===================

    function testStaticChartsWithoutFilterCountsAllApprovedRegistrations()
    {
        $opportunity = $this->createOpportunityWithProponentTypesAndRanges();

        $pfFaixaA = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 3,
            proponent_type: ProponentTypes::PESSOA_FISICA->value, range: 'Faixa A'
        );
        foreach ($pfFaixaA as $registration) {
            $registration->setStatus(10);
        }

        $meiFaixaB = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 2,
            proponent_type: ProponentTypes::MEI->value, range: 'Faixa B'
        );
        foreach ($meiFaixaB as $registration) {
            $registration->setStatus(10);
        }

        $charts = $this->fetchStaticCharts($opportunity, ['status' => 'approved']);

        $this->assertEquals(
            5,
            array_sum($charts['registrationsByStatus']['data']),
            'sem filtro, o gráfico de status deve contar as 5 inscrições aprovadas'
        );
    }

    function testStaticChartsFiltersByProponentType()
    {
        $opportunity = $this->createOpportunityWithProponentTypesAndRanges();

        $pf = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 3,
            proponent_type: ProponentTypes::PESSOA_FISICA->value, range: 'Faixa A'
        );
        foreach ($pf as $registration) {
            $registration->setStatus(10);
        }

        $mei = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 2,
            proponent_type: ProponentTypes::MEI->value, range: 'Faixa B'
        );
        foreach ($mei as $registration) {
            $registration->setStatus(10);
        }

        $charts = $this->fetchStaticCharts($opportunity, [
            'status' => 'approved',
            'proponentType' => ProponentTypes::PESSOA_FISICA->value,
        ]);

        $this->assertEquals(
            3,
            array_sum($charts['registrationsByStatus']['data']),
            'filtrando por Pessoa Física, o gráfico de status deve contar só as 3 inscrições desse tipo'
        );
    }

    function testStaticChartsFiltersByRange()
    {
        $opportunity = $this->createOpportunityWithProponentTypesAndRanges();

        $pf = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 3,
            proponent_type: ProponentTypes::PESSOA_FISICA->value, range: 'Faixa A'
        );
        foreach ($pf as $registration) {
            $registration->setStatus(10);
        }

        $mei = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 2,
            proponent_type: ProponentTypes::MEI->value, range: 'Faixa B'
        );
        foreach ($mei as $registration) {
            $registration->setStatus(10);
        }

        $charts = $this->fetchStaticCharts($opportunity, [
            'status' => 'approved',
            'range' => 'Faixa B',
        ]);

        $this->assertEquals(
            2,
            array_sum($charts['registrationsByStatus']['data']),
            'filtrando por Faixa B, o gráfico de status deve contar só as 2 inscrições dessa faixa'
        );
    }

    function testStaticChartsFiltersByProponentTypeAndRangeCombined()
    {
        $opportunity = $this->createOpportunityWithProponentTypesAndRanges();

        // Pessoa Física + Faixa A: 3 inscrições
        $pfFaixaA = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 3,
            proponent_type: ProponentTypes::PESSOA_FISICA->value, range: 'Faixa A'
        );
        foreach ($pfFaixaA as $registration) {
            $registration->setStatus(10);
        }

        // Pessoa Física + Faixa B: 4 inscrições (mesmo tipo, faixa diferente)
        $pfFaixaB = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 4,
            proponent_type: ProponentTypes::PESSOA_FISICA->value, range: 'Faixa B'
        );
        foreach ($pfFaixaB as $registration) {
            $registration->setStatus(10);
        }

        // MEI + Faixa A: 2 inscrições (tipo diferente, mesma faixa)
        $meiFaixaA = $this->registrationDirector->createSentRegistrations(
            $opportunity, number_of_registrations: 2,
            proponent_type: ProponentTypes::MEI->value, range: 'Faixa A'
        );
        foreach ($meiFaixaA as $registration) {
            $registration->setStatus(10);
        }

        $combined = $this->fetchStaticCharts($opportunity, [
            'status' => 'approved',
            'proponentType' => ProponentTypes::PESSOA_FISICA->value,
            'range' => 'Faixa A',
        ]);

        $this->assertEquals(
            3,
            array_sum($combined['registrationsByStatus']['data']),
            'a combinação Pessoa Física + Faixa A deve contar só as 3 inscrições que atendem aos dois filtros ao mesmo tempo'
        );
    }

    // =================== GET /reports/reportFields ===================

    function testReportFieldsIncludesProponentTypeAndRangeWhenConfigured()
    {
        $opportunity = $this->createOpportunityWithProponentTypesAndRanges();

        $fields = $this->callReportsEndpoint('GET', 'reportFields', ['opportunity_id' => $opportunity->id]);
        $values = array_column($fields, 'value');

        $this->assertContains(
            'proponent_type',
            $values,
            'Tipo de proponente deve estar disponível no construtor de gráficos quando a oportunidade tem tipos configurados'
        );
        $this->assertContains(
            'range',
            $values,
            'Faixa/Linha deve estar disponível no construtor de gráficos quando a oportunidade tem faixas configuradas'
        );
    }

    function testReportFieldsOmitsProponentTypeAndRangeWhenNotConfigured()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $fields = $this->callReportsEndpoint('GET', 'reportFields', ['opportunity_id' => $opportunity->id]);
        $values = array_column($fields, 'value');

        $this->assertNotContains(
            'proponent_type',
            $values,
            'Tipo de proponente não deve aparecer quando a oportunidade não usa tipos de proponente'
        );
        $this->assertNotContains(
            'range',
            $values,
            'Faixa/Linha não deve aparecer quando a oportunidade não usa faixas'
        );
    }

    // =================== POST /reports/saveGraphic (regressão do bug de dedupe) ===================

    function testSaveGraphicDoesNotCollideBetweenDifferentOpportunities()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunityA = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()->setRegistrationPeriod(new Open)->done()
            ->save()->refresh()->getInstance();

        $opportunityB = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()->setRegistrationPeriod(new Open)->done()
            ->save()->refresh()->getInstance();

        $basePayload = [
            'typeGraphic' => 'pie',
            'columns' => [
                ['label' => 'Status', 'value' => 'status', 'source' => ['table' => 'r', 'type' => 'status']],
            ],
            'title' => 'Gráfico de teste',
            'description' => '',
            'fields' => 'Status',
            'groupData' => false,
            'status' => 'all',
        ];

        // salva o MESMO gráfico (mesmas colunas/tipo) em duas oportunidades diferentes
        foreach ([$opportunityA, $opportunityB] as $opportunity) {
            $this->callReportsEndpoint('POST', 'saveGraphic', payload: array_merge($basePayload, [
                'opportunity_id' => $opportunity->id,
            ]));
        }

        $graphicsA = $this->callReportsEndpoint('GET', 'graphics', ['opportunity_id' => $opportunityA->id, 'status' => 'all']);
        $graphicsB = $this->callReportsEndpoint('GET', 'graphics', ['opportunity_id' => $opportunityB->id, 'status' => 'all']);

        $this->assertCount(1, $graphicsA, 'oportunidade A deve ter exatamente 1 gráfico salvo');
        $this->assertCount(1, $graphicsB, 'oportunidade B deve ter exatamente 1 gráfico salvo');
        $this->assertNotEquals(
            $graphicsA[0]['reportData']['graphicId'],
            $graphicsB[0]['reportData']['graphicId'],
            'gráficos com a mesma definição em oportunidades diferentes não devem colidir (bug antigo de dedupe por LIKE global)'
        );
    }

    function testSaveGraphicUpdatesTheSameRecordOnSecondSaveInSameOpportunity()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()->setRegistrationPeriod(new Open)->done()
            ->save()->refresh()->getInstance();

        $payload = [
            'opportunity_id' => $opportunity->id,
            'typeGraphic' => 'pie',
            'columns' => [
                ['label' => 'Status', 'value' => 'status', 'source' => ['table' => 'r', 'type' => 'status']],
            ],
            'title' => 'Gráfico de teste',
            'description' => '',
            'fields' => 'Status',
            'groupData' => false,
            'status' => 'all',
        ];

        $first = $this->callReportsEndpoint('POST', 'saveGraphic', payload: $payload);
        $second = $this->callReportsEndpoint('POST', 'saveGraphic', payload: $payload);

        $this->assertEquals(
            $first['graphicId'],
            $second['graphicId'],
            'salvar novamente a mesma definição de gráfico deve atualizar o registro existente, não criar um novo'
        );

        $graphics = $this->callReportsEndpoint('GET', 'graphics', ['opportunity_id' => $opportunity->id, 'status' => 'all']);
        $this->assertCount(1, $graphics, 'não deve haver duplicata do gráfico após salvar duas vezes a mesma definição');
    }

    function testDeleteGraphicRemovesIt()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()->setRegistrationPeriod(new Open)->done()
            ->save()->refresh()->getInstance();

        $saved = $this->callReportsEndpoint('POST', 'saveGraphic', payload: [
            'opportunity_id' => $opportunity->id,
            'typeGraphic' => 'pie',
            'columns' => [
                ['label' => 'Status', 'value' => 'status', 'source' => ['table' => 'r', 'type' => 'status']],
            ],
            'title' => 'Gráfico a excluir',
            'description' => '',
            'fields' => 'Status',
            'groupData' => false,
            'status' => 'all',
        ]);

        $app = App::i();
        $request = $this->requestFactory->DELETE(
            controller_id: 'reports',
            action: 'deleteGraphic',
            payload: ['opportunity_id' => $opportunity->id, 'graphicId' => $saved['graphicId']],
            ajax: true
        );
        $app->reset();
        $app->run($request, false);
        $this->assertEquals(200, $app->response->getStatusCode());

        $graphics = $this->callReportsEndpoint('GET', 'graphics', ['opportunity_id' => $opportunity->id, 'status' => 'all']);
        $this->assertCount(0, $graphics, 'o gráfico excluído não deve mais aparecer na lista');
    }
}
