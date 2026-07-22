<?php
namespace Tests\Traits;

use Tests\Directors;

trait SystemRoleDirector {
    protected Directors\SystemRoleDirector $systemRoleDirector;

    function __initSystemRoleDirector() {
        $this->systemRoleDirector = new Directors\SystemRoleDirector;
    }
}

