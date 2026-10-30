<?php

namespace Tests\Doubles;

/**
 * Substitui a carga de inscrições do cálculo de cotas (Quotas) por inscrições
 * sintéticas em memória, para exercitar a classificação com volume e variedade
 * de dados sem criar milhares de entidades no banco.
 *
 * Mantém o formato que a carga real produz: só os metadados usados pelas cotas,
 * pela distribuição geográfica e pelo desempate (além de appliedForQuota)
 * chegam às inscrições, e os dados da primeira fase já vêm aplicados.
 */
trait InjectedQuotaData
{
    /** @var object[] */
    protected array $injectedRegistrations = [];

    protected array $injectedEvaluationData = [];

    /**
     * @param object[] $registrations inscrições no formato de loadRegistrationsForQuotaSorting
     * @param array $evaluation_data médias por critério no formato de fetchEvaluationData
     */
    public function inject(array $registrations, array $evaluation_data = []): static
    {
        $this->injectedRegistrations = $registrations;
        $this->injectedEvaluationData = $evaluation_data;

        return $this;
    }

    protected function loadRegistrationsForQuotaSorting(): array
    {
        $keys = array_unique(array_merge($this->fields, ['appliedForQuota']));

        $registrations = [];
        foreach ($this->injectedRegistrations as $prototype) {
            $registration = clone $prototype;

            foreach (array_keys(get_object_vars($registration)) as $property) {
                if (str_starts_with($property, 'field_') && !in_array($property, $keys, true)) {
                    unset($registration->$property);
                }
            }

            $registrations[] = $registration;
        }

        return $registrations;
    }

    protected function enrichRegistrationsFromFirstPhase(array $registrations): void
    {
        // os dados sintéticos já trazem os campos da primeira fase
    }

    public function fetchEvaluationData(array $registrations): array
    {
        return $this->injectedEvaluationData;
    }
}
