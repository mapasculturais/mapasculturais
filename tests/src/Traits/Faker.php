<?php

namespace Tests\Traits;

use Faker as PHPFaker;

trait Faker
{
    protected PHPFaker\Generator $faker;

    protected function __initFaker()
    {
        $this->faker = PHPFaker\Factory::create('pt_BR');
    }
}
