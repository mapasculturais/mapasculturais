<?php

namespace Tests\Builders\PhasePeriods;

use MapasCulturais\DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;

class Past implements DataCollectionPeriodInterface
{
    public function getRegistrationFrom(?Opportunity $reference_phase = null): DateTime
    {
        $now = new DateTime;
        $reference_date = $reference_phase ? min($now, clone $reference_phase->registrationFrom) : $now;


        return new DateTime($reference_date->format('Y-m-d 8:00') . ' - 1 week');
    }

    public function getRegistrationTo(?Opportunity $reference_phase = null): DateTime
    {
        $now = new DateTime;
        $reference_date = $reference_phase ? min($now, clone $reference_phase->registrationFrom) : $now;

        return new DateTime($reference_date->format('Y-m-d 8:00') . ' - 1 day');
    }
}
