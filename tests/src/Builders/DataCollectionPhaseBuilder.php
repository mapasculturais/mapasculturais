<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use Tests\Abstract\Builder;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Traits\Faker;
use Tests\Traits\RegistrationFieldBuilder;

class DataCollectionPhaseBuilder extends Builder
{
    const FIELD_TYPE_AGENT_OWNER_FIELD = 'agent-owner-field';
    const FIELD_TYPE_AGENT_COLLECTIVE_FIELD = 'agent-collective-field';

    use Faker,
        RegistrationFieldBuilder;

    protected Opportunity $instance;

    /** @var RegistrationStep[] */
    protected array $steps = [];

    function __construct(private OpportunityBuilder $opportunityBuilder)
    {
        parent::__construct();
    }

    public function reset(?Opportunity $instance = null): self
    {
        if ($instance) {
            $this->instance = $instance;
            return $this;
        }

        $first_phase_instance = $this->opportunityBuilder->getInstance();
        $opportunity_class_name = $first_phase_instance->specializedClassName;

        $this->instance = new $opportunity_class_name;
        $this->instance->parent = $first_phase_instance;
        $this->instance->status = Opportunity::STATUS_PHASE;

        return $this;
    }

    public function getInstance(): Opportunity
    {
        return $this->instance;
    }

    public function done(): OpportunityBuilder
    {
        return $this->opportunityBuilder;
    }

    public function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->name;
        return $this;
    }

    public function setRegistrationPeriod(DataCollectionPeriodInterface $period): self
    {
        $opportunity = $this->instance->opportunity;

        $this->instance->registrationFrom = $period->getRegistrationFrom($opportunity);
        $this->instance->registrationTo = $period->getRegistrationTo($opportunity);
        
        return $this;
    }

    public function setRegistrationFrom(string $registration_from): self
    {
        $this->instance->registrationFrom = $registration_from;
        return $this;
    }

    public function setRegistrationTo(string $registration_to): self
    {
        $this->instance->registrationTo = $registration_to;
        return $this;
    }

    public function createStep(string $step_name, ?int $displayOrder = null, bool $save = true, bool $flush = true): self
    {
        $step = new RegistrationStep;
        $step->setOpportunity($this->instance);
        $step->name = $step_name;

        if (!is_null($displayOrder)) {
            $step->displayOrder = $displayOrder;
        }

        if ($save) {
            $step->save($flush);
        }

        $this->instance->registrationSteps[] = $step;

        $this->steps[$step_name] = $step;
        
        return $this;
    }

    protected function getSpecialFieldMetadataOptions(string $field_type, string $entity_field): array 
    {
        $app = App::i();
        /** @todo se for um campo @ de uma uma taxonomia (área de atuação) */
        if(in_array($field_type, [self::FIELD_TYPE_AGENT_OWNER_FIELD, self::FIELD_TYPE_AGENT_COLLECTIVE_FIELD])) {
            if($meta = $app->getRegisteredMetadataByMetakey($entity_field, Agent::class)) {
                return $meta->options ?: [];
            }
        }
        /** @todo else if field é de espaço */
        return [];
    }

    public function createField(string $identifier, string $field_type, string $title = '', bool $required = false, array $categories = [], array $ranges = [], array $proponent_types = [], string $field_condition = '', array $options = [], ?string $step_name = null, bool $save = true, bool $flush = false): self
    {
        $builder = $this->registrationFieldBuilder;

        if(empty($this->steps)) {
            throw new Exception("Antes de criar um campo é necessário criar uma etapa (function addRegistrationStep)");
        }

        $step_name = array_key_last($this->steps);
        $step = $this->steps[$step_name];

        $builder->reset($this->instance, $field_type, $identifier)
            ->setStep($step)
            ->setTitle($title)
            ->setRequired($required)
            ->setCategories($categories)
            ->setRanges($ranges)
            ->setProponentTypes($proponent_types)
            ->setFieldCondition($field_condition)
            ->setOptions($options);

        if($save) {
            $builder->save($flush);
        }

        $this->opportunityBuilder->saveField($identifier, $builder->getInstance());
        
        return $this;
    }

    public function createSpecialField(string $identifier, string $field_type, string $entity_field, string $title = '', bool $required = false, array $categories = [], array $ranges = [], array $proponent_types = [], string $field_condition = '', array $options = [], bool $save = true, bool $flush = false): self
    {
        $builder = $this->registrationFieldBuilder;

        // se não foi informado a lista de opções, tenta pegar do registro do metadado da 
        $options = $options ?: $this->getSpecialFieldMetadataOptions($field_type, $entity_field);

        $this->createField(
            $identifier,
            $field_type, 
            title: $title,
            required: $required, 
            categories: $categories, 
            ranges: $ranges, 
            proponent_types: $proponent_types, 
            field_condition: $field_condition,
            options: $options,
            save: false
        );
        
        $builder->setEntityField($entity_field);

        if($save) {
            $builder->save($flush);
        }

        return $this;
    }

    public function createOwnerField(string $identifier, string $entity_field, string $title = '', bool $required = false, array $categories = [], array $ranges = [], array $proponent_types = [], string $field_condition = '', array $options = [], bool $save = true, bool $flush = false): self
    {
        return $this->createSpecialField(
            $identifier,
            self::FIELD_TYPE_AGENT_OWNER_FIELD, 
            entity_field: $entity_field,  
            title: $title,
            required: $required, 
            categories: $categories, 
            ranges: $ranges, 
            proponent_types: $proponent_types, 
            field_condition: $field_condition,
            options: $options,
            save: $save,
            flush: $flush
        );
    }

    public function createCollectiveField(string $identifier, string $entity_field, string $title = '', bool $required = false, array $categories = [], array $ranges = [], array $proponent_types = [], string $field_condition = '', array $options = [], bool $save = true, bool $flush = false): self
    {
        return $this->createSpecialField(
            $identifier,
            self::FIELD_TYPE_AGENT_COLLECTIVE_FIELD, 
            entity_field: $entity_field,  
            title: $title,
            required: $required, 
            categories: $categories, 
            ranges: $ranges, 
            proponent_types: $proponent_types, 
            field_condition: $field_condition,
            options: $options,
            save: $save,
            flush: $flush
        );
    }

    public function enableQuotaQuestion(): self
    {
        if(!$this->instance->isFirstPhase){
            throw new Exception('só é possível habilitar a pergunta "Vai concorrer às cotas" na primeira fase');
        }

        $this->instance->enableQuotasQuestion = '1';

        return $this;
    }

    public function disableQuotaQuestion(): self
    {
        if(!$this->instance->isFirstPhase){
            throw new Exception('só é possível habilitar a pergunta "Vai concorrer às cotas" na primeira fase');
        }

        $this->instance->enableQuotasQuestion = '0';

        return $this;
    }
}
