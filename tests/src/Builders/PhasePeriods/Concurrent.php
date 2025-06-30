<?php

namespace Tests\Builders\PhasePeriods;

use DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Interfaces\EvaluationPeriodInterface;

class Concurrent implements EvaluationPeriodInterface, DataCollectionPeriodInterface
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
        return clone $reference_phase->registrationTo;
    }

    public function getEvaluationFrom(Opportunity $phase): DateTime
    {
        return clone $phase->registrationTo;
    }

    public function getEvaluationTo(Opportunity $phase): DateTime
    {
        return clone $phase->registrationFrom;
    }
}
