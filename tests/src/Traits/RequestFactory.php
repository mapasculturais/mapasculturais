<?php
namespace Tests\Traits;

use Tests\Factories;

trait RequestFactory {
    protected Factories\RequestFactory $requestFactory;

    function __initRequestFactor() {
        $this->requestFactory = new Factories\RequestFactory;
    }
}