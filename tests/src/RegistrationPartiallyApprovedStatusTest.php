<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Garante o status de inscrição deferido parcialmente (9) no core: nome, chave, gravação, hook, rota e visibilidade.
 */
class RegistrationPartiallyApprovedStatusTest extends Abstract\TestCase
{
    use RequestFactory,
        UserDirector,
        OpportunityBuilder,
        RegistrationDirector;

    function testPartiallyApprovedStatusHasNameAndKey()
    {
        $this->assertSame(9, Registration::STATUS_PARTIALLY_APPROVED, 'Garantindo que o deferido parcialmente seja o status 9');

        $this->assertSame(
            'Deferido parcialmente',
            Registration::getStatusNameById(Registration::STATUS_PARTIALLY_APPROVED),
            'Garantindo o nome do status deferido parcialmente'
        );

        $this->assertSame(
            'PartiallyApproved',
            Registration::getStatuses()[Registration::STATUS_PARTIALLY_APPROVED],
            'Garantindo a chave do status deferido parcialmente'
        );
    }

    function testSetStatusStoresPartiallyApprovedAndFiresHook()
    {
        [, $registration] = $this->createOpportunityWithSentRegistration();

        $hook_calls = 0;
        $this->app->hook('entity(Registration).status(partiallyapproved)', function () use (&$hook_calls) {
            $hook_calls++;
        });

        $this->app->disableAccessControl();
        try {
            $registration->setStatus(Registration::STATUS_PARTIALLY_APPROVED);
            $registration->save(true);
        } finally {
            $this->app->enableAccessControl();
        }

        $this->assertSame(
            Registration::STATUS_PARTIALLY_APPROVED,
            $registration->refreshed()->status,
            'Garantindo que setStatus grave o status deferido parcialmente'
        );

        $this->assertSame(1, $hook_calls, 'Garantindo que o status deferido parcialmente dispare o hook próprio');
    }

    function testManagerSetsPartiallyApprovedThroughSetStatusToRoute()
    {
        [, $registration] = $this->createOpportunityWithSentRegistration();

        $request = $this->requestFactory->POST(
            controller_id: 'registration',
            action: 'setStatusTo',
            url_params: [$registration->id],
            payload: ['status' => Registration::STATUS_PARTIALLY_APPROVED]
        );

        $this->assertStatus200($request, 'Garantindo que o gestor consiga aplicar o deferido parcialmente pela rota setStatusTo');

        $this->assertSame(
            Registration::STATUS_PARTIALLY_APPROVED,
            $registration->refreshed()->status,
            'Garantindo que a rota setStatusTo grave o status deferido parcialmente'
        );
    }

    function testPartiallyApprovedRegistrationHasSameVisibilityAsApproved()
    {
        [, $registration] = $this->createOpportunityWithSentRegistration();

        $this->app->disableAccessControl();
        try {
            $registration->setStatusToPartiallyApproved();
        } finally {
            $this->app->enableAccessControl();
        }

        $visitor = $this->userDirector->createUser();
        $this->login($visitor);

        $json = $this->app->repo('Registration')->find($registration->id)->jsonSerialize();

        $this->assertIsArray($json, 'Garantindo que a inscrição deferida parcialmente seja serializada para quem não a pode ver');
        $this->assertArrayHasKey(
            'agentRelations',
            $json,
            'Garantindo que a inscrição deferida parcialmente exponha os mesmos dados públicos da selecionada'
        );
    }

    function testPublishRegistrationsIncludesPartiallyApproved()
    {
        [$opportunity, $registration] = $this->createOpportunityWithSentRegistration();

        $this->app->disableAccessControl();
        try {
            $registration->setStatusToPartiallyApproved();
        } finally {
            $this->app->enableAccessControl();
        }

        $published = [];
        $this->app->hook('entity(Opportunity).publishRegistration', function ($published_registration) use (&$published) {
            $published[] = $published_registration->id;
        });

        $opportunity = $this->app->repo('Opportunity')->find($opportunity->id);
        $opportunity->publishRegistrations();

        $this->assertContains(
            $registration->id,
            $published,
            'Garantindo que a publicação do resultado trate o deferido parcialmente como o selecionado'
        );
    }

    function testContinuousBatchApplicationSetsPartiallyApprovedAndFiresHook()
    {
        [$opportunity, $registration] = $this->createContinuousOpportunityWithResult('9', [
            Registration::STATUS_DRAFT => 'Rascunho',
            Registration::STATUS_SENT => 'Aguardando resposta',
            Registration::STATUS_INVALID => 'Negado',
            Registration::STATUS_NOTAPPROVED => 'Indeferido',
            Registration::STATUS_PARTIALLY_APPROVED => 'Deferido parcialmente',
            Registration::STATUS_APPROVED => 'Deferido',
        ]);

        $hook_calls = 0;
        $this->app->hook('entity(Registration).status(partiallyapproved)', function () use (&$hook_calls) {
            $hook_calls++;
        });

        $request = $this->createBatchApplicationRequest($opportunity, '9', Registration::STATUS_PARTIALLY_APPROVED);

        // Descarta o log do endpoint durante a requisição.
        $this->app->log->pushHandler(new \Monolog\Handler\NullHandler());
        try {
            $this->assertStatus200($request, 'Garantindo que a aplicação em lote do método contínuo aceite o deferido parcialmente');
        } finally {
            $this->app->log->popHandler();
        }

        $this->assertSame(
            Registration::STATUS_PARTIALLY_APPROVED,
            $this->app->repo('Registration')->find($registration->id)->status,
            'Garantindo que a aplicação em lote grave o status deferido parcialmente'
        );

        $this->assertSame(1, $hook_calls, 'Garantindo que a aplicação em lote dispare o hook do deferido parcialmente');
    }

