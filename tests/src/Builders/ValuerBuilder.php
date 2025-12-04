<?php

namespace Tests\Builders;

use Exception;
use Tests\Abstract\Builder;
use Tests\Traits\UserDirector;
use Tests\Traits\AgentDirector;
use MapasCulturais\Entities\Agent;
use Tests\Traits\EvaluationBuilder;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;

class ValuerBuilder extends Builder
{
    use AgentDirector,
        EvaluationBuilder,
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

    public function createDraftEvaluation(?Registration $registration = null): static
    {
        if ($registration) {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                registration: $registration
            );
        } else {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                opportunity: $this->instance->owner->opportunity
            );
        }

        $this->evaluationBuilder->save();

        return $this;
    }

    public function createConcludedEvaluation(?Registration $registration = null): static
    {
        if ($registration) {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                registration: $registration
            );
        } else {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                opportunity: $this->instance->owner->opportunity
            );
        }

        $this->evaluationBuilder->conclude();

        return $this;
    }

    public function createSentEvaluation(?Registration $registration = null): static
    {
        if ($registration) {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                registration: $registration
            );
        } else {
            $this->evaluationBuilder->reset(
                user: $this->instance->agent->user,
                opportunity: $this->instance->owner->opportunity
            );
        }

        $this->evaluationBuilder->send();

        return $this;
    }

    public function createDraftRegistrations(int $number_of_registrations): static
    {
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $this->createDraftEvaluation();
        }

        return $this;
    }

    public function createConcludedRegistrations(int $number_of_registrations): static
    {
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $this->createConcludedEvaluation();
        }

        return $this;
    }

    public function createSentRegistrations(int $number_of_registrations): static
    {
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $this->createSentEvaluation();
        }

        return $this;
    }
}
