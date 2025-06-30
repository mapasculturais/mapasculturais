<?php
namespace Tests\Traits;

use Tests\Directors;

trait OpportunityDirector {
    protected Directors\OpportunityDirector $opportunityDirector;

    function __initOpportunityDirector() {
        $this->opportunityDirector = new Directors\OpportunityDirector;
    }
}