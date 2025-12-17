<?php
namespace Tests\Traits;

use Tests\Directors;

trait ProjectDirector {
    protected Directors\ProjectDirector $projectDirector;

    function __initProjectDirector() {
        $this->projectDirector = new Directors\ProjectDirector;
    }
}