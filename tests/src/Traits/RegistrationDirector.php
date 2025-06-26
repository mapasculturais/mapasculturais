<?php
namespace Tests\Traits;

use Tests\Directors;

trait RegistrationDirector {
    protected Directors\RegistrationDirector $registrationDirector;

    function __initRegistrationDirector() {
        $this->registrationDirector = new Directors\RegistrationDirector;
    }
}