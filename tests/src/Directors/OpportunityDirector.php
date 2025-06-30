<?php

namespace Tests\Directors;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;
use Tests\Abstract\Director;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Traits\OpportunityBuilder;

class OpportunityDirector extends Director {
    use OpportunityBuilder;

    const PERIOD_FUTURE = 'future';
    const PERIOD_PAST = 'past';
    const PERIOD_OPEN = 'open';
    const PERIOD_CONCURRENT = 'concurrent';

    function createOpportunity(Agent $owner, Agent|Space|Project|Event $ownerEntity, DataCollectionPeriodInterface $registration_period, ?string $evaluation_method_slug = null): Opportunity {
        $this->opportunityBuilder
            ->reset($owner, $ownerEntity)
            ->firstPhase()
                ->save()
                ->done();


        $opportunity = $this->opportunityBuilder->getInstance();
            
        return $opportunity;
    }
}
