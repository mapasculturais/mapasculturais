<?php

namespace Tests\Traits;

use Tests\Directors;

trait PersonalAccessTokenDirector
{
    protected Directors\PersonalAccessTokenDirector $personalAccessTokenDirector;

    function __initPersonalAccessTokenDirector()
    {
        $this->personalAccessTokenDirector = new Directors\PersonalAccessTokenDirector;
    }
}
