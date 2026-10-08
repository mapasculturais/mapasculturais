<?php

namespace Tests\Doubles;

use EvaluationMethodTechnical\Quotas;

/**
 * Implementação atual da classificação por cotas, alimentada com inscrições
 * sintéticas (ver InjectedQuotaData).
 */
class InjectedQuotas extends Quotas
{
    use InjectedQuotaData;
}
