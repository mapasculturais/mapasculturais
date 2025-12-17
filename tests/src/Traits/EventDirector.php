<?php
namespace Tests\Traits;

use Tests\Directors;

trait EventDirector {
    protected Directors\EventDirector $eventDirector;

    function __initEventDirector() {
        $this->eventDirector = new Directors\EventDirector;
    }
}