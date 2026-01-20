<?php

namespace Tests\Builders\PhasePeriods;

use MapasCulturais\DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Interfaces\EvaluationPeriodInterface;

class Open implements EvaluationPeriodInterface, DataCollectionPeriodInterface
{
    public function getRegistrationFrom(?Opportunity $reference_phase = null): DateTime
    {
        return new DateTime('- 1 day');
    }

    public function getRegistrationTo(?Opportunity $reference_phase = null): DateTime
    {
        return new DateTime('+ 1 day');
    }

    public function getEvaluationFrom(Opportunity $phase): DateTime
    {
        return new DateTime('- 1 day');
    }

    public function getEvaluationTo(Opportunity $phase): DateTime
    {
        return new DateTime('+ 1 day');
    }
}
