<?php

namespace Tests\Abstract;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entity;

/** @property Entity $instance */
abstract class Builder
{
    function __construct()
    {

        if (!property_exists($this, 'instance')) {
            throw new \Exception(get_called_class() . '::instance property is required');
        }

        if (!method_exists($this, 'reset')) {
            throw new \Exception(get_called_class() . '::reset() method is required');
        }

        // chama os inicializadores das classes ou traits
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (str_starts_with($method, '__init')) {
                $this->$method();
            }
        }
    }

    function save(bool $flush = true): static
    {
        $this->getInstance()->save($flush);
        $app = App::i();
        $app->persistPCachePendingQueue();
        return $this;
    }

    function refresh(): self
    {
        $this->instance = $this->instance->refreshed();
        return $this;
    }

    abstract function getInstance();

    abstract function fillRequiredProperties(): self;
}
