<?php

namespace Tests\Builders\PhasePeriods;

use DateTime;
use DateTimeInterface;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\DataCollectionPeriodInterface;

/**
 * Período de inscrição com datas absolutas, para testes que dependem do horário
 * exato de abertura/encerramento (ex.: classificação de oportunidades abertas,
 * futuras e encerradas na API por timestamp completo).
 */
class RegistrationWindow implements DataCollectionPeriodInterface
{
    protected DateTime $registration_from;
    protected DateTime $registration_to;

    public function __construct(DateTimeInterface $registration_from, DateTimeInterface $registration_to)
    {
        $this->registration_from = DateTime::createFromInterface($registration_from);
        $this->registration_to = DateTime::createFromInterface($registration_to);
    }

    public function getRegistrationFrom(?Opportunity $reference_phase = null): DateTime
    {
        return $this->registration_from;
    }

    public function getRegistrationTo(?Opportunity $reference_phase = null): DateTime
    {
        return $this->registration_to;
    }
}
