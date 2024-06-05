<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ReflectionClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class Fixture extends AbstractFixture implements FixtureInterface
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

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
