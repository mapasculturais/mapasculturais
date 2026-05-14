<?php

namespace Tests\Builders;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\Builder;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Traits\Faker;
use Tests\Traits\UserDirector;

/**
 * Builder para a Fase de Execução e seus pedidos (inscrições).
 *
 * Uso básico:
 *
 *   $executionPhaseBuilder
 *       ->reset($opportunityBuilder)
 *       ->setRegistrationPeriod(new Open)
 *       ->setCategories(['Alteração de planilha orçamentária', 'Prorrogação'])
 *       ->save()
 *       ->done(); // volta para o OpportunityBuilder
 */
class ExecutionPhaseBuilder extends Builder
{
    use Faker, UserDirector;

    protected Opportunity $instance;

    public function withInstance(Opportunity $phase): self
    {
        $this->instance = $phase;
        return $this;
    }

    function __construct(private OpportunityBuilder $opportunityBuilder)
    {
        parent::__construct();
    }

    /**
     * Cria a fase de execução como filha da oportunidade principal.
     */
    public function reset(?Opportunity $parent = null): self
    {
        $app = App::i();

        $first_phase = $parent ?: $this->opportunityBuilder->getInstance();
        $class = $first_phase->specializedClassName;

        $execution_phase = new $class();
        $execution_phase->parent             = $first_phase;
        $execution_phase->status             = Opportunity::STATUS_PHASE;
        $execution_phase->name               = $this->faker->name . ' — Execução';
        $execution_phase->type               = $first_phase->type;
        $execution_phase->ownerEntity        = $first_phase->ownerEntity;
        $execution_phase->isOpportunityPhase = true;
        $execution_phase->isDataCollection   = true;
        $execution_phase->isExecutionPhase   = true;
        $execution_phase->registrationLimitPerOwner = 0;

        $this->instance = $execution_phase;

        return $this;
    }

    public function getInstance(): Opportunity
    {
        return $this->instance;
    }

    public function fillRequiredProperties(): self
    {
        if (!$this->instance->name) {
            $this->instance->name = $this->faker->name . ' — Execução';
        }

        return $this;
    }

    public function done(): OpportunityBuilder
    {
        return $this->opportunityBuilder;
    }

    public function setRegistrationPeriod(DataCollectionPeriodInterface $period): self
    {
        $opportunity = $this->instance->parent ?? $this->instance;

        $this->instance->registrationFrom = $period->getRegistrationFrom($opportunity);
        $this->instance->registrationTo   = $period->getRegistrationTo($opportunity);

        return $this;
    }

    /**
     * Cria e salva um pedido (inscrição) de um agente na fase de execução.
     * Vincula ao ID da inscrição aprovada na lastPhase para rastreabilidade.
     *
     * @param Registration $approved_registration Inscrição aprovada na lastPhase
     */
    public function createRequest(Registration $approved_registration): Registration
    {
        $pedido = new Registration();
        $pedido->opportunity = $this->instance;
        $pedido->owner       = $approved_registration->owner;
        $pedido->previousPhaseRegistrationId = $approved_registration->id;
        $pedido->save(true);

        return $pedido;
    }
}
