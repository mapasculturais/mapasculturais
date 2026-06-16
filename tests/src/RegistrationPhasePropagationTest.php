<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
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

    function testReportingPhaseAfterEvaluationWithAppealOnlyImportsApprovedRegistrations()
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

        $technical_evaluation = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();

        $technical_phase = $technical_evaluation->opportunity;

        $reporting_phase = $this->opportunityBuilder
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->getInstance();

        $appeal_phase = new ($technical_phase->specializedClassName)();
        $appeal_phase->parent = $technical_phase;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Recurso da avaliação técnica';
        $appeal_phase->ownerEntity = $technical_phase->ownerEntity;
        $appeal_phase->owner = $technical_phase->owner;
        $appeal_phase->isDataCollection = true;
        $appeal_phase->isAppealPhase = true;
        $appeal_phase->save(true);

        $appeal_evaluation = new EvaluationMethodConfiguration();
        $appeal_evaluation->opportunity = $appeal_phase;
        $appeal_evaluation->type = 'continuous';
        $appeal_evaluation->save(true);

        $appeal_phase->evaluationMethodConfiguration = $appeal_evaluation;
        $technical_phase->appealPhase = $appeal_phase;
        $technical_phase->save(true);

        $first_phase_registrations = $this->registrationDirector->createSentRegistrations($first_phase, number_of_registrations: 5);

        foreach ($first_phase_registrations as $registration) {
            $registration->setStatusToApproved(true);
        }

        $technical_phase->syncRegistrations($first_phase_registrations);
        $this->processJobs();

        $status_by_index = [
            Registration::STATUS_SENT,
            Registration::STATUS_INVALID,
            Registration::STATUS_NOTAPPROVED,
            Registration::STATUS_WAITLIST,
            Registration::STATUS_APPROVED,
        ];

        $expected_reporting_numbers = [];
        foreach ($first_phase_registrations as $index => $first_phase_registration) {
            $technical_registration = $this->app->repo('Registration')->findOneBy([
                'number' => $first_phase_registration->number,
                'opportunity' => $technical_phase,
            ]);

            $technical_registration->setStatus($status_by_index[$index]);
            $technical_registration->save(true);

            if ($status_by_index[$index] === Registration::STATUS_APPROVED) {
                $expected_reporting_numbers[] = $technical_registration->number;
            }
        }

        $reporting_phase->syncRegistrations();
        $this->processJobs();

        $reporting_numbers = array_map(
            fn(Registration $registration) => $registration->number,
            $this->app->repo('Registration')->findBy(['opportunity' => $reporting_phase])
        );

        sort($expected_reporting_numbers);
        sort($reporting_numbers);

        $this->assertSame($expected_reporting_numbers, $reporting_numbers, 'Garantindo que a prestação de informações após avaliação com recurso receba apenas inscrições selecionadas');
    }

    function testDataCollectionWithoutEvaluationKeepsQualifyingSentRegistrations()
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

        $data_collection_phase = $this->opportunityBuilder
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->getInstance();

        [$condition, $params] = \OpportunityPhases\Module::getPreviousPhaseQualificationDql('r1', $data_collection_phase);

        $this->assertSame('r1.status > 0', $condition, 'Garantindo que coleta/prestação sem avaliação continue qualificando inscrições enviadas');
        $this->assertSame([], $params, 'Garantindo que coleta/prestação sem avaliação não dependa de parâmetros de recurso');
    }
}
