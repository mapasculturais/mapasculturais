<?php

namespace Tests\Abstract;

abstract class Builder
{
    final function __construct()
    {
        
        if (!property_exists($this, 'instance')) {
            throw new \Exception(get_called_class() . '::instance property is required');
        }
        
        if (!method_exists($this, 'reset')) {
            throw new \Exception(get_called_class() . '::reset method is required');
        }
    
        // chama os inicializadores das classes ou traits
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (str_starts_with($method, '__init')) {
                $this->$method();
            }
        }
    }

    function save(): static
    {
        $this->getInstance()->save(true);
        return $this;
    }

    abstract function getInstance();

    abstract function fillRequiredProperties(): self;
}
