<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Opportunity;
use Symfony\Component\Serializer\SerializerInterface;

class OpportunityFixtures extends Fixture implements DependentFixtureInterface
{
    public const OPPORTUNITY_ID_PREFIX = 'opportunity';
    public const OPPORTUNITY_ID_1 = 1;

    private SerializerInterface $serializer;

    public const OPPORTUNITY = [
        'id' => self::OPPORTUNITY_ID_1,
        'name' => 'Concurso Teste',
        'shortDescription' => 'Desperte sua criatividade e mostre seu talento no Concurso de Artes! Inscreva suas obras originais e concorra a prêmios incríveis.',
        'registrationFrom' => [
            'date' => '2024-05-01 00:00:00.000000',
        ],
        'registrationTo' => [
            'date' => '2024-06-05 11:13:00.000000',
        ],
        'publishedRegistrations' => false,
        'area' => ['Arquitetura-Urbanismo'],
        'twitter' => 'admin',
        'instagram' => 'admin',
        'tag' => [
            'Concurso',
            'Artes',
            'Teste',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        global $opportunity;
        $manager = $this->referenceRepository->getManager();

        $user = $manager->getRepository(Opportunity::class)->find(1);

        foreach (self::OPPORTUNITY as $opportunityData) {
            $event = $this->serializer->denormalize($opportunityData, Opportunity::class);
            $manager->persist($opportunity);
            $this->setProperty($opportunity, 'owner', $user);
            $event->setTerms($opportunityData['terms']);
            $this->setReference(sprintf('%s-%s', self::OPPORTUNITY_ID_PREFIX, $opportunityData['id']), $opportunity);
            $opportunity->saveMetadata();
            $opportunity->saveTerms();
            $manager->persist($opportunity);
        }

        $manager->flush();
    }
}