    function testContinuousBatchApplicationRejectsPartiallyApprovedWhenPhaseDisablesIt()
    {
        [$opportunity, $registration] = $this->createContinuousOpportunityWithResult('9');

        $request = $this->createBatchApplicationRequest($opportunity, '9', Registration::STATUS_PARTIALLY_APPROVED);

        $this->assertStatus400($request, 'Garantindo que a aplicação em lote recuse o deferido parcialmente desabilitado na fase');

        $this->assertSame(
            Registration::STATUS_SENT,
            $this->app->repo('Registration')->find($registration->id)->status,
            'Garantindo que a aplicação em lote recusada não altere o status da inscrição'
        );
    }

    function testContinuousBatchApplicationRejectsAnyStatusDisabledInPhase()
    {
        [$opportunity, $registration] = $this->createContinuousOpportunityWithResult('3', [
            Registration::STATUS_DRAFT => 'Rascunho',
            Registration::STATUS_SENT => 'Aguardando resposta',
            Registration::STATUS_NOTAPPROVED => 'Indeferido',
            Registration::STATUS_APPROVED => 'Deferido',
        ]);

        $request = $this->createBatchApplicationRequest($opportunity, '3', Registration::STATUS_INVALID);

        $this->assertStatus400($request, 'Garantindo que a aplicação em lote recuse o Negado desabilitado na fase');

        $this->assertSame(
            Registration::STATUS_SENT,
            $this->app->repo('Registration')->find($registration->id)->status,
            'Garantindo que a aplicação em lote recusada não altere o status da inscrição'
        );
    }

    function testSpreadsheetsNamePartiallyApprovedStatus()
    {
        $registrations = (new \ReflectionClass(\Spreadsheets\JobTypes\Registrations::class))->newInstanceWithoutConstructor();
        $evaluations = (new \ReflectionClass(\EvaluationMethodContinuous\JobTypes\Spreadsheet::class))
            ->newInstanceWithoutConstructor();

        $this->assertSame(
            'Deferido parcialmente',
            $registrations->getStatusName(Registration::STATUS_PARTIALLY_APPROVED),
            'Garantindo o nome do deferido parcialmente na planilha de inscrições'
        );

        $this->assertSame(
            'Deferido parcialmente',
            $evaluations->statusName(Registration::STATUS_PARTIALLY_APPROVED),
            'Garantindo o nome do deferido parcialmente na planilha de avaliações'
        );
    }

    function testReportCountsPartiallyApprovedRegistrations()
    {
        [$opportunity, $registration] = $this->createOpportunityWithSentRegistration();

        $this->app->disableAccessControl();
        try {
            $registration->setStatusToPartiallyApproved();
        } finally {
            $this->app->enableAccessControl();
        }

        $data = $this->app->modules['Reports']->registrationsByStatus($opportunity);

        $this->assertEquals(
            1,
            $data['Deferido parcialmente'] ?? null,
            'Garantindo que o relatório por status conte as inscrições deferidas parcialmente'
        );
    }

    function testKeywordSearchFindsPartiallyApprovedResult()
    {
        $where = '';
        $this->app->applyHook('repo(Registration).getIdsByKeywordDQL.where', [&$where, 'Deferido parcialmente', 'keyword']);

        $this->assertStringContainsString(
            "e.consolidatedResult = '9'",
            $where,
            'Garantindo que a busca por "deferido parcialmente" encontre o resultado 9'
        );
    }

    /**
     * Cria uma oportunidade de avaliação contínua com uma inscrição enviada e o resultado consolidado informado
     *
     * @return array{Opportunity, Registration}
     */
    private function createContinuousOpportunityWithResult(string $consolidated_result, ?array $status_labels = null): array
    {
        [$opportunity, $registration] = $this->createOpportunityWithSentRegistration();

        $this->app->disableAccessControl();
        try {
            $opportunity->evaluationMethodConfiguration->type = 'continuous';
            $opportunity->evaluationMethodConfiguration->save(true);

            if ($status_labels !== null) {
                $opportunity = $opportunity->refreshed();
                $opportunity->statusLabels = $status_labels;
                $opportunity->save(true);
            }
        } finally {
            $this->app->enableAccessControl();
        }

        $this->app->conn->executeStatement(
            'UPDATE registration SET consolidated_result = :result WHERE id = :id',
            ['result' => $consolidated_result, 'id' => $registration->id]
        );
        $this->app->em->clear();

        return [$opportunity->refreshed(), $registration];
    }

    /**
     * Monta a requisição de aplicação em lote do método contínuo
     */
    private function createBatchApplicationRequest(Opportunity $opportunity, string $from, int $to)
    {
        return $this->requestFactory->POST(
            controller_id: 'opportunity',
            action: 'applyEvaluationsContinuous',
            url_params: [$opportunity->id],
            payload: ['from' => $from, 'to' => $to, 'status' => 'all']
        );
    }

    /**
     * Cria uma oportunidade com fase de avaliação e uma inscrição enviada, com o administrador logado
     *
     * @return array{Opportunity, Registration}
     */
    private function createOpportunityWithSentRegistration(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->getInstance();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        return [$opportunity->refreshed(), $registration->refreshed()];
    }
}
