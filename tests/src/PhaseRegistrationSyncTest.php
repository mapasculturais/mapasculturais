<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions\Halt;
use OpportunityPhases\Module as OpportunityPhasesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class PhaseRegistrationSyncTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RequestFactory,
        UserDirector;

    /**
     * Garante que o sync em massa para Anexos importe apenas inscrições selecionadas e remova inscrições órfãs.
     */
    function testMassSyncToAnexosImportsOnlySelectedAndRemovesOrphans(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        /** @var OpportunityPhasesModule $module */
        $module = $this->getOpportunityPhasesModule();
        $this->app->disableAccessControl();
        $module->createPhaseRegistration(
            $context->anexos,
            $this->findRegistration($context->coletaMerito, '444')
        );
        $this->app->enableAccessControl();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que a inscrição 444 exista em Anexos antes do sync em massa (simulação do bug de produção)'
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que a inscrição selecionada (333) seja importada para Anexos após o sync em massa'
        );
        $this->assertEquals(
            Registration::STATUS_DRAFT,
            $this->findRegistration($context->anexos, '333')->status,
            'Garantindo que a inscrição selecionada entre em Anexos como rascunho'
        );

        foreach (['111', '222', '444', '555', '666'] as $number) {
            $this->assertNull(
                $this->findRegistration($context->anexos, $number),
                "Garantindo que a inscrição {$number} não permaneça em Anexos após o sync em massa"
            );
        }
    }

    /**
     * Garante que a publicação final exiba apenas inscrições enviadas e com consolidatedResult correto da fase de mérito.
     */
    function testPublicationFinalSyncLabels(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);
        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $module = $this->getOpportunityPhasesModule();
        $meritoName = $context->coletaMerito->evaluationMethodConfiguration->name;

        $this->assertNull(
            $this->findRegistration($context->publicacao, '111'),
            'Garantindo que a inscrição em rascunho (111) não apareça na publicação final'
        );

        $expectedByNumber = [
            '222' => Registration::STATUS_SENT,
            '444' => Registration::STATUS_NOTAPPROVED,
            '555' => Registration::STATUS_WAITLIST,
            '666' => Registration::STATUS_INVALID,
        ];

        foreach ($expectedByNumber as $number => $status) {
            $publication = $this->findRegistration($context->publicacao, $number);
            $this->assertNotNull(
                $publication,
                "Garantindo que a inscrição {$number} apareça na publicação final"
            );

            [, $labelTemplate] = $module->getRegistrationStatusLabels($status);
            $expected = str_replace('{PHASE_NAME}', $meritoName, $labelTemplate);
            $this->assertEquals(
                $expected,
                $publication->consolidatedResult,
                "Garantindo que a inscrição {$number} tenha o consolidatedResult correto na publicação final"
            );
        }

        $this->assertNotNull(
            $this->findRegistration($context->publicacao, '333'),
            'Garantindo que a inscrição selecionada (333) apareça na publicação final'
        );
    }

    /**
     * Garante que inscrição selecionada com recurso deferido retorne à fase Anexos após sair dela ao abrir o recurso.
     */
    function testSelectedRegistrationAppealDeferredReturnsToAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que a inscrição selecionada (333) esteja em Anexos antes da abertura do recurso'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '333')
        );
        $this->assertNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que a inscrição selecionada seja removida de Anexos ao abrir recurso'
        );

        $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que a inscrição selecionada retorne a Anexos após recurso deferido'
        );
    }

    /**
     * Garante que inscrição selecionada com recurso indeferido permaneça selecionada e volte a Anexos pelo mérito.
     */
    function testSelectedRegistrationAppealIndeferredReturnsToAnexosViaMerit(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $reg333 = $this->findRegistration($context->coletaMerito, '333');
        $appeal = $this->createAppealRegistration($reg333);
        $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToNotApproved(true));

        $this->assertEquals(
            Registration::STATUS_APPROVED,
            $reg333->refreshed()->status,
            'Garantindo que recurso indeferido não altere o status selecionado na fase de origem'
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que inscrição selecionada com recurso indeferido volte a Anexos pelo mérito'
        );
    }

    /**
     * Garante que inscrição não selecionada sem recurso não seja sincronizada para Anexos.
     */
    function testNotSelectedWithoutAppealStaysOutOfAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);
        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que inscrição não selecionada sem recurso não seja sincronizada para Anexos'
        );
    }

    /**
     * Garante que inscrição não selecionada com recurso deferido seja sincronizada para Anexos.
     */
    function testNotSelectedAppealDeferredSyncsToAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que inscrição não selecionada não esteja em Anexos antes do recurso deferido'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '444')
        );
        $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que inscrição não selecionada entre em Anexos após recurso deferido'
        );
    }

    /**
     * Garante que inscrição não selecionada com recurso indeferido não seja sincronizada para Anexos.
     */
    function testNotSelectedAppealIndeferredStaysOutOfAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '444')
        );
        $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToNotApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que inscrição não selecionada com recurso indeferido não entre em Anexos'
        );
    }

    /**
     * Garante que inscrições suplente e inválida sigam as mesmas regras de sync de Anexos que a não selecionada.
     */
    function testWaitlistAndInvalidFollowSameAnexosRulesAsNotSelected(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        foreach (['555', '666'] as $number) {
            $this->assertNull(
                $this->findRegistration($context->anexos, $number),
                "Garantindo que a inscrição {$number} não vá direto para Anexos sem recurso deferido"
            );
        }

        foreach (['555', '666'] as $number) {
            $appeal = $this->createAppealRegistration(
                $this->findRegistration($context->coletaMerito, $number)
            );
            $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));
        }

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        foreach (['555', '666'] as $number) {
            $this->assertNotNull(
                $this->findRegistration($context->anexos, $number),
                "Garantindo que a inscrição {$number} entre em Anexos após recurso deferido"
            );
        }
    }

    /**
     * Garante que recurso pendente bloqueie o sync e remova inscrição órfã de Anexos.
     */
    function testPendingAppealBlocksAnexosSync(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $module = $this->getOpportunityPhasesModule();
        $this->app->disableAccessControl();
        $module->createPhaseRegistration(
            $context->anexos,
            $this->findRegistration($context->coletaMerito, '444')
        );
        $this->app->enableAccessControl();

        $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '444')
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que recurso pendente remova inscrição não selecionada órfã de Anexos'
        );
    }

    private function buildPnabLikeOpportunity(): object
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new Open)
                ->save()
                ->done();

        $coletaMerito = $this->opportunityBuilder->getInstance()->firstPhase;
        $coletaMerito->evaluationMethodConfiguration->name = 'Avaliação de Mérito Cultural';
        $coletaMerito->evaluationMethodConfiguration->save(true);

        $this->createAppealPhaseForOpportunity($coletaMerito);

        $edital = $this->opportunityBuilder
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new Open)
                ->save()
                ->done()
            ->refresh()
            ->getInstance();

        $coletaMerito = $edital->firstPhase->refreshed();
        $anexos = OpportunityPhasesModule::getNextMainPhase($coletaMerito);
        $publicacao = $edital->lastPhase;

        return (object) [
            'admin' => $admin,
            'edital' => $edital,
            'coletaMerito' => $coletaMerito,
            'anexos' => $anexos,
            'publicacao' => $publicacao,
        ];
    }

    /** @return array<string, Registration> */
    private function createRegistrationsByStatus(Opportunity $coletaMerito): array
    {
        $draft = $this->registrationDirector->createDraftRegistrations($coletaMerito, 1)[0];

        $pending = $this->registrationDirector->createSentRegistration($coletaMerito, []);

        $selected = $this->registrationDirector->createSentRegistration($coletaMerito, []);
        $selected->setStatusToApproved(true);

        $notSelected = $this->registrationDirector->createSentRegistration($coletaMerito, []);
        $notSelected->setStatusToNotApproved(true);

        $waitlist = $this->registrationDirector->createSentRegistration($coletaMerito, []);
        $waitlist->setStatusToWaitlist(true);

        $invalid = $this->registrationDirector->createSentRegistration($coletaMerito, []);
        $invalid->setStatusToInvalid(true);

        return [
            '111' => $this->setRegistrationNumber($draft, '111'),
            '222' => $this->setRegistrationNumber($pending, '222'),
            '333' => $this->setRegistrationNumber($selected->refreshed(), '333'),
            '444' => $this->setRegistrationNumber($notSelected->refreshed(), '444'),
            '555' => $this->setRegistrationNumber($waitlist->refreshed(), '555'),
            '666' => $this->setRegistrationNumber($invalid->refreshed(), '666'),
        ];
    }

    private function setRegistrationNumber(Registration $registration, string $number): Registration
    {
        $this->app->conn->executeStatement(
            'UPDATE registration SET number = :number WHERE id = :id',
            ['number' => $number, 'id' => $registration->id]
        );
        $registration->number = $number;

        return $registration;
    }

    private function findRegistration(Opportunity $phase, string $number): ?Registration
    {
        return $this->app->repo('Registration')->findOneBy([
            'opportunity' => $phase->id,
            'number' => $number,
        ]);
    }

    private function refreshContext(object $context): object
    {
        $context->coletaMerito = $this->app->repo('Opportunity')->find($context->coletaMerito->id);
        $context->anexos = $this->app->repo('Opportunity')->find($context->anexos->id);
        $context->publicacao = $this->app->repo('Opportunity')->find($context->publicacao->id);

        return $context;
    }

    private function createAppealPhaseForOpportunity(Opportunity $opportunity): Opportunity
    {
        $app = $this->app;
        $opportunityId = $opportunity->id;

        $app->request = $this->requestFactory->mapasPOST(
            'opportunity',
            'createAppealPhase',
            [$opportunityId],
            ['id' => $opportunityId]
        );
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $opportunityId]);
        try {
            $controller->callAction('POST', 'createAppealPhase', []);
        } catch (Halt) {
        }

        return $opportunity->refreshed()->appealPhase;
    }

    private function createAppealRegistration(Registration $registration): Registration
    {
        $app = $this->app;
        $registration = $this->findRegistration(
            $app->repo('Opportunity')->find($registration->opportunity->id),
            $registration->number
        );
        $appealPhase = $registration->opportunity->appealPhase;

        $existing = $app->repo('Registration')->findOneBy([
            'opportunity' => $appealPhase,
            'number' => $registration->number,
        ]);

        if ($existing) {
            return $existing;
        }

        $app->disableAccessControl();
        try {
            $appeal = new Registration();
            $appeal->opportunity = $appealPhase;
            $appeal->category = $registration->category;
            $appeal->proponentType = $registration->proponentType;
            $appeal->range = $registration->range;
            $appeal->owner = $registration->owner;
            $appeal->number = $registration->number;
            $appeal->save(true);

            OpportunityPhasesModule::removeDownstreamRegistrations($registration);

            return $appeal->refreshed();
        } finally {
            $app->enableAccessControl();
        }
    }

    private function setAppealStatus(Registration $appeal, callable $setter): Registration
    {
        $this->app->disableAccessControl();
        try {
            $setter($appeal);
            return $appeal->refreshed();
        } finally {
            $this->app->enableAccessControl();
        }
    }

    /**
     * Garante que inscrição pendente (status 1) em coleta sem avaliação não avance para a próxima fase de coleta.
     */
    function testPendingRegistrationDoesNotSyncBetweenDataCollectionPhases(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $edital = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $fase1 = $edital->firstPhase;
        $fase2 = OpportunityPhasesModule::getNextMainPhase($fase1);

        $pending = $this->registrationDirector->createSentRegistration($fase1, []);
        $pending->number = '999';
        $pending->save(true);

        $selected = $this->registrationDirector->createSentRegistration($fase1, []);
        $selected->number = '888';
        $selected->setStatusToApproved(true);

        $fase2->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($fase2, '999'),
            'Garantindo que inscrição pendente (status 1) não avance para a próxima fase de coleta'
        );

        $this->assertNotNull(
            $this->findRegistration($fase2, '888'),
            'Garantindo que inscrição selecionada (status 10) avance para a próxima fase de coleta'
        );
    }

    private function getOpportunityPhasesModule(): OpportunityPhasesModule
    {
        return $this->app->modules['OpportunityPhases'];
    }
}
