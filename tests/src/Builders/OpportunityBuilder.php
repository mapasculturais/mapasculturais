<?php

namespace Tests\src\Builders;

use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\Builder;

class OpportunityBuilder extends Builder
{
    protected Opportunity $instance;

    public function fillRequiredProperties(): self
    {
        

        return $this;
    }

    function getInstance(): Opportunity
    {
        return $this->instance;
    }
}
