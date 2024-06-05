<?php

declare(strict_types=1);

namespace App\Service;

use ReflectionClass;
use ReflectionException;

abstract class AbstractService
{
    /**
     * @throws ReflectionException
     */
    public function setProperty($obj, string $property, $value): void
    {
        $reflection = new ReflectionClass($obj);
        $owner = $reflection->getProperty($property);
        $owner->setValue($obj, $value);
    }
}
