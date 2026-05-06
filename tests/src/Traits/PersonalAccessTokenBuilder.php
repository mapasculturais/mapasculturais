<?php

namespace Tests\Traits;

use Tests\Builders;

trait PersonalAccessTokenBuilder
{
    protected Builders\PersonalAccessTokenBuilder $personalAccessTokenBuilder;

    function __initPersonalAccessTokenBuilder()
    {
        $this->personalAccessTokenBuilder = new Builders\PersonalAccessTokenBuilder;
    }
}
