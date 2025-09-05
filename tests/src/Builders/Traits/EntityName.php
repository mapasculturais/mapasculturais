<?php

namespace Tests\Builders\Traits;

use Tests\Traits\Faker;

/** @property Entities\Agent|Entities\Space|Entities\Project|Entities\Opportunity $instance */
trait EntityName
{
    use Faker;

    function setName(?string $name = null): self
    {
        if (is_null($name)) {
            $name = $this->faker->name;
        }

        $this->instance->name = $name;

        return $this;
    }
}
