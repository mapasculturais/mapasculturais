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
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testSelectedRegistrationAppealDeferredReturnsToAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
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
        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que a inscrição selecionada permaneça em Anexos com recurso em rascunho'
        );

        $appeal = $this->sendAppealRegistration($appeal);
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
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testSelectedRegistrationAppealIndeferredReturnsToAnexosViaMerit(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
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
     * Garante que inscrição pendente não possa solicitar recurso por padrão.
     */
    function testPendingRegistrationAppealRequestBlockedByDefault(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $pending = $this->findRegistration($context->coletaMerito, '222');

        $this->assertCreateAppealPhaseRegistrationStatus(
            $pending,
            403,
            'Garantindo que recurso para inscrição pendente seja bloqueado por padrão'
        );

        $this->assertNull(
            $this->findRegistration($context->coletaMerito->appealPhase, '222'),
            'Garantindo que nenhuma inscrição de recurso seja criada para pendente com configuração desligada'
        );
    }

    /**
     * Garante que inscrição pendente possa solicitar recurso quando habilitado,
     * mas o deferimento registre apenas a decisão sem avançar para Anexos.
     */
    function testPendingRegistrationAppealApprovalOnlyRecordsDecision(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
        $this->setAllowPendingRegistrationsAppeal($context->coletaMerito, true);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $pending = $this->findRegistration($context->coletaMerito, '222');

        $this->assertCreateAppealPhaseRegistrationStatus(
            $pending,
            200,
            'Garantindo que recurso para inscrição pendente seja permitido quando configurado'
        );

        $appeal = $this->findRegistration($context->coletaMerito->appealPhase, '222');
        $this->assertNotNull(
            $appeal,
            'Garantindo que inscrição de recurso seja criada para pendente quando configurado'
        );

        $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertEquals(
            Registration::STATUS_SENT,
            $pending->refreshed()->status,
            'Garantindo que o deferimento do recurso não altera o status pendente na fase de origem'
        );
        $this->assertNull(
            $this->findRegistration($context->anexos, '222'),
            'Garantindo que pendente com recurso deferido não avance para Anexos'
        );
    }

    /**
     * Garante que inscrição não selecionada com recurso deferido seja sincronizada para Anexos.
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testNotSelectedAppealDeferredSyncsToAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
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
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testNotSelectedAppealIndeferredStaysOutOfAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
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
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testWaitlistAndInvalidFollowSameAnexosRulesAsNotSelected(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
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
     * Garante alinhamento dos ponteiros next/previous entre fases principais em cenários variados:
     * sync normal, re-sync com elo corrompido, recurso pendente e pós-recurso, até publicação final.
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testPhaseRegistrationPointersStayAlignedAcrossMultiplePhases(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        // 1. Sync inicial mérito → anexos
        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após sync inicial para Anexos: '
        );

        // 2. Sync completo até publicação final (várias fases principais)
        $context->publicacao->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após sync até publicação final: '
        );
        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '222',
            'Inscrição pendente (sem Anexos) após sync até publicação: '
        );

        // 3. Re-sync com ponteiro corrompido (simula metadata inconsistente de produção)
        $this->breakPhasePointer(
            $this->findRegistration($context->coletaMerito, '333'),
            'next'
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após re-sync com next corrompido: '
        );

        // 4. Re-sync com inscrição já existente na fase destino (não recria, deve reparar elos)
        $this->breakPhasePointer(
            $this->findRegistration($context->coletaMerito, '333'),
            'next'
        );
        $this->breakPhasePointer(
            $this->findRegistration($context->anexos, '333'),
            'previous'
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após re-sync com inscrição pré-existente em Anexos: '
        );

        // 5. Recurso: durante pendência o next deve apontar para a inscrição de recurso (não id deletado/nulo)
        $merit333 = $this->findRegistration($context->coletaMerito, '333');
        $appeal = $this->createAppealRegistration($merit333);
        $appeal = $this->sendAppealRegistration($appeal);
        $merit333 = $merit333->refreshed();

        $this->assertEquals(
            $appeal->id,
            $merit333->nextPhaseRegistrationId,
            'Durante recurso pendente: next deve apontar para inscrição de recurso'
        );
        $this->assertNull(
            $this->findRegistration($context->anexos, '333'),
            'Durante recurso pendente: inscrição removida de Anexos'
        );
        $this->assertRegistrationDoesNotPointToMissingId($merit333);

        // 6. Pós-recurso deferido: cadeia principal restaurada em todas as fases
        $appeal = $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após recurso deferido e sync: '
        );

        $context->publicacao->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $context->coletaMerito,
            '333',
            'Após recurso deferido e sync até publicação: '
        );
    }

    /**
     * Garante alinhamento dos ponteiros em sync entre fases comuns de coleta de dados (sem recurso).
     */
    function testDataCollectionPhaseRegistrationPointersAlignedAfterSync(): void
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

        $this->assertMainPhaseChainAligned($fase1, '888', 'Sync entre fases de coleta: ');

        $this->breakPhasePointer($this->findRegistration($fase1, '888'), 'next');
        $fase2->syncRegistrations([]);
        $this->processJobs();

        $this->assertMainPhaseChainAligned(
            $fase1,
            '888',
            'Re-sync entre fases de coleta com ponteiro corrompido: '
        );
    }

    /**
     * Garante que recurso pendente bloqueie o sync e remova inscrição órfã de Anexos.
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testPendingAppealBlocksAnexosSync(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $module = $this->getOpportunityPhasesModule();
        $this->app->disableAccessControl();
        $module->createPhaseRegistration(
            $context->anexos,
            $this->findRegistration($context->coletaMerito, '444')
        );
        $this->app->enableAccessControl();

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '444')
        );
        $this->sendAppealRegistration($appeal);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Garantindo que recurso pendente remova inscrição não selecionada órfã de Anexos'
        );
    }

    /**
     * Garante que recurso em rascunho não bloqueie inscrição selecionada em Anexos.
     *
     * Modo appealPhaseAffectsSync = true: o recurso afeta ativamente a sincronização.
     */
    function testDraftAppealDoesNotBlockSelectedInAnexos(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, true);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '333')
        );

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Garantindo que inscrição selecionada permaneça em Anexos com recurso em rascunho após sync'
        );
    }

    /**
     * Modo neutro (appealPhaseAffectsSync = false): inscrição selecionada com recurso enviado
     * permanece na fase seguinte — o recurso NÃO remove a inscrição de Anexos.
     */
    function testNeutralMode_SelectedWithAppealSent_StaysInDownstreamPhase(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, false);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Pré-condição: inscrição selecionada (333) sincronizada para Anexos'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '333')
        );
        $this->sendAppealRegistration($appeal);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Modo neutro: inscrição selecionada permanece em Anexos mesmo com recurso enviado (não é removida)'
        );
    }

    /**
     * Modo neutro (appealPhaseAffectsSync = false): inscrição selecionada com recurso deferido
     * permanece na fase seguinte — o deferimento não dispara re-sync porque a inscrição nunca saiu.
     */
    function testNeutralMode_SelectedWithAppealDeferred_StaysInDownstreamPhase(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, false);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Pré-condição: inscrição selecionada (333) sincronizada para Anexos'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '333')
        );
        $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Modo neutro: inscrição selecionada permanece em Anexos após recurso deferido (nunca saiu)'
        );
    }

    /**
     * Modo neutro (appealPhaseAffectsSync = false): inscrição selecionada com recurso indeferido
     * permanece na fase seguinte — o indeferimento não altera o avanço por mérito.
     */
    function testNeutralMode_SelectedWithAppealRejected_StaysInDownstreamPhase(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, false);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Pré-condição: inscrição selecionada (333) sincronizada para Anexos'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '333')
        );
        $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToNotApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNotNull(
            $this->findRegistration($context->anexos, '333'),
            'Modo neutro: inscrição selecionada permanece em Anexos após recurso indeferido (nunca saiu)'
        );
    }

    /**
     * Modo neutro (appealPhaseAffectsSync = false): inscrição não selecionada com recurso deferido
     * NÃO avança para a fase seguinte — o deferimento não qualifica sem seleção prévia por mérito.
     */
    function testNeutralMode_NonSelectedWithAppealDeferred_DoesNotAdvance(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, false);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();
        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Pré-condição: inscrição não selecionada (444) não está em Anexos'
        );

        $appeal = $this->createAppealRegistration(
            $this->findRegistration($context->coletaMerito, '444')
        );
        $this->setAppealStatus($appeal, fn ($r) => $r->setStatusToApproved(true));

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Modo neutro: inscrição não selecionada não avança para Anexos mesmo com recurso deferido'
        );
    }

    /**
     * Modo neutro (appealPhaseAffectsSync = false): inscrição não selecionada sem recurso
     * NÃO avança para a fase seguinte — apenas a seleção por mérito qualifica.
     */
    function testNeutralMode_NonSelectedWithoutAppeal_DoesNotAdvance(): void
    {
        $context = $this->buildPnabLikeOpportunity();
        $this->setAppealPhaseAffectsSync($context->coletaMerito, false);
        $this->createRegistrationsByStatus($context->coletaMerito);
        $context = $this->refreshContext($context);

        $context->anexos->syncRegistrations([]);
        $this->processJobs();

        $this->assertNull(
            $this->findRegistration($context->anexos, '444'),
            'Modo neutro: inscrição não selecionada sem recurso não avança para Anexos'
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

    /**
     * Define o metadata appealPhaseAffectsSync na fase de mérito (parent da fase de recurso).
     *
     * A flag é lida a partir da fase principal de origem ($previous_phase / $opportunity->parent),
     * conforme OpportunityPhases\Module::appealPhaseAffectsSync().
     */
    private function setAppealPhaseAffectsSync(Opportunity $meritPhase, bool $enabled): void
    {
        $this->app->disableAccessControl();
        try {
            $appealPhase = $meritPhase->appealPhase;
            if ($appealPhase) {
                $appealPhase->appealPhaseAffectsSync = $enabled;
                $appealPhase->save(true);
            }
        } finally {
            $this->app->enableAccessControl();
        }
    }

    private function setAllowPendingRegistrationsAppeal(Opportunity $meritPhase, bool $enabled): void
    {
        $this->app->disableAccessControl();
        try {
            $appealPhase = $meritPhase->appealPhase;
            if ($appealPhase) {
                $appealPhase->allowPendingRegistrationsAppeal = $enabled;
                $appealPhase->save(true);
            }
        } finally {
            $this->app->enableAccessControl();
        }
    }

    private function assertCreateAppealPhaseRegistrationStatus(Registration $registration, int $status, string $message): void
    {
        $request = $this->requestFactory->POST(
            'opportunity',
            'createAppealPhaseRegistration',
            [$registration->opportunity->id],
            ['registration_id' => $registration->id]
        );

        $this->assertHttpStatusCode($request, $status, $message);
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

            return $appeal->refreshed();
        } finally {
            $app->enableAccessControl();
        }
    }

    private function sendAppealRegistration(Registration $appeal): Registration
    {
        $this->app->disableAccessControl();
        try {
            $appeal->send(false);
            return $appeal->refreshed();
        } finally {
            $this->app->enableAccessControl();
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

    /** @return Opportunity[] */
    private function getMainPhasesFrom(Opportunity $first_phase): array
    {
        $phases = [$first_phase];

        foreach (OpportunityPhasesModule::getDownstreamMainPhases($first_phase) as $phase) {
            $phases[] = $phase;
        }

        return $phases;
    }

    /**
     * Verifica elos bidirecionais entre fases principais onde a inscrição existe (ignora fases intermediárias ausentes).
     */
    private function assertMainPhaseChainAligned(Opportunity $first_phase, string $number, string $context = ''): void
    {
        $registrations = [];

        foreach ($this->getMainPhasesFrom($first_phase) as $phase) {
            $registration = $this->findRegistration($phase, $number);
            if ($registration) {
                $registrations[] = $registration->refreshed();
            }
        }

        $this->assertGreaterThanOrEqual(
            2,
            count($registrations),
            "{$context}esperava inscrição {$number} em ao menos duas fases principais"
        );

        for ($i = 0, $last = count($registrations) - 1; $i < $last; $i++) {
            $previous = $registrations[$i];
            $current = $registrations[$i + 1];

            $this->assertEquals(
                $current->id,
                $previous->nextPhaseRegistrationId,
                "{$context}next da fase {$previous->opportunity->id} deve apontar para fase {$current->opportunity->id}"
            );
            $this->assertEquals(
                $previous->id,
                $current->previousPhaseRegistrationId,
                "{$context}previous da fase {$current->opportunity->id} deve apontar para fase {$previous->opportunity->id}"
            );

            $this->assertRegistrationDoesNotPointToMissingId($previous, $context);
        }

        $this->assertRegistrationDoesNotPointToMissingId($registrations[$last], $context);
    }

    private function assertRegistrationDoesNotPointToMissingId(Registration $registration, string $context = ''): void
    {
        if (!$registration->nextPhaseRegistrationId) {
            return;
        }

        $next = $this->app->repo('Registration')->find($registration->nextPhaseRegistrationId);

        $this->assertNotNull(
            $next,
            "{$context}inscrição {$registration->id} não deve apontar next para id inexistente ({$registration->nextPhaseRegistrationId})"
        );
    }

    private function breakPhasePointer(Registration $registration, string $field): void
    {
        $this->app->disableAccessControl();
        try {
            if ($field === 'next') {
                $registration->nextPhaseRegistrationId = null;
            } elseif ($field === 'previous') {
                $registration->previousPhaseRegistrationId = null;
            } else {
                throw new \InvalidArgumentException("Campo de ponteiro inválido: {$field}");
            }

            $registration->__skipQueuingPCacheRecreation = true;
            $registration->skipSync = true;
            $registration->save(true);
        } finally {
            $this->app->enableAccessControl();
        }
    }

    private function getOpportunityPhasesModule(): OpportunityPhasesModule
    {
        return $this->app->modules['OpportunityPhases'];
    }
}
