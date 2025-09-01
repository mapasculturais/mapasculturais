<?php

namespace Tests\Traits;

use MapasCulturais\Entities\Opportunity;
use Tests\Builders;

trait RegistrationFieldBuilder
{
    protected Builders\RegistrationFieldBuilder $registrationFieldBuilder;

    function __initRegistrationBuilder()
    {
        $this->registrationFieldBuilder = new Builders\RegistrationFieldBuilder;
    }
}
