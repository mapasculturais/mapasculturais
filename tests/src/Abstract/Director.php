<?php

namespace Tests\Abstract;

class Director
{
    final function __construct()
    {
        // chama os inicializadores das classes ou traits
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (str_starts_with($method, '__init')) {
                $this->$method();
            }
        }
    }
}
