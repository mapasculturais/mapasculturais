<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;

class AgentFixtures extends Fixture
{
    public const AGENT_ID_PREFIX = 'agent';
    public const AGENT_ID_2 = 2;
    public const AGENT_ID_3 = 3;
    public const AGENT_ID_4 = 4;
    public const AGENT_ID_5 = 5;
    public const AGENT_ID_6 = 6;

    public const AGENTS = [
        [
            'id' => self::AGENT_ID_2,
            'name' => 'Alessandro Feitoza',
            'shortDescription' => 'Agente Feitoza',
            'longDescription' => '',
            'status' => 1,
        ],
        [
            'id' => self::AGENT_ID_3,
            'name' => 'Henrique Lima',
            'shortDescription' => 'Agente Lima',
            'longDescription' => '',
            'status' => 1,
        ],
        [
            'id' => self::AGENT_ID_4,
            'name' => 'Anna Kelly Moura',
            'shortDescription' => 'Agente Moura',
            'longDescription' => '',
            'status' => 1,
        ],
        [
            'id' => self::AGENT_ID_5,
            'name' => 'Sara Camilo',
            'shortDescription' => 'Agente Camilo',
            'longDescription' => '',
            'status' => 1,
        ],
        [
            'id' => self::AGENT_ID_6,
            'name' => 'Talyson Soares',
            'shortDescription' => 'Agente Soares',
            'longDescription' => 'talyson soares',
            'status' => 1,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $manager = $this->referenceRepository->getManager();

        $user = $manager->getRepository(User::class)->find(1);

        foreach (self::AGENTS as $agentData) {
            $agent = new Agent($user);
            $agent->name = $agentData['name'];
            $agent->shortDescription = $agentData['shortDescription'];
            $agent->longDescription = $agentData['longDescription'];
            $this->setReference(sprintf('%s-%s', self::AGENT_ID_PREFIX, $agentData['id']), $agent);
            $manager->persist($agent);
        }

        $manager->flush();
    }
}
