<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Enum\EntityStatusEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Seal;

class SealFixtures extends Fixture implements DependentFixtureInterface
{
    public const SEAL_ID_PREFIX = 'seal';
    public const SEAL_ID_1 = 1;
    public const SEAL_ID_2 = 2;
    public const SEAL_ID_3 = 3;
    public const SEAL_ID_4 = 4;
    public const SEAL_ID_5 = 5;
    public const SEAL_ID_6 = 6;

    public const SEALS = [
        [
            'id' => self::SEAL_ID_1,
            'name' => 'Selo Mapas',
            'shortDescription' => '',
            'longDescription' => '',
            'validPeriod' => 9000,
            'status' => EntityStatusEnum::DISABLED,
        ],
        [
            'id' => self::SEAL_ID_2,
            'name' => 'Selo Feitoza',
            'shortDescription' => 'descrição curta do selo feitoza',
            'longDescription' => 'descrição longa do selo feitoza',
            'validPeriod' => 24,
            'status' => EntityStatusEnum::DISABLED,
        ],
        [
            'id' => self::SEAL_ID_3,
            'name' => 'Selo Lima',
            'shortDescription' => 'descrição curta do selo lima',
            'longDescription' => 'descrição longa do selo lima',
            'validPeriod' => 12,
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::SEAL_ID_4,
            'name' => 'Selo Moura',
            'shortDescription' => 'descrição curta do selo moura',
            'longDescription' => 'descrição longa do selo moura',
            'validPeriod' => 18,
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::SEAL_ID_5,
            'name' => 'Selo Camilo',
            'shortDescription' => 'descrição curta do selo camilo',
            'longDescription' => 'descrição longa do selo camilo',
            'validPeriod' => 12,
            'status' => EntityStatusEnum::ENABLED,
        ],
        [
            'id' => self::SEAL_ID_6,
            'name' => 'Selo Soares',
            'shortDescription' => 'descrição curta do selo soares',
            'longDescription' => 'descrição longa do selo soares',
            'validPeriod' => 6,
            'status' => EntityStatusEnum::ENABLED,
        ],
    ];

    public function getDependencies(): array
    {
        return [
            AgentFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->deleteAllDataFromTable(Seal::class);

        $owner = $this->getReference(AgentFixtures::AGENT_ID_PREFIX.'-'.AgentFixtures::AGENT_ID_1);

        foreach (self::SEALS as $sealData) {
            $sealData['status'] = $sealData['status']->getValue();
            $seal = $this->getSerializer()->denormalize($sealData, Seal::class);
            $this->setProperty($seal, 'owner', $owner);
            $this->setReference(sprintf('%s-%s', self::SEAL_ID_PREFIX, $sealData['id']), $seal);
            $manager->persist($seal);
        }

        $manager->flush();
    }
}
