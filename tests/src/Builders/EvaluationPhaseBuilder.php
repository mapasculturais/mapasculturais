<?php

namespace Tests\Builders;

use MapasCulturais\App;
use Tests\Traits\Faker;
use Tests\Abstract\Builder;
use Tests\Traits\UserDirector;
use MapasCulturais\Entities\Agent;
use Tests\Enums\EvaluationMethods;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\EvaluationPeriodInterface;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\EvaluationMethodConfigurationBuilder;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;

class EvaluationPhaseBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        UserDirector;

    const PERIOD_AFTER = 'after';
    const PERIOD_CONCURRENT = 'concurrent';
    const PERIOD_CONCURRENT_ENDING_AFTER = 'concurrent-after';


    protected EvaluationMethodConfiguration $instance;
    protected EvaluationMethods $evaluationMethod;
    protected ValuerBuilder $valuerBuilder;

    /**
     * @var EvaluationMethodConfigurationAgentRelation[][]
     */
    protected array $valuersRelations = [];


    /**
     * @var Agent[]
     */
    protected array $valuers = [];

    function __construct(protected OpportunityBuilder $opportunityBuilder)
    {
        $this->valuerBuilder = new ValuerBuilder($this);
        parent::__construct();
    }

    public function reset(Opportunity $opportunity, EvaluationMethods $evaluation_method): self
    {
        $this->instance = new EvaluationMethodConfiguration;
        $this->evaluationMethod = $evaluation_method;

        $this->instance->opportunity = $opportunity;
        $this->instance->type = $evaluation_method->name;

        return $this;
    }

    public function getInstance(): EvaluationMethodConfiguration
    {
        return $this->instance;
    }

    public function done(): OpportunityBuilder
    {
        $this->instance->opportunity->evaluationMethodConfiguration = $this->instance;
        return $this->opportunityBuilder;
    }

    public function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->name;

        return $this;
    }

    public function setEvaluationPeriod(EvaluationPeriodInterface $period): self
    {
        $opportunity = $this->instance->opportunity;

        $this->instance->evaluationFrom = $period->getEvaluationFrom($opportunity);
        $this->instance->evaluationTo = $period->getEvaluationTo($opportunity);

        return $this;
    }

    public function setEvaluationFrom(string $evaluation_from): self
    {
        $this->instance->evaluationFrom = $evaluation_from;

        return $this;
    }

    public function setEvaluationTo(string $evaluation_to): self
    {
        $this->instance->evaluationTo = $evaluation_to;

        return $this;
    }

    public function addValuers(int $number_of_valuers, string $committee): self
    {
        for ($i = 1; $i <= $number_of_valuers; $i++) {
            $this->addValuer($committee, "{$committee} - valuer {$i}");
        }

        return $this;
    }

    public function addValuer(string $committe, string $name, ?Agent $valuer = null): ValuerBuilder
    {
        if ($valuer && isset($this->valuers[$name]) && !$valuer->equals($this->valuers[$name]->agent)) {
            throw new \Exception("o avaliador informado não é o mesmo já utilizado com o mesmo nome");
        }

        if (isset($this->valuers[$name])) {
            $valuer = $this->valuers[$name]->agent;
        }

        $this->valuerBuilder->reset(committee_name: $committe, valuer: $valuer, agent_name: $name);

        $valuer_relation = $this->valuerBuilder->getInstance();

        $this->valuersRelations[$committe] = $this->valuersRelations[$committe] ?? [];
        $this->valuersRelations[$committe][$name] = $valuer_relation;
        $this->valuers[$name] = $valuer_relation;

        return $this->valuerBuilder;
    }

    public function withValuer(string $committe, string $name): ValuerBuilder
    {
        if (!isset($this->valuersRelations[$committe][$name])) {
            throw new \Exception("Avaliador {$name} não encontrado na comissão {$committe}");
        }

        $this->valuerBuilder->reset($this->valuersRelations[$committe][$name]);

        return $this->valuerBuilder;
    }

    public function redistributeCommitteeRegistrations(): static
    {
        $this->instance->redistributeCommitteeRegistrations();

        return $this;
    }

    public function createEvaluations(string $committee, int $number_of_evaluations, array $evaluation_data = [], int $status = RegistrationEvaluation::STATUS_DRAFT, ?int $valuer_index = null ): static
    {
        $app = App::i();
        // Commite de avaliação
        $valuers = $valuer_index ? [$this->instance->agentRelations[$valuer_index]] : $this->instance->agentRelations;
        $registratons = $app->repo('Registration')->findBy(['opportunity' => $this->instance->opportunity]);
        
        foreach($registratons as $registration) {
            foreach($valuers as $valuer) {
                for ($i = 0; $i < $number_of_evaluations; $i++) {
                    $evaluation = new RegistrationEvaluation();
                    $evaluation->registration = $registration;
                    $evaluation->user = $valuer;
                    $evaluation->status = $status;
                    eval(\psy\sh());
                    $evaluation->save(true);
                }
            }
        }

        
        foreach($valuers as $valuer) {
            for ($i = 0; $i < $number_of_evaluations; $i++) {
                $evaluation = new RegistrationEvaluation();
                $evaluation->committee = $committee;
                $evaluation->user = $valuer->agent->owner->user;
                $evaluation->status = $status;
                $evaluation->opportunity = $this->instance->opportunity;
                $evaluation->save(true);
            }
        }
       

        return $this;
    }

    public function setCommitteeValuersPerRegistration(string $committee, int $number_of_valuers_per_registration): self
    {
        $valuers_per_registration = $this->instance->valuersPerRegistration ?: (object)[];
        $valuers_per_registration->$committee = $number_of_valuers_per_registration;
        $this->instance->valuersPerRegistration = $valuers_per_registration;

        return $this;
    }

    public function setCommitteeFilterCategory(string $committee, array $categories): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $fetch_fields->$committee = $fetch_fields->$committee ?? [];

        $fetch_fields->$committee['category'] = $categories;

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    /**
     * 
     * @param string $committee 
     * @param ProponentTypes[] $proponent_types 
     * @return EvaluationPhaseBuilder 
     */
    public function setCommitteeFilterProponentType(string $committee, array $proponent_types): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $fetch_fields->$committee = $fetch_fields->$committee ?? [];

        $fetch_fields->$committee['proponentType'] = $proponent_types;

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function setCommitteeFilterRange(string $committee, array $ranges): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $fetch_fields->$committee = $fetch_fields->$committee ?? [];

        $fetch_fields->$committee['range'] = $ranges;

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function setCommitteeFilterField(string $committee, string $field_identifier, array $answers): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $fetch_fields->$committee = $fetch_fields->$committee ?? [];

        $field = $this->opportunityBuilder->getFieldName($field_identifier);

        $fetch_fields->$committee[$field] = $answers;

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function setCommitteeFilterBySentTimestamp(string $committee, ?string $from_datetime = null, ?string $to_datetime = null): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $fetch_fields->$committee = $fetch_fields->$committee ?? [];

        $fetch_fields->$committee['sentTimestamp'] = (object) ['from' => $from_datetime, 'to' => $to_datetime];

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function config(): EvaluationMethodConfigurationBuilder|EvaluationMethodTechnicalBuilder
    {
        $builder = $this->evaluationMethod->builder($this, $this->opportunityBuilder);

        return $builder->reset($this->instance);
    }
}
