<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\Builder;
use Tests\Abstract\EvaluationBuilder as AbstractEvaluationBuilder;
use Tests\Builders\EvaluationDocumentaryBuilder;
use Tests\Builders\EvaluationQualificationBuilder;
use Tests\Builders\EvaluationSimpleBuilder;
use Tests\Traits\AgentDirector;
use Tests\Traits\EvaluationBuilder;
use Tests\Traits\UserDirector;

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
        $this->instance->agent = $this->instance->agent->refreshed();
        $this->instance->owner = $this->instance->owner->refreshed();

        $this->save(true);
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
            agent: $valuer->refreshed(),
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
        $this->evaluationBuilder->fillRequiredProperties();
        $this->evaluationBuilder->save();

        return $this;
    }

    public function createConcludedEvaluation(?Registration $registration = null): static
    {
        $this->createDraftEvaluation($registration);

        $this->evaluationBuilder->conclude();

        return $this;
    }

    public function createSentEvaluation(?Registration $registration = null): static
    {
        $this->createDraftEvaluation($registration);

        $this->evaluationBuilder->send();

        return $this;
    }

    public function evaluation(?Registration $registration = null): AbstractEvaluationBuilder
    {
        $evaluation_method_config = $this->evaluationPhaseBuilder->getInstance();
        $evaluation_method_slug = $evaluation_method_config->type;
        
        // TODO: Verificar se é necessário essa parte
        // Criar a avaliação primeiro usando o builder que já existia
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
        
        $this->evaluationBuilder->fillRequiredProperties();
        $evaluation_instance = $this->evaluationBuilder->getInstance();
        
        // Retornar o builder específico baseado no tipo
        if ($evaluation_method_slug->id == 'simple') {
            $builder = new EvaluationSimpleBuilder($this->evaluationPhaseBuilder);
        }
        
        if ($evaluation_method_slug->id == 'documentary') {
            $builder = new EvaluationDocumentaryBuilder($this->evaluationPhaseBuilder);
        }

        if ($evaluation_method_slug->id == 'qualification') {
            $builder = new EvaluationQualificationBuilder($this->evaluationPhaseBuilder);
        }

        return $builder->reset($evaluation_instance);
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

    public function registrationList(array $registration_numbers, bool $exclusive = false): static
    {
        $this->instance->metadata->registrationList = $registration_numbers;
        $this->instance->metadata->registrationListExclusive = $exclusive;

        return $this;
    }
}
