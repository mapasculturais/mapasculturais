<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public const EVENT_ID_PREFIX = 'event';
    public const EVENT_ID_1 = 1;
    public const EVENT_ID_2 = 2;
    public const EVENT_ID_3 = 3;

    public const EVENTS = [
        [
            'id' => self::EVENT_ID_1,
            'name' => 'Evento de Cultura',
            'shortDescription' => 'Este é um evento incrível organizado por todos.',
            'longDescription' => 'Junte-se a nós para uma experiência única e emocionante! O evento de Cultura oferece uma variedade de atividades e entretenimento para todos os gostos.',
            'rules' => 'Por favor, siga todas as regras e regulamentos do local.',
            'subTitle' => 'subtitulo do evento',
            'registrationInfo' => 'informações do evento',
            'classificacaoEtaria' => '18 anos',
            'telefonePublico' => '(85) 98991-8135',
            'traducaoLibras' => 'Sim',
            'descricaoSonora' => 'Não',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'teste',
            'twitter' => 'cultura',
            'instagram' => 'teste',
            'linkedin' => 'teste',
            'vimeo' => 'teste',
            'spotify' => 'teste',
            'youtube' => 'cultura',
            'pinterest' => 'teste',
            'event_attendance' => 50,
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Dança'],
            ],
        ],
        [
            'id' => self::EVENT_ID_2,
            'name' => 'Evento de Cultura',
            'shortDescription' => 'Este é um evento incrível organizado por todos.',
            'longDescription' => 'Junte-se a nós para uma experiência única e emocionante! O evento de Cultura oferece uma variedade de atividades e entretenimento para todos os gostos.',
            'rules' => 'Por favor, siga todas as regras e regulamentos do local.',
            'subTitle' => 'subtitulo do evento',
            'registrationInfo' => 'informações do evento',
            'classificacaoEtaria' => '18 anos',
            'telefonePublico' => '(85) 98991-8135',
            'traducaoLibras' => 'Sim',
            'descricaoSonora' => 'Não',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'teste',
            'twitter' => 'cultura',
            'instagram' => 'teste',
            'linkedin' => 'teste',
            'vimeo' => 'teste',
            'spotify' => 'teste',
            'youtube' => 'cultura',
            'pinterest' => 'teste',
            'event_attendance' => 50,
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Dança'],
            ],
        ],
        [
            'id' => self::EVENT_ID_3,
            'name' => 'Evento de Cultura',
            'shortDescription' => 'Este é um evento incrível organizado por todos.',
            'longDescription' => 'Junte-se a nós para uma experiência única e emocionante! O evento de Cultura oferece uma variedade de atividades e entretenimento para todos os gostos.',
            'rules' => 'Por favor, siga todas as regras e regulamentos do local.',
            'subTitle' => 'subtitulo do evento',
            'registrationInfo' => 'informações do evento',
            'classificacaoEtaria' => '18 anos',
            'telefonePublico' => '(85) 98991-8135',
            'traducaoLibras' => 'Sim',
            'descricaoSonora' => 'Não',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'teste',
            'twitter' => 'cultura',
            'instagram' => 'teste',
            'linkedin' => 'teste',
            'vimeo' => 'teste',
            'spotify' => 'teste',
            'youtube' => 'cultura',
            'pinterest' => 'teste',
            'event_attendance' => 50,
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Dança'],
            ],
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
            TermFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $manager = $this->referenceRepository->getManager();

        $user = $manager->getRepository(Agent::class)->find(1);

        foreach (self::EVENTS as $eventData) {
            $event = $this->serializer->denormalize($eventData, Event::class);
            $manager->persist($event);
            $this->setProperty($event, 'owner', $user);
            $event->setTerms($eventData['terms']);
            $this->setReference(sprintf('%s-%s', self::EVENT_ID_PREFIX, $eventData['id']), $event);
            $event->saveMetadata();
            $event->saveTerms();
            $manager->persist($event);
        }

        $manager->flush();
    }
}
