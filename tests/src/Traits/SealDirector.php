<?php
namespace Tests\Traits;

use Tests\Directors;

trait SealDirector {
    protected Directors\SealDirector $sealDirector;

    function __initSealDirector() {
        $this->sealDirector = new Directors\SealDirector;
    }
}

