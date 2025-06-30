<?php
namespace Tests\Traits;

use Tests\Builders;

trait RegistrationBuilder {
    protected Builders\RegistrationBuilder $registrationBuilder;

    function __initRegistrationBuilder() {
        $this->registrationBuilder = new Builders\RegistrationBuilder;
    }
}