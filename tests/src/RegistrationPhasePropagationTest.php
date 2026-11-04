<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class RegistrationPhasePropagationTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testRegistrationPropagation()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $first_phase */
        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $eval_phase_1 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        $phase2 = $eval_phase_1->opportunity;

        $eval_phase_2 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        $phase3 = $eval_phase_2->opportunity;

        $eval_phase_3 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        $phase4 = $eval_phase_3->opportunity;

        $eval_phase_4 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        $phase5 = $eval_phase_4->opportunity;

        $eval_phase_5 = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        $phase6 = $eval_phase_5->opportunity;

        $first_phase = $first_phase->refreshed();

        // Cria uma inscrição na primeira fase
        /** @var Registration $registration_first */
        $registration_first = $this->registrationDirector->createSentRegistration($first_phase, []);
        $number = $registration_first->number;

        // Aprova a inscrição na primeira fase
        $registration_first->setStatusToApproved(true);
        $registration_first = $registration_first->refreshed();

        $repo = $this->app->repo('Registration');

        $current_registration = $registration_first;

        // Fase 2
        $phase2->syncRegistrations([$current_registration]);
        $this->processJobs();
        $current_registration = $repo->findOneBy(['number' => $number, 'opportunity' => $phase2]);
        $this->assertNotNull($current_registration, 'Inscrição deve ter sido propagada para a fase 2');
        $current_registration->setStatusToApproved(true);
        $current_registration = $current_registration->refreshed();

        // Fase 3
        $phase3->syncRegistrations([$current_registration]);
        $this->processJobs();
        $current_registration = $repo->findOneBy(['number' => $number, 'opportunity' => $phase3]);
        $this->assertNotNull($current_registration, 'Inscrição deve ter sido propagada para a fase 3');
        $current_registration->setStatusToApproved(true);
        $current_registration = $current_registration->refreshed();

        // Fase 4
        $phase4->syncRegistrations([$current_registration]);
        $this->processJobs();
        $current_registration = $repo->findOneBy(['number' => $number, 'opportunity' => $phase4]);
        $this->assertNotNull($current_registration, 'Inscrição deve ter sido propagada para a fase 4');
        $current_registration->setStatusToApproved(true);
        $current_registration = $current_registration->refreshed();

        // Fase 5
        $phase5->syncRegistrations([$current_registration]);
        $this->processJobs();
        $current_registration = $repo->findOneBy(['number' => $number, 'opportunity' => $phase5]);
        $this->assertNotNull($current_registration, 'Inscrição deve ter sido propagada para a fase 5');
        $current_registration->setStatusToApproved(true);
        $current_registration = $current_registration->refreshed();

        // Fase 6
        $phase6->syncRegistrations([$current_registration]);
        $this->processJobs();
        $current_registration = $repo->findOneBy(['number' => $number, 'opportunity' => $phase6]);
        $this->assertNotNull($current_registration, 'Inscrição deve ter sido propagada para a fase 6');
    }

    function testLastPhaseUsesEvaluationResultWhenDeepestLinkHasNoResult()
    {
        [$evaluated_phase, $pass_through_phase] = $this->buildPhasesWithPassThrough();

        $registration = $this->propagate($evaluated_phase, Registration::STATUS_APPROVED);
        $last_phase_registration = $this->findInPhase($registration->number, $evaluated_phase->lastPhase);

        $this->assertSame(
            Registration::STATUS_DRAFT,
            $this->findInPhase($registration->number, $pass_through_phase)->status,
            'A fase de passagem precisa existir na cadeia sem registrar resultado'
        );
        $this->assertSame(
            Registration::STATUS_APPROVED,
            $last_phase_registration->status,
            'A publicação final deve manter o resultado da avaliação, e não o status do elo de passagem'
        );
        $this->assertStringContainsString(
            'Selecionada em',
            $last_phase_registration->consolidatedResult,
            'O texto gravado deve citar o resultado da avaliação'
        );
    }

    function testLastPhaseUsesNearestResultWhenDeepestLinkHasResult()
    {
        [$evaluated_phase, $pass_through_phase] = $this->buildPhasesWithPassThrough();

        $registration = $this->propagate($evaluated_phase, Registration::STATUS_APPROVED);

        $pass_through_registration = $this->findInPhase($registration->number, $pass_through_phase);
        $pass_through_registration->send(false);
        $pass_through_registration->refreshed()->setStatusToNotApproved(true);

        $last_phase = $evaluated_phase->lastPhase;
        $last_phase->syncRegistrations([$this->findInPhase($registration->number, $pass_through_phase)]);
        $this->processJobs();

        $this->assertSame(
            Registration::STATUS_NOTAPPROVED,
            $this->findInPhase($registration->number, $last_phase)->status,
            'Havendo resultado no elo mais profundo, é ele que vale, e não o da fase anterior'
        );
    }

    /**
     * @return array{0: Opportunity, 1: Opportunity} fase avaliada e fase de passagem
     */
    private function buildPhasesWithPassThrough(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $evaluated_phase */
        $evaluated_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save();

        $pass_through_phase = $this->opportunityBuilder
            ->addDataCollectionPhase()
                ->getInstance();

        return [$evaluated_phase->refreshed(), $pass_through_phase];
    }

    private function propagate(Opportunity $evaluated_phase, int $status): Registration
    {
        $registration = $this->registrationDirector->createSentRegistration($evaluated_phase, []);
        $registration->setStatus($status);
        $this->processJobs();

        $evaluated_phase->lastPhase->syncRegistrations([$registration->refreshed()]);
        $this->processJobs();

        return $registration->refreshed();
    }

    private function findInPhase(string $number, Opportunity $phase): ?Registration
    {
        return $this->app->repo('Registration')->findOneBy(['number' => $number, 'opportunity' => $phase]);
    }
}

