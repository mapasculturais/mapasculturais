<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use Tests\Abstract\Builder;
use Tests\Traits\AgentDirector;
use Tests\Traits\UserDirector;

class ValuerBuilder extends Builder
{
    use AgentDirector,
        UserDirector;

    protected EvaluationMethodConfigurationAgentRelation $instance;

    public function __construct(private EvaluationPhaseBuilder $evaluationPhaseBuilder)
    {
        parent::__construct();
    }

    public function getInstance(): EvaluationMethodConfigurationAgentRelation
    {
        return $this->instance;
    }

    public function fillRequiredProperties(): static
    {
        return $this;
    }

    public function done(): EvaluationPhaseBuilder
    {
        $this->instance->save(true);
        return $this->evaluationPhaseBuilder;
    }

    public function reset(?EvaluationMethodConfigurationAgentRelation $instance = null, ?string $committee_name = null, ?Agent $valuer = null, ?string $agent_name = null): static
    {
        if ($instance) {
            $this->instance = $instance;
            return $this;
        }

        if (!$valuer) {
            $valuer_user = $this->userDirector->createUser();
            $valuer = $valuer_user->profile;
            
            if ($agent_name) {
                $valuer->name = $agent_name;
                $valuer->save(true);
            }
        }

        if (is_null($committee_name)) {
            throw new Exception('o nome da comissão é obrigatório se a instância não foi informada');
        }

        $this->instance = $this->evaluationPhaseBuilder->getInstance()->createAgentRelation(
            agent: $valuer,
            group: $committee_name,
            has_control: true
        );

        return $this;
    }

    public function maxRegistrations(?int $max_registrations): static
    {
        $this->instance->maxRegistrations = $max_registrations;

        return $this;
    }
}
