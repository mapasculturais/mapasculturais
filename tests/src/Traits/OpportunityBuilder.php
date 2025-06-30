<?php
namespace Tests\Traits;

use Tests\Builders;

trait OpportunityBuilder {
    protected Builders\OpportunityBuilder $opportunityBuilder;

    function __initOpportunityBuilder() {
        $this->opportunityBuilder = new Builders\OpportunityBuilder;
    }
}