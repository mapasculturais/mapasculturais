<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SealFixtures extends Fixture implements DependentFixtureInterface
{
    public const SEAL_ID_PREFIX = 'seal';
    public const SEAL_ID_2 = 2;
    public const SEAL_ID_3 = 3;
    public const SEAL_ID_4 = 4;
    public const SEAL_ID_5 = 5;
    public const SEAL_ID_6 = 6;

    public const SEALS = [
        [
            'id' => self::SEAL_ID_2,
            'name' => 'Selo Feitoza',
            'shortDescription' => 'descrição curta do selo feitoza',
            'longDescription' => 'descrição longa do selo feitoza',
            'validPeriod' => 12,
            'status' => 1,
        ],
        [
            'id' => self::SEAL_ID_3,
            'name' => 'Selo Lima',
            'shortDescription' => 'descrição curta do selo lima',
            'longDescription' => 'descrição longa do selo lima',
            'validPeriod' => 12,
            'status' => 1,
        ],
        [
            'id' => self::SEAL_ID_4,
            'name' => 'Selo Moura',
            'shortDescription' => 'descrição curta do selo moura',
            'longDescription' => 'descrição longa do selo moura',
            'validPeriod' => 12,
            'status' => 1,
        ],
        [
            'id' => self::SEAL_ID_5,
            'name' => 'Selo Camilo',
            'shortDescription' => 'descrição curta do selo camilo',
            'longDescription' => 'descrição longa do selo camilo',
            'validPeriod' => 12,
            'status' => 1,
        ],
        [
            'id' => self::SEAL_ID_6,
            'name' => 'Selo Soares',
            'shortDescription' => 'descrição curta do selo soares',
            'longDescription' => 'descrição longa do selo soares',
            'validPeriod' => 12,
            'status' => 1,
        ],
    ];

    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function getDependencies(): array
    {
        return [
            AgentFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $manager = $this->referenceRepository->getManager();

        $owner = $manager->getRepository(Agent::class)->find(1);

        foreach (self::SEALS as $sealData) {
            $seal = $this->serializer->denormalize($sealData, Seal::class);
            $this->setProperty($seal, 'owner', $owner);
            $this->setReference(sprintf('%s-%s', self::SEAL_ID_PREFIX, $sealData['id']), $seal);
            $manager->persist($seal);
        }

        $manager->flush();
    }
}
