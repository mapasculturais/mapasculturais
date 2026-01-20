<?php

namespace Tests\Builders\PhasePeriods;

use MapasCulturais\DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;

class Future implements DataCollectionPeriodInterface
{
    public function getRegistrationFrom(?Opportunity $reference_phase = null): DateTime
    {
        $reference_date = $reference_phase ? clone $reference_phase->registrationTo : new DateTime;

        return new DateTime($reference_date->format('Y-m-d 8:00') . ' + 1 day');
    }

    public function getRegistrationTo(?Opportunity $reference_phase = null): DateTime
    {
        $reference_date = $reference_phase ? clone $reference_phase->registrationFrom : new DateTime;

        return new DateTime($reference_date->format('Y-m-d 18:00') . ' + 1 week');
    }
}
