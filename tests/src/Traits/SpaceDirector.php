<?php
namespace Tests\Traits;

use Tests\Directors;

trait SpaceDirector {
    protected Directors\SpaceDirector $spaceDirector;

    function __initSpaceDirector() {
        $this->spaceDirector = new Directors\SpaceDirector;
    }
}