<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class RegistrationFieldBuilder extends Builder
{
    use Faker;

    protected RegistrationFieldConfiguration $instance;

    protected array $displayOrder = [];

    /**
     * @var RegistrationFieldConfiguration[]
     */
    protected array $fieldsByIdentifier = [];

    protected function getFieldByIdentifier(string $field_identifier): RegistrationFieldConfiguration
    {
        if (!isset($this->fieldsByIdentifier[$field_identifier])) {
            throw new Exception('O field ' . $field_identifier . " não existe");
        }

        return $this->fieldsByIdentifier[$field_identifier];
    }

    public function reset(Opportunity $owner, string $field_type, string $field_identifier): self
    {
        /*
          RegistrationFieldConfiguration properties
            - Opportunity $owner
            - RegistrationStep $step --
            - string $title -- 
            - string $description --
            - int $maxSize
            - bool $required --
            - string $fieldType -- 
            - int $displayOrder --
            - array $fieldOptions
            - object $config
            - bool $conditional --
            - string $conditionalField --
            - string $conditionalValue --

            - array $categories --
            - array $registrationRanges --
            - array $proponentTypes --
        */
        $this->instance = new RegistrationFieldConfiguration;
        $this->instance->owner = $owner;
        $this->instance->fieldType = $field_type;

        $this->fieldsByIdentifier[$field_identifier] = $this->instance;

        return $this;
    }

    public function fillRequiredProperties(): Builder
    {
        $this->setTitle();
        $this->setDescription();
        $this->setDisplayOrder();
        return $this;
    }

    public function getInstance(): RegistrationFieldConfiguration
    {
        return $this->instance;
    }

    public function setStep(RegistrationStep $step): self
    {
        $this->instance->step = $step;
        return $this;
    }

    public function setTitle(?string $title = null): self
    {
        $this->instance->title = $title ?: $this->faker->text(40);

        return $this;
    }

    public function setDescription(?string $description = null): self
    {
        $this->instance->description = $description ?: $this->faker->text(120);

        return $this;
    }

    public function setRequired(bool $required): self
    {
        $this->instance->required = $required;

        return $this;
    }

    public function setDisplayOrder(?int $display_order = null): self
    {
        $opportunity_id = $this->instance->owner->id;
        $this->displayOrder[$opportunity_id] = $this->displayOrder[$opportunity_id] ?? 0;

        $display_order = $display_order ?: $this->displayOrder[$opportunity_id]++;

        if ($display_order > $this->displayOrder[$opportunity_id]) {
            $this->displayOrder[$opportunity_id] = $display_order;
        }

        $this->instance->displayOrder = $display_order;

        return $this;
    }

    public function setCategories(array $categories): self
    {
        $this->instance->categories = array_values(array_unique($categories));

        return $this;
    }

    public function addCategory(string $category): self
    {
        $categories = $this->instance->categories;
        $categories[] = $category;

        $this->setCategories($categories);

        return $this;
    }

    public function setRanges(array $ranges): self
    {
        $this->instance->registrationRanges = array_values(array_unique($ranges));

        return $this;
    }

    public function addRange(string $range): self
    {
        $ranges = $this->instance->registrationRanges;
        $ranges[] = $range;

        $this->setRanges($ranges);

        return $this;
    }

    public function setProponentTypes(array $proponent_types): self
    {
        $this->instance->proponentTypes = array_values(array_unique($proponent_types));

        return $this;
    }

    public function addProponentType(string $proponent_type): self
    {
        $proponent_types = $this->instance->proponentTypes;
        $proponent_types[] = $proponent_type;

        $this->setProponentTypes($proponent_types);

        return $this;
    }

    /**
     * O $condition deve ser no formato `field_identifier:valor` 
     * por exemplo, se foi criado um campo de seleção com 
     * field_identifier 'campo1' e valores 'valor 1', 'valor 2',
     * o $condition deve ser algo como "campo1:valor 2"
     * 
     * @param string $field_condition 
     * @return RegistrationFieldBuilder 
     * @throws Exception 
     */
    public function setFieldCondition(string $field_condition): self
    {
        if (!$field_condition) {
            return $this;
        }

        list($field_identifier, $condition_value) = array_map('trim', explode(':', $field_condition, 2));

        $this->instance->conditional = true;
        $condition_field = $this->getFieldByIdentifier($field_identifier);

        $this->instance->conditionalField = $condition_field->fieldName;
        $this->instance->conditionalValue = $condition_value;

        return $this;
    }

    public function unsetFieldCondition(): self
    {
        $this->instance->conditional = false;

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->instance->fieldOptions = $options;

        return $this;
    }

    public function setEntityField(string $property_or_metadata): self
    {
        $config = $this->instance->config;
        $config['entityField'] = $property_or_metadata;
        $this->instance->config = $config;

        return $this;
    }
}
