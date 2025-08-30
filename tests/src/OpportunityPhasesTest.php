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
        $evaluation_method_configuration_id = $phases[1]->id;

        $this->assertEquals($opportunity->id, $phases[1]->opportunity->id, "Garantindo que uma segunda fase de avaliação, após a exclusão da primeira fase de avaliação, esteja vinculada a primeira fase de coletada de dados");
    
    }
}
