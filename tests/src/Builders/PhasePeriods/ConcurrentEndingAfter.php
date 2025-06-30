<?php

namespace Tests\Builders\PhasePeriods;

use DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Interfaces\EvaluationPeriodInterface;

class ConcurrentEndingAfter implements EvaluationPeriodInterface, DataCollectionPeriodInterface
{
    public function getRegistrationFrom(?Opportunity $reference_phase = null): DateTime
    {
        if (!$reference_phase) {
            throw new \Exception(__CLASS__ . ': Não pode ser utilizado na primeira fase');
        }

        return clone $reference_phase->registrationFrom;
    }

    public function getRegistrationTo(?Opportunity $reference_phase = null): DateTime
    {
        if (!$reference_phase) {
            throw new \Exception(__CLASS__ . ': Não pode ser utilizado na primeira fase');
        }

        return new DateTime($reference_phase->registrationTo->format('Y-m-d H:i:s') . ' + 1 week');
    }

    public function getEvaluationFrom(Opportunity $phase): DateTime
    {
        return clone $phase->registrationTo;
    }

    public function getEvaluationTo(Opportunity $phase): DateTime
    {
        return new DateTime($phase->registrationTo->format('Y-m-d H:i:s') . ' + 1 week');
    }
}
