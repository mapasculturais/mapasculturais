<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationsDistributionTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testConcurrentEvaluationPhaseDistribution() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase('simple')
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->getInstance();
        
        $this->registrationDirector->createDraftRegistrations(
                $opportunity,
                number_of_registrations:10
        );

        $this->registrationDirector->createSentRegistrations(
                $opportunity,
                number_of_registrations:10
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        
        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $opportunity->evaluationMethodConfiguration->refreshed();

        $valuer1_summary = $emc->agentRelations[0]->metadata['summary'];
        $valuer2_summary = $emc->agentRelations[1]->metadata['summary'];
        
        $this->assertEquals(5, $valuer1_summary['pending'], 'Garantindo que o avaliador 1 tem 5 avaliações pendentes');
        $this->assertEquals(5, $valuer2_summary['pending'], 'Garantindo que o avaliador 2 tem 5 avaliações pendentes');

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");

        $this->assertEquals(10, $number_of_evaluations, 'Garantindo que tenha 10 avaliações');
    }

    function testFirstEvaluationPhaseDeletion() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase('simple')
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->save()
                                    ->done()
                                ->addEvaluationPhase('simple')
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->refresh()
                                ->getInstance();


        $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: 10
        );

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();
        $phases = $opportunity->phases;
        $evaluation_method_configuration_id = $phases[1]->id;

        $app = $this->app;

        /** @var EvaluationMethodConfiguration */
        $emc = $app->repo('EvaluationMethodConfiguration')->find($evaluation_method_configuration_id);
        
        $emc->redistributeCommitteeRegistrations();
        // atualiza o objeto da fase de avaliação para recarregar os metadados dos agentes relacionados
        $emc = $emc->refreshed();

        $valuer1_summary = $emc->agentRelations[0]->metadata['summary'];
        $valuer2_summary = $emc->agentRelations[1]->metadata['summary'];
        
        $this->assertEquals(5, $valuer1_summary['pending'], 'Garantindo que o avaliador 1 tem 5 avaliações pendentes');
        $this->assertEquals(5, $valuer2_summary['pending'], 'Garantindo que o avaliador 2 tem 5 avaliações pendentes');

        /** @var Connection */
        $conn = $this->app->em->getConnection();
        $number_of_evaluations = $conn->fetchScalar("SELECT COUNT(*) FROM evaluations");

        $this->assertEquals(10, $number_of_evaluations, 'Garantindo que tenha 10 avaliações');
    }
}
