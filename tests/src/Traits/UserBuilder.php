<?php
namespace Tests\Traits;

use Tests\Builders;

trait UserBuilder {
    protected Builders\UserBuilder $userBuilder;

    function __initUserBuilder() {
        $this->userBuilder = new Builders\UserBuilder;
    }
}