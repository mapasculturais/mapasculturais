<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class RegistrationAppealReviewTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    /**
     * Cria um cenário mínimo para testar a entidade RegistrationAppealReview.
     */
    private function createAppealReviewScenario(User $admin): array
    {
        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('Comissão', 'Avaliador 1', $admin->profile)
                    ->done()
                ->done();

        $opportunity = $evaluation_phase_builder->getInstance();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $original_evaluation = new RegistrationEvaluation();
        $original_evaluation->registration = $registration;
        $original_evaluation->user = $admin;
        $original_evaluation->status = RegistrationEvaluation::STATUS_EVALUATED;
        $original_evaluation->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso de teste';
        $appeal_phase->ownerEntity = $admin->profile;
        $appeal_phase->owner = $admin->profile;
        $appeal_phase->registrationFrom = new DateTime('-1 day');
        $appeal_phase->registrationTo = new DateTime('+1 day');
        $appeal_phase->save(true);

        $slot_owner = $this->userDirector->createUser();
        $corrector = $this->userDirector->createUser();

        $app->enableAccessControl();

        return [
            $opportunity,
            $registration,
            $original_evaluation,
            $appeal_phase,
            $slot_owner,
            $corrector,
        ];
    }

    /**
     * Cria um cenário de fase técnica + fase de recurso para testar elegibilidade e permissões.
     */
    private function createAppealCorrectionPermissionScenario(User $admin): array
    {
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';

        $slot_owner = $this->userDirector->createUser();
        $other_slot_evaluator = $this->userDirector->createUser();
        $appeal_committee_user = $this->userDirector->createUser();
        $outsider = $this->userDirector->createUser();

        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
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
                ->addValuer('Comissão', 'Avaliador Slot', $slot_owner->profile)
                    ->done()
                ->addValuer('Comissão', 'Outro Avaliador', $other_slot_evaluator->profile)
                    ->done()
                ->done();

        $opportunity = $evaluation_phase_builder->getInstance();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $slot_evaluation = new RegistrationEvaluation();
        $slot_evaluation->registration = $registration;
        $slot_evaluation->user = $slot_owner;
        $slot_evaluation->status = RegistrationEvaluation::STATUS_EVALUATED;
        $slot_evaluation->save(true);

        $other_slot_evaluation = new RegistrationEvaluation();
        $other_slot_evaluation->registration = $registration;
        $other_slot_evaluation->user = $other_slot_evaluator;
        $other_slot_evaluation->status = RegistrationEvaluation::STATUS_EVALUATED;
        $other_slot_evaluation->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Fase de recurso técnica';
        $appeal_phase->ownerEntity = $opportunity->ownerEntity;
        $appeal_phase->owner = $opportunity->owner;
        $appeal_phase->registrationCategories = $opportunity->registrationCategories;
        $appeal_phase->registrationRanges = $opportunity->registrationRanges;
        $appeal_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;
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
        $appeal_emc->publishEvaluationDetails = true;
        $appeal_emc->save(true);

        $appeal_phase->evaluationMethodConfiguration = $appeal_emc;
        $appeal_phase->save(true);

        $appeal_relation = $appeal_emc->createAgentRelation($appeal_committee_user->profile, 'committee 1', true);
        $appeal_relation->save(true);

        $app->enableAccessControl();

        return [
            'opportunity' => $opportunity,
            'registration' => $registration,
            'slotEvaluation' => $slot_evaluation,
            'otherSlotEvaluation' => $other_slot_evaluation,
            'appealPhase' => $appeal_phase,
            'slotOwner' => $slot_owner,
            'otherSlotEvaluator' => $other_slot_evaluator,
            'appealCommitteeUser' => $appeal_committee_user,
            'outsider' => $outsider,
        ];
    }

    function testCanPersistAndRetrieveAllFields(): void
    {
        $admin = $this->userDirector->createUser('admin');

        [
            $opportunity,
            $registration,
            $original_evaluation,
            $appeal_phase,
            $slot_owner,
            $corrector,
        ] = $this->createAppealReviewScenario($admin);

        $starts_at = new DateTime('2026-08-01 09:00:00');
        $ends_at = new DateTime('2026-08-10 18:00:00');

        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $original_evaluation;
        $review->registration = $registration;
        $review->appealPhase = $appeal_phase;
        $review->slotOwnerUser = $slot_owner;
        $review->correctorUser = $corrector;
        $review->status = RegistrationAppealReview::STATUS_DRAFT;
        $review->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;
        $review->releasedScope = (object) ['fields' => ['field1', 'field2']];
        $review->startsAt = $starts_at;
        $review->endsAt = $ends_at;
        $review->originalValue = (object) ['score' => 5.0, 'result' => 'approved'];
        $review->correctedValue = (object) ['score' => 7.5, 'result' => 'approved'];
        $review->originalScore = 5.0;
        $review->correctedScore = 7.5;
        $review->sentTimestamp = new DateTime('2026-08-09 14:30:00');

        $app = App::i();
        $app->disableAccessControl();
        $review->save(true);
        $app->enableAccessControl();

        $this->assertNotNull($review->id, 'O ID da revisão de recurso deve ser gerado.');

        $retrieved = $app->repo('OpportunityAppealPhase\Entities\RegistrationAppealReview')->find($review->id);

        $this->assertInstanceOf(RegistrationAppealReview::class, $retrieved);
        $this->assertEquals($original_evaluation->id, $retrieved->originalEvaluation->id);
        $this->assertEquals($registration->id, $retrieved->registration->id);
        $this->assertEquals($appeal_phase->id, $retrieved->appealPhase->id);
        $this->assertEquals($slot_owner->id, $retrieved->slotOwnerUser->id);
        $this->assertEquals($corrector->id, $retrieved->correctorUser->id);
        $this->assertEquals(RegistrationAppealReview::STATUS_DRAFT, $retrieved->status);
        $this->assertEquals(RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL, $retrieved->correctionType);
        $this->assertEquals(['fields' => ['field1', 'field2']], (array) $retrieved->releasedScope);
        $this->assertEquals($starts_at->format('Y-m-d H:i:s'), $retrieved->startsAt->format('Y-m-d H:i:s'));
        $this->assertEquals($ends_at->format('Y-m-d H:i:s'), $retrieved->endsAt->format('Y-m-d H:i:s'));
        $this->assertEquals(['score' => 5.0, 'result' => 'approved'], (array) $retrieved->originalValue);
        $this->assertEquals(['score' => 7.5, 'result' => 'approved'], (array) $retrieved->correctedValue);
        $this->assertEqualsWithDelta(5.0, $retrieved->originalScore, 0.001);
        $this->assertEqualsWithDelta(7.5, $retrieved->correctedScore, 0.001);
        $this->assertNotNull($retrieved->createTimestamp, 'createTimestamp deve ser preenchido automaticamente.');
        $this->assertEquals('2026-08-09 14:30:00', $retrieved->sentTimestamp->format('Y-m-d H:i:s'));
        $this->assertNotNull($retrieved->updateTimestamp, 'updateTimestamp deve ser preenchido automaticamente.');
    }

    function testActiveSlotUniqueIndexPreventsDuplicateActiveDesignations(): void
    {
        $admin = $this->userDirector->createUser('admin');

        [
            $opportunity,
            $registration,
            $original_evaluation,
            $appeal_phase,
            $slot_owner,
            $corrector,
        ] = $this->createAppealReviewScenario($admin);

        $app = App::i();
        $app->disableAccessControl();

        $review1 = new RegistrationAppealReview();
        $review1->originalEvaluation = $original_evaluation;
        $review1->registration = $registration;
        $review1->appealPhase = $appeal_phase;
        $review1->slotOwnerUser = $slot_owner;
        $review1->correctorUser = $corrector;
        $review1->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review1->correctionType = RegistrationAppealReview::CORRECTION_TYPE_RECORD;
        $review1->save(true);

        $review2 = new RegistrationAppealReview();
        $review2->originalEvaluation = $original_evaluation;
        $review2->registration = $registration;
        $review2->appealPhase = $appeal_phase;
        $review2->slotOwnerUser = $slot_owner;
        $review2->correctorUser = $corrector;
        $review2->status = RegistrationAppealReview::STATUS_DRAFT;
        $review2->correctionType = RegistrationAppealReview::CORRECTION_TYPE_RECORD;

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        $review2->save(true);
    }

    function testSentSlotAllowsNewDesignation(): void
    {
        $admin = $this->userDirector->createUser('admin');

        [
            $opportunity,
            $registration,
            $original_evaluation,
            $appeal_phase,
            $slot_owner,
            $corrector,
        ] = $this->createAppealReviewScenario($admin);

        $app = App::i();
        $app->disableAccessControl();

        $review1 = new RegistrationAppealReview();
        $review1->originalEvaluation = $original_evaluation;
        $review1->registration = $registration;
        $review1->appealPhase = $appeal_phase;
        $review1->slotOwnerUser = $slot_owner;
        $review1->correctorUser = $corrector;
        $review1->status = RegistrationAppealReview::STATUS_SENT;
        $review1->correctionType = RegistrationAppealReview::CORRECTION_TYPE_RECORD;
        $review1->save(true);

        $review2 = new RegistrationAppealReview();
        $review2->originalEvaluation = $original_evaluation;
        $review2->registration = $registration;
        $review2->appealPhase = $appeal_phase;
        $review2->slotOwnerUser = $slot_owner;
        $review2->correctorUser = $corrector;
        $review2->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review2->correctionType = RegistrationAppealReview::CORRECTION_TYPE_RECORD;
        $review2->save(true);

        $this->assertNotNull($review2->id, 'Uma nova designação deve ser permitida quando o slot anterior já foi enviado.');
    }

    function testReopenedSlotPreventsDuplicateActiveDesignation(): void
    {
        $admin = $this->userDirector->createUser('admin');

        [
            $opportunity,
            $registration,
            $original_evaluation,
            $appeal_phase,
            $slot_owner,
            $corrector,
        ] = $this->createAppealReviewScenario($admin);

        $app = App::i();
        $app->disableAccessControl();

        $review1 = new RegistrationAppealReview();
        $review1->originalEvaluation = $original_evaluation;
        $review1->registration = $registration;
        $review1->appealPhase = $appeal_phase;
        $review1->slotOwnerUser = $slot_owner;
        $review1->correctorUser = $corrector;
        $review1->status = RegistrationAppealReview::STATUS_REOPENED;
        $review1->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;
        $review1->save(true);

        $review2 = new RegistrationAppealReview();
        $review2->originalEvaluation = $original_evaluation;
        $review2->registration = $registration;
        $review2->appealPhase = $appeal_phase;
        $review2->slotOwnerUser = $slot_owner;
        $review2->correctorUser = $corrector;
        $review2->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review2->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        $review2->save(true);
    }

    function testEligibleCorrectorsIncludeSlotOwnerAndAppealCommitteeOnly(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $scenario = $this->createAppealCorrectionPermissionScenario($admin);

        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $scenario['slotEvaluation'];
        $review->registration = $scenario['registration'];
        $review->appealPhase = $scenario['appealPhase'];
        $review->slotOwnerUser = $scenario['slotOwner'];
        $review->correctorUser = $scenario['appealCommitteeUser'];
        $review->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;

        $eligible_ids = array_map(fn (User $user) => $user->id, $review->eligibleCorrectors());

        $this->assertContains($scenario['slotOwner']->id, $eligible_ids);
        $this->assertContains($scenario['appealCommitteeUser']->id, $eligible_ids);
        $this->assertNotContains($scenario['otherSlotEvaluator']->id, $eligible_ids);
        $this->assertNotContains($scenario['outsider']->id, $eligible_ids);
    }

    function testDesignatedAppealCommitteeUserCanModifyAndViewOriginalEvaluation(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $scenario = $this->createAppealCorrectionPermissionScenario($admin);

        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $scenario['slotEvaluation'];
        $review->registration = $scenario['registration'];
        $review->appealPhase = $scenario['appealPhase'];
        $review->slotOwnerUser = $scenario['slotOwner'];
        $review->correctorUser = $scenario['appealCommitteeUser'];
        $review->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;

        $app = App::i();
        $app->disableAccessControl();
        $review->save(true);
        $app->enableAccessControl();

        $this->assertTrue($scenario['slotEvaluation']->canUser('modify', $scenario['appealCommitteeUser']));
        $this->assertTrue($scenario['slotEvaluation']->canUser('view', $scenario['appealCommitteeUser']));
    }

    function testUserWithoutActiveDesignationCannotModifyOriginalEvaluation(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $scenario = $this->createAppealCorrectionPermissionScenario($admin);

        $this->assertFalse($scenario['slotEvaluation']->canUser('modify', $scenario['appealCommitteeUser']));
        $this->assertFalse($scenario['slotEvaluation']->canUser('modify', $scenario['outsider']));
    }

    function testEvaluatorFromAnotherSlotIsRejectedWhenNotInAppealCommittee(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $scenario = $this->createAppealCorrectionPermissionScenario($admin);

        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $scenario['slotEvaluation'];
        $review->registration = $scenario['registration'];
        $review->appealPhase = $scenario['appealPhase'];
        $review->slotOwnerUser = $scenario['slotOwner'];
        $review->correctorUser = $scenario['appealCommitteeUser'];
        $review->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;

        $app = App::i();
        $app->disableAccessControl();
        $review->save(true);
        $app->enableAccessControl();

        $this->assertFalse($scenario['slotEvaluation']->canUser('modify', $scenario['otherSlotEvaluator']));
    }
}
