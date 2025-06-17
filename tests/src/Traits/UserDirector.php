<?php
namespace Tests\Traits;

use Tests\Directors;

trait UserDirector {
    protected Directors\UserDirector $userDirector;

    function __initUserDirector() {
        $this->userDirector = new Directors\UserDirector;
    }
}