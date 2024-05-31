<?php

declare(strict_types=1);

namespace App\DataFixtures;

use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\EventOpportunity;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\ProjectOpportunity;

class OpportunityFixtures extends Fixture implements DependentFixtureInterface
{
    public const OPPORTUNITY_ID_PREFIX = 'opportunity';
    public const OPPORTUNITY_ID_1 = 1;
    public const OPPORTUNITY_ID_2 = 2;
    public const OPPORTUNITY_ID_3 = 3;
    public const OPPORTUNITY_ID_4 = 4;

    public const EVENT_OPPORTUNITTIES = [
        [
            'id' => self::OPPORTUNITY_ID_1,
            'name' => 'Concurso Teste',
            'shortDescription' => 'Desperte sua criatividade e mostre seu talento no Concurso de Artes! Inscreva suas obras originais e concorra a prêmios incríveis.',
            'publishedRegistrations' => false,
            'area' => ['Arquitetura-Urbanismo'],
            'twitter' => 'admin',
            'instagram' => 'admin',
            'tag' => [
                'Concurso',
                'Artes',
                'Teste',
            ],
        ],
        [
            'id' => self::OPPORTUNITY_ID_2,
            'name' => 'Concurso Teste 2',
            'shortDescription' => 'Desperte sua criatividade e mostre seu talento no Concurso de Artes! Inscreva suas obras originais e concorra a prêmios incríveis.',
            'publishedRegistrations' => false,
            'area' => ['Arquitetura-Urbanismo'],
            'twitter' => 'admin',
            'instagram' => 'admin',
            'tag' => [
                'Concurso',
                'Artes',
                'Teste',
            ],
        ],
    ];

    public const PROJECT_OPPORTUNITTIES = [
        [
            'id' => self::OPPORTUNITY_ID_3,
            'name' => 'Projeto de Verão',
            'shortDescription' => 'Desperte sua criatividade e mostre seu talento no Concurso de Artes! Inscreva suas obras originais e concorra a prêmios incríveis.',
            'publishedRegistrations' => false,
            'area' => ['Arquitetura-Urbanismo'],
            'twitter' => 'admin',
            'instagram' => 'admin',
            'tag' => [
                'Concurso',
                'Artes',
                'Teste',
            ],
        ],
        [
            'id' => self::OPPORTUNITY_ID_4,
            'name' => 'O Projeto de verão falhou',
            'shortDescription' => 'Desperte sua criatividade e mostre seu talento no Concurso de Artes! Inscreva suas obras originais e concorra a prêmios incríveis.',
            'publishedRegistrations' => false,
            'area' => ['Arquitetura-Urbanismo'],
            'twitter' => 'admin',
            'instagram' => 'admin',
            'tag' => [
                'Concurso',
                'Artes',
                'Teste',
            ],
        ],
    ];

    public function getDependencies(): array
    {
        return [
            AgentFixtures::class,
            EventFixtures::class,
            ProjectFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->deleteAllDataFromTable(Opportunity::class);

        $agent = $this->getReference(AgentFixtures::AGENT_ID_PREFIX.'-'.AgentFixtures::AGENT_ID_1);
        $event = $this->getReference(EventFixtures::EVENT_ID_PREFIX.'-'.EventFixtures::EVENT_ID_1);
        $project = $this->getReference(ProjectFixtures::PROJECT_ID_PREFIX.'-'.ProjectFixtures::PROJECT_ID_3);

        foreach (self::EVENT_OPPORTUNITTIES as $opportunityData) {
            /** @var EventOpportunity $opportunity */
            $opportunity = $this->getSerializer()->denormalize($opportunityData, EventOpportunity::class);
            $opportunity->setRegistrationTo(new DateTime());
            $opportunity->setRegistrationFrom(new DateTime());
            $opportunity->setOwnerEntity($event);

            $this->setProperty($opportunity, 'owner', $agent);
            $this->setReference(sprintf('%s-%s', self::OPPORTUNITY_ID_PREFIX, $opportunityData['id']), $opportunity);

            $manager->persist($opportunity);
        }

        foreach (self::PROJECT_OPPORTUNITTIES as $opportunityData) {
            /** @var ProjectOpportunity $opportunity */
            $opportunity = $this->getSerializer()->denormalize($opportunityData, ProjectOpportunity::class);
            $opportunity->setRegistrationTo(new DateTime());
            $opportunity->setRegistrationFrom(new DateTime());
            $opportunity->setOwnerEntity($project);

            $this->setProperty($opportunity, 'owner', $agent);
            $this->setReference(sprintf('%s-%s', self::OPPORTUNITY_ID_PREFIX, $opportunityData['id']), $opportunity);

            $manager->persist($opportunity);
        }

        $manager->flush();
    }
}
