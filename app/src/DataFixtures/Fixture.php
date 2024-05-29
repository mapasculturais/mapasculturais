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

    public function deleteAllDataFromTable(string $entityName, bool $resetSequence = true): void
    {
        $entityManager = $this->referenceRepository->getManager();
        $connection = $entityManager->getConnection();
        $tableName = $entityManager->getClassMetadata($entityName)->getTableName();

        if (true === $resetSequence) {
            $statement = $connection->prepare("
                ALTER SEQUENCE {$tableName}_id_seq RESTART WITH 1;
            ");
            $statement->execute();
        }

        $statement = $connection->prepare("DELETE FROM {$tableName}");
        $statement->execute();
    }
}
