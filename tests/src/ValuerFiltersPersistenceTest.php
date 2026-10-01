<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class ValuerFiltersPersistenceTest extends TestCase
{
    use OpportunityBuilder;
    use RequestFactory;
    use UserDirector;

    public function testSetValuerFiltersMarksRelationStorageEvenWhenAllFiltersAreRemoved(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter())
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->done()
            ->getInstance();

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $valuer = $this->userDirector->createUser();
        $relation = $emc->createAgentRelation($valuer->profile, 'Comissão', true, true, true);

        $response = $this->requestFactory->POST(
            'evaluationMethodConfiguration',
            'setValuerFilters',
            [$emc->id],
            [
                'relationId' => $relation->id,
                'categories' => null,
                'proponentTypes' => null,
                'ranges' => null,
                'distribution' => null,
                'selectionFields' => null,
            ]
        );

        $this->assertStatus200($response);

        $persistedRelation = App::i()
            ->repo('EvaluationMethodConfigurationAgentRelation')
            ->find($relation->id);

        $this->assertTrue($persistedRelation->getFiltersStoredOnRelation());
    }
}
