<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;
use Tests\Traits\UserDirector;

class RegistrationBuilder extends Builder
{
    use Faker,
        UserDirector;

    protected Registration $instance;

    public function reset(Opportunity $opportunity, ?Agent $owner = null): self
    {
        $opportunity->registerRegistrationMetadata();
        $this->instance = new Registration;
        $this->instance->opportunity = $opportunity;
        $this->instance->owner = $owner ?: $this->userDirector->createUser()->profile;

        return $this;
    }

    public function getInstance(): Registration
    {
        return $this->instance;
    }

    public function fillRequiredProperties(): self
    {
        $instance = $this->instance;
        $opportunity = $this->instance->opportunity;

        if ($opportunity->registrationCategories && !$instance->category) {
            $this->setCategory();
        }

        if ($opportunity->registrationProponentTypes && !$instance->proponentType) {
            $this->setProponentType();
        }

        if ($opportunity->registrationRanges && !$instance->range) {
            $this->setRange();
        }

        /** @todo Coletivo */

        return $this;
    }

    public function setCategory(?string $category = null): self
    {
        $opportunity = $this->instance->opportunity;

        if (!$opportunity->registrationCategories) {
            return $this;
        }

        if ($category) {
            $this->instance->category = $category;
            return $this;
        }

        $rand_index = array_rand($opportunity->registrationCategories);
        $category = $opportunity->registrationCategories[$rand_index];
        $this->instance->category = $category;

        return $this;
    }

    public function setProponentType(?string $proponent_type = null): self
    {
        $opportunity = $this->instance->opportunity;

        if (!$opportunity->registrationProponentTypes) {
            return $this;
        }

        if ($proponent_type) {
            $this->instance->proponentType = $proponent_type;
            return $this;
        }

        $rand_index = array_rand($opportunity->registrationProponentTypes);
        $proponent_type = $opportunity->registrationProponentTypes[$rand_index];
        $this->instance->proponentType = $proponent_type;

        return $this;
    }

    public function setRange(?string $range = null): self
    {
        $opportunity = $this->instance->opportunity;

        if (!$opportunity->registrationRanges) {
            return $this;
        }

        if ($range) {
            $this->instance->range = $range;
            return $this;
        }

        $rand_index = array_rand($opportunity->registrationRanges);
        $range = $opportunity->registrationRanges[$rand_index];
        $this->instance->range = $range['label'];

        return $this;
    }

    public function send(): self
    {
        $this->instance->send();

        return $this;
    }
}
