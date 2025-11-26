<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityPhasesTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;


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
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->save()
                                    ->done()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->refresh()
                                ->getInstance();
        

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();

        $phases = $opportunity->phases;

        $this->assertEquals($opportunity->id, $phases[1]->opportunity->id, "Garantindo que uma segunda fase de avaliação, após a exclusão da primeira fase de avaliação, esteja vinculada a primeira fase de coletada de dados");
    
    }

    function testSecondEvaluationPhaseDeletion() {
        $app = $this->app;

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;
        
        /** @var Opportunity */
        $opportunity = $builder->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->getInstance();

        $eval_phase1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();
        


        $this->assertFalse((bool) $eval_phase2->opportunity->isDataCollection, 'Garantindo que a  Opportunity vinculada a uma fase de avaliação que segue outra fase de avaliação não é uma coleta de dados');

        $hidden_opportunity_id = $eval_phase2->opportunity->id;
        
        $eval_phase2->delete(flush: true);

        $hidden_opportunity = $app->repo('Opportunity')->find($hidden_opportunity_id);

        $this->assertNull($hidden_opportunity, 'Garantindo que a exclusão de uma fase de avaliação exclua a Opportunity vinculada quando essa não é uma coleta de dados');
    
    }
}
