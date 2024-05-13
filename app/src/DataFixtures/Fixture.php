<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ReflectionClass;

abstract class Fixture extends AbstractFixture implements FixtureInterface
{
    public function setProperty($obj, string $property, $value): void
    {
        $reflection = new ReflectionClass($obj);
        $owner = $reflection->getProperty($property);
        $owner->setValue($obj, $value);
    }
}
