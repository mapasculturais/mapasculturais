<?php

namespace Tests\Builders;

use Exception;
use Tests\Traits\Faker;
use Tests\Abstract\Builder;
use Tests\Traits\UserDirector;
use Tests\Enums\ProponentTypes;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Space;
use Tests\Enums\EvaluationMethods;
use MapasCulturais\Entities\Project;
use Tests\Traits\RegistrationDirector;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationFieldConfiguration;

class OpportunityBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\EntityStatuses,
        Traits\EntityType,
        Traits\SealRelations,
        Traits\Taxonomies,
        Traits\EntityName,
        RegistrationDirector,
        UserDirector;

    protected Opportunity $instance;

    protected Opportunity $currentPhase;

    protected DataCollectionPhaseBuilder $firstPhaseBuilder;
    protected DataCollectionPhaseBuilder $lastPhaseBuilder;

    protected array $fieldsByIdentifier = [];

    public function reset(Agent $owner, Agent|Space|Project|Event $owner_entity, int $status = Opportunity::STATUS_ENABLED): self
    {
        $opportunity_class_name =  $owner_entity->opportunityClassName;

        /** @var Opportunity */
        $instance = new $opportunity_class_name;
        $instance->status = $status;
        $instance->ownerEntity = $owner_entity;

        $instance->owner = $owner;

        $this->instance = $instance;
        $this->currentPhase = $instance;

        $this->firstPhaseBuilder = new DataCollectionPhaseBuilder($this);
        $this->firstPhaseBuilder->reset($this->instance);

        $this->lastPhaseBuilder = new DataCollectionPhaseBuilder($this);
        $this->lastPhaseBuilder->reset($this->instance->lastPhase);

        return $this;
    }

    public function firstPhase(): DataCollectionPhaseBuilder
    {
        return $this->firstPhaseBuilder;
    }

    public function lastPhase(): DataCollectionPhaseBuilder
    {
        $this->lastPhaseBuilder->reset($this->instance->lastPhase);
        return $this->lastPhaseBuilder;
    }

    public function getInstance(): Opportunity
    {
        return $this->instance;
    }

    public function addDataCollectionPhase(): DataCollectionPhaseBuilder
    {
        $builder = new DataCollectionPhaseBuilder($this);
        $builder->reset();
        $builder->fillRequiredProperties();
        $builder->save();
        return $builder;
    }

    public function addEvaluationPhase(EvaluationMethods $evaluation_method): EvaluationPhaseBuilder
    {
        $builder = new EvaluationPhaseBuilder($this);
        $builder->reset($this->instance, $evaluation_method);

        return $builder;
    }

    public function fillRequiredProperties(): self
    {
        $instance = $this->instance;
        $instance->name = $this->faker->name();
        $instance->shortDescription = $this->faker->name();
        if (!$instance->type) {
            $this->setType(1);
        }

        return $this;
    }

    /**
     * Número de vagas da oportunidade
     * 
     * @param null|int $vacancies 
     * @return OpportunityBuilder 
     */
    public function setVacancies(?int $vacancies = null): self
    {
        $this->instance->vacancies = $vacancies;
        return $this;
    }

    /**
     * Límite de inscrições totais no edital
     * 
     * @param null|int $limit 
     * @return OpportunityBuilder 
     */
    public function setRegistrationLimit(?int $limit = null): self
    {
        $this->instance->registrationLimit = $limit;
        return $this;
    }

    /**
     * Limite de inscrições que o mesmo agente responsável pode fazer
     * 
     * @param null|int $limit 
     * @return OpportunityBuilder 
     */
    public function setRegistrationLimitPerOwner(?int $limit = null): self
    {
        $this->instance->registrationLimitPerOwner = $limit;
        return $this;
    }

    public function addCategory(?string $category = null, bool $save = true, bool $flush = true): self
    {
        $categories = $this->instance->registrationCategories;
        $categories[] = $category ?: $this->faker->text(10);
        $this->instance->registrationCategories = $categories;

        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function setCategories(array $categories = [], int $number_of_random_categories = 3, bool $save = true, bool $flush = true): self
    {
        if (empty($categories)) {
            for ($i = 0; $i < $number_of_random_categories; $i++) {
                $categories[] = $this->faker->text(10);
            }
        }

        $this->instance->registrationCategories = $categories;
        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function addProponentType(?ProponentTypes $proponent_type = null, bool $save = true, bool $flush = true): self
    {
        $available_proponent_types = array_map(fn($case) => $case->value, ProponentTypes::cases());
        
        $proponent_types = $this->instance->registrationProponentTypes ?: [];

        if(!$proponent_type) {
            $remaining_types = array_diff($available_proponent_types, $this->instance->registrationProponentTypes);
            $proponent_type_value = $remaining_types ? 
                $remaining_types[array_rand($remaining_types)] : 
                $available_proponent_types[array_rand($available_proponent_types)];

            $proponent_type = ProponentTypes::from($proponent_type_value);
        }

        // se já tem todos os proponent types
        if(!$proponent_type) {
            return $this;
        }

        $proponent_types = (array) $this->instance->registrationProponentTypes;
        $proponent_types[] = $proponent_type->value;

        $this->instance->registrationProponentTypes = $proponent_types;
        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function setProponentTypes(array $proponent_types = [], bool $save = true, bool $flush = true): self
    {
        $available_proponent_types = ['Coletivo', 'MEI', 'Pessoa Jurídica', 'Pessoa Física'];

        if (empty($proponent_types)) {
            foreach($available_proponent_types as $type) {
                $proponent_types[] = $type;
            }
        }

        $this->instance->registrationProponentTypes = $proponent_types;
        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function addRange(?string $label = null, ?int $limit = 0, ?int $value = 0, bool $save = true, bool $flush = true): self
    {
        $ranges = $this->instance->registrationRanges ?: [];

        $ranges[] = [
            'label' => $label ?: $this->faker->text(10),
            'limit' => $limit,
            'value' => $value
        ];

        $this->instance->registrationRanges = $ranges;
        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function setRanges(array $ranges = [], int $number_of_random_ranges = 3, bool $save = true, bool $flush = true): self
    {
        if (empty($ranges)) {
            for ($i = 0; $i < $number_of_random_ranges; $i++) {
                $ranges[] = [
                    'label' => $this->faker->text(10),
                    'limit' => $this->faker->randomNumber(),
                    'value' => $this->faker->randomNumber()
                ];
            }
        }

        $this->instance->registrationRanges = $ranges;
        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function saveField(string $identifier, RegistrationFieldConfiguration $field, ?Opportunity $opportunity = null): string
    {
        $opportunity = $opportunity ?: $this->instance;
        $key = "$opportunity:$identifier";

        $this->fieldsByIdentifier[$key] = $field;

        return $key;
    }

    public function getField(string $identifier, ?Opportunity $opportunity = null): ?RegistrationFieldConfiguration
    {
        $opportunity = $opportunity ?: $this->instance;
        $key = "$opportunity:$identifier";

        return $this->fieldsByIdentifier[$key] ?? null;
    }

    public function getFieldName(string $identifier, ?Opportunity $opportunity = null): ?string
    {
        $field = $this->getField($identifier, $opportunity);

        return $field ? $field->fieldName : null;
    }

    public function createDraftRegistrations(int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null, array $data = [], bool $fill_requered_properties = true, bool $save = true, bool $flush = true): static
    {
        $this->registrationDirector->createDraftRegistrations($this->instance, $number_of_registrations, $category, $proponent_type, $range, $data, $fill_requered_properties, $save, $flush);

        return $this;
    }

    public function createSentRegistrations(int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null, array $data = []): static
    {
        $this->registrationDirector->createSentRegistrations($this->instance, $number_of_registrations, $category, $proponent_type, $range, $data);

        return $this;
    }

    public function createSentRegistration(array $data): static
    {
        $this->registrationDirector->createSentRegistration($this->instance, $data);

        return $this;
    }
}