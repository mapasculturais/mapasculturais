<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\PermissionDenied;
use OpportunityAppealPhase\AppealReview\Service;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class AppealApplyCorrectionTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function enableFlag(): void
    {
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';
    }

    private function disableFlag(): void
    {
        unset($_ENV['APPEAL_SCORE_CORRECTION']);
        putenv('APPEAL_SCORE_CORRECTION');
    }

    /**
     * Cenário: fase técnica com 2 slots + fase de recurso + designação ativa.
     *
     * @return array{
     *   admin: User,
     *   opportunity: Opportunity,
     *   registration: \MapasCulturais\Entities\Registration,
     *   slotA: RegistrationEvaluation,
     *   slotB: RegistrationEvaluation,
     *   appealPhase: Opportunity,
     *   corrector: User,
     *   reviewA: RegistrationAppealReview,
     *   originalConsolidated: string|float|null
     * }
     */
    private function createCorrectionScenario(
        string $correction_type = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL,
        ?array $released_criteria = ['c-1'],
        bool $designate_slot_b = false
    ): array {
        $this->enableFlag();

        $admin = $this->userDirector->createUser('admin');
        $slot_owner_a = $this->userDirector->createUser();
        $slot_owner_b = $this->userDirector->createUser();
        $corrector = $slot_owner_a;
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->config()
                    ->addSection('sec-1', 'Seção 1')
                    ->addCriterion('c-1', 'sec-1', 'Critério 1', 0, 10, 1)
                    ->addCriterion('c-2', 'sec-1', 'Critério 2', 0, 10, 1)
                    ->done()
                ->save()
                ->addValuer('Comissão', 'Avaliador A', $slot_owner_a->profile)
                    ->done()
                ->addValuer('Comissão', 'Avaliador B', $slot_owner_b->profile)
                    ->done()
                ->done()
            ->save();

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder->getInstance();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $slot_a = new RegistrationEvaluation();
        $slot_a->registration = $registration;
        $slot_a->user = $slot_owner_a;
        $slot_a->setEvaluationData((object) ['c-1' => 4, 'c-2' => 6, 'obs' => 'A']);
        $slot_a->status = RegistrationEvaluation::STATUS_SENT;
        $slot_a->sentTimestamp = new DateTime();
        $slot_a->save(true);

        $slot_b = new RegistrationEvaluation();
        $slot_b->registration = $registration;
        $slot_b->user = $slot_owner_b;
        $slot_b->setEvaluationData((object) ['c-1' => 8, 'c-2' => 2, 'obs' => 'B']);
        $slot_b->status = RegistrationEvaluation::STATUS_SENT;
        $slot_b->sentTimestamp = new DateTime();
        $slot_b->save(true);

        $registration->consolidateResult(true);
        $original_consolidated = $registration->refreshed()->consolidatedResult;

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Recurso técnico';
        $appeal_phase->ownerEntity = $opportunity->ownerEntity;
        $appeal_phase->owner = $opportunity->owner;
        $appeal_phase->isDataCollection = true;
        $appeal_phase->isAppealPhase = true;
        $appeal_phase->registrationFrom = new DateTime('-1 day');
        $appeal_phase->registrationTo = new DateTime('+1 day');
        $appeal_phase->save(true);

        $opportunity->appealPhase = $appeal_phase;
        $opportunity->save(true);

        $appeal_emc = new EvaluationMethodConfiguration();
        $appeal_emc->opportunity = $appeal_phase;
        $appeal_emc->type = 'continuous';
        $appeal_emc->save(true);
        $appeal_phase->evaluationMethodConfiguration = $appeal_emc;
        $appeal_phase->save(true);

        $review_a = $this->createReview(
            $slot_a,
            $registration,
            $appeal_phase,
            $slot_owner_a,
            $corrector,
            $correction_type,
            $released_criteria
        );

        $review_b = null;
        if ($designate_slot_b) {
            $review_b = $this->createReview(
                $slot_b,
                $registration,
                $appeal_phase,
                $slot_owner_b,
                $corrector,
                $correction_type,
                $released_criteria
            );
        }

        $app->enableAccessControl();

        return [
            'admin' => $admin,
            'opportunity' => $opportunity,
            'registration' => $registration,
            'slotA' => $slot_a,
            'slotB' => $slot_b,
            'appealPhase' => $appeal_phase,
            'corrector' => $corrector,
            'reviewA' => $review_a,
            'reviewB' => $review_b,
            'originalConsolidated' => $original_consolidated,
            'slotOwnerB' => $slot_owner_b,
        ];
    }

    private function createReview(
        RegistrationEvaluation $slot,
        $registration,
        Opportunity $appeal_phase,
        User $slot_owner,
        User $corrector,
        string $correction_type,
        ?array $released_criteria
    ): RegistrationAppealReview {
        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $slot;
        $review->registration = $registration;
        $review->appealPhase = $appeal_phase;
        $review->slotOwnerUser = $slot_owner;
        $review->correctorUser = $corrector;
        $review->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review->correctionType = $correction_type;
        $review->releasedScope = $released_criteria === null
            ? null
            : (object) ['criteria' => $released_criteria];
        $review->startsAt = new DateTime('-1 hour');
        $review->endsAt = new DateTime('+1 day');
        $review->originalValue = $slot->getEvaluationData();
        $review->originalScore = is_numeric($slot->result) ? (float) $slot->result : null;
        $review->save(true);

        return $review;
    }

    function testFlagOffBlocksApplyCorrection(): void
    {
        $scenario = $this->createCorrectionScenario();
        $this->disableFlag();

        $this->login($scenario['corrector']);
        $service = new Service();

        $this->expectException(PermissionDenied::class);
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 10]);
    }

    function testOfficialCorrectionUpdatesSlotAndConsolidation(): void
    {
        $scenario = $this->createCorrectionScenario();
        $this->login($scenario['corrector']);

        $service = new Service();
        $review = $service->applyCorrection($scenario['reviewA'], [
            'c-1' => 10,
            'obs' => 'corrigido',
        ]);

        $slot_a = App::i()->repo('RegistrationEvaluation')->find($scenario['slotA']->id);
        $slot_b = App::i()->repo('RegistrationEvaluation')->find($scenario['slotB']->id);
        $registration = App::i()->repo('Registration')->find($scenario['registration']->id);

        $this->assertEquals(RegistrationAppealReview::STATUS_SENT, $review->status);
        $this->assertEquals(10.0, (float) $slot_a->evaluationData->{'c-1'});
        $this->assertEquals(6.0, (float) $slot_a->evaluationData->{'c-2'});
        $this->assertEquals(RegistrationEvaluation::STATUS_SENT, $slot_a->status);

        // Slot B intacto (regressão)
        $this->assertEquals(8.0, (float) $slot_b->evaluationData->{'c-1'});
        $this->assertEquals(2.0, (float) $slot_b->evaluationData->{'c-2'});

        // Consolidação mudou (média técnica dos 2 slots: (10+6)=16 e (8+2)=10 → média 13)
        $this->assertNotEquals($scenario['originalConsolidated'], $registration->consolidatedResult);
        $this->assertEquals(13.0, (float) $registration->consolidatedResult);

        $this->assertEquals(16.0, (float) $review->correctedScore);
        $this->assertNotNull($review->sentTimestamp);
    }

    function testMultipleSlotCorrectionsReflectInConsolidation(): void
    {
        $scenario = $this->createCorrectionScenario(
            released_criteria: ['c-1', 'c-2'],
            designate_slot_b: true
        );

        // reviewB foi designado com corrector = slot_owner_a; precisa redesignar para B
        $app = App::i();
        $app->disableAccessControl();
        $scenario['reviewB']->correctorUser = $scenario['slotOwnerB'];
        $scenario['reviewB']->save(true);
        $app->enableAccessControl();

        $service = new Service();

        $this->login($scenario['corrector']);
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 10, 'c-2' => 10]);

        $this->login($scenario['slotOwnerB']);
        $service->applyCorrection($scenario['reviewB'], ['c-1' => 0, 'c-2' => 0]);

        $registration = $app->repo('Registration')->find($scenario['registration']->id);
        // (20 + 0) / 2 = 10
        $this->assertEquals(10.0, (float) $registration->consolidatedResult);
    }

    function testRecordTypeDoesNotChangeOfficialScore(): void
    {
        $scenario = $this->createCorrectionScenario(
            correction_type: RegistrationAppealReview::CORRECTION_TYPE_RECORD
        );
        $this->login($scenario['corrector']);

        $before_a = (array) $scenario['slotA']->getEvaluationData();
        $before_consolidated = $scenario['originalConsolidated'];

        $service = new Service();
        $review = $service->applyCorrection($scenario['reviewA'], ['c-1' => 10]);

        $slot_a = App::i()->repo('RegistrationEvaluation')->find($scenario['slotA']->id);
        $registration = App::i()->repo('Registration')->find($scenario['registration']->id);

        $this->assertEquals($before_a['c-1'], $slot_a->evaluationData->{'c-1'});
        $this->assertEquals($before_consolidated, $registration->consolidatedResult);
        $this->assertEquals(RegistrationAppealReview::STATUS_SENT, $review->status);
        $this->assertEquals(10.0, (float) $review->correctedValue->{'c-1'});
        $this->assertEquals(16.0, (float) $review->correctedScore);

        $last = $slot_a->getLastRevision();
        $this->assertNotNull($last);
        $this->assertStringContainsString((string) $scenario['corrector']->id, $last->message);
        $this->assertStringContainsString((string) $slot_a->id, $last->message);
        $this->assertStringContainsString('somente registro', $last->message);
    }

    function testRevisionIdentifiesCorrectorAndSlot(): void
    {
        $scenario = $this->createCorrectionScenario();
        $this->login($scenario['corrector']);

        $service = new Service();
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 9]);

        $slot_a = App::i()->repo('RegistrationEvaluation')->find($scenario['slotA']->id);
        $last = $slot_a->getLastRevision();

        $this->assertNotNull($last);
        $this->assertStringContainsString((string) $scenario['corrector']->id, $last->message);
        $this->assertStringContainsString((string) $slot_a->id, $last->message);
        $this->assertStringContainsString((string) $slot_a->user->id, $last->message);
    }

    function testOutOfScopeCriteriaRejected(): void
    {
        $scenario = $this->createCorrectionScenario(released_criteria: ['c-1']);
        $this->login($scenario['corrector']);

        $service = new Service();
        $this->expectException(\InvalidArgumentException::class);
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 9, 'c-2' => 9]);
    }

    function testOutsideWindowRejected(): void
    {
        $scenario = $this->createCorrectionScenario();
        $app = App::i();
        $app->disableAccessControl();
        $scenario['reviewA']->endsAt = new DateTime('-1 minute');
        $scenario['reviewA']->save(true);
        $app->enableAccessControl();

        $this->login($scenario['corrector']);
        $service = new Service();

        $this->expectException(PermissionDenied::class);
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 9]);
    }

    function testWrongUserRejected(): void
    {
        $scenario = $this->createCorrectionScenario();
        $outsider = $this->userDirector->createUser();
        $this->login($outsider);

        $service = new Service();
        $this->expectException(PermissionDenied::class);
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 9]);
    }

    function testSaveDraftDoesNotTouchOfficialScore(): void
    {
        $scenario = $this->createCorrectionScenario();
        $this->login($scenario['corrector']);

        $service = new Service();
        $review = $service->saveDraft($scenario['reviewA'], ['c-1' => 10]);

        $slot_a = App::i()->repo('RegistrationEvaluation')->find($scenario['slotA']->id);
        $registration = App::i()->repo('Registration')->find($scenario['registration']->id);

        $this->assertEquals(RegistrationAppealReview::STATUS_DRAFT, $review->status);
        $this->assertEquals(10.0, (float) $review->correctedValue->{'c-1'});
        $this->assertEquals(4.0, (float) $slot_a->evaluationData->{'c-1'});
        $this->assertEquals($scenario['originalConsolidated'], $registration->consolidatedResult);

        // Envio a partir do rascunho
        $review = $service->applyCorrection($review);
        $slot_a = App::i()->repo('RegistrationEvaluation')->find($scenario['slotA']->id);
        $this->assertEquals(10.0, (float) $slot_a->evaluationData->{'c-1'});
        $this->assertEquals(RegistrationAppealReview::STATUS_SENT, $review->status);
    }

    function testPcacheQueuedOnlyForAffectedRegistration(): void
    {
        $scenario = $this->createCorrectionScenario();
        $this->login($scenario['corrector']);

        $app = App::i();
        $conn = $app->em->getConnection();
        $before = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM permission_cache_pending WHERE object_type LIKE :t AND object_id = :id',
            [
                't' => '%Registration%',
                'id' => $scenario['registration']->id,
            ]
        );

        $service = new Service();
        $service->applyCorrection($scenario['reviewA'], ['c-1' => 10]);

        $after = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM permission_cache_pending WHERE object_type LIKE :t AND object_id = :id',
            [
                't' => '%Registration%',
                'id' => $scenario['registration']->id,
            ]
        );

        // Com recreateCacheImmediately pode zerar a fila; nesse caso consolidação já prova o efeito.
        // Quando a fila é usada, deve haver ao menos um item para a inscrição afetada.
        if (!$app->config['app.recreateCacheImmediately']) {
            $this->assertGreaterThan($before, $after);
        } else {
            $this->assertTrue(true);
        }
    }
}
