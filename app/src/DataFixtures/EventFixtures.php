<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Event;

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

    public function getDependencies(): array
    {
        return [
            TermFixtures::class,
            AgentFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->deleteAllDataFromTable(Event::class);

        $user = $this->getReference(AgentFixtures::AGENT_ID_PREFIX.'-'.AgentFixtures::AGENT_ID_1);

        foreach (self::EVENTS as $eventData) {
            $event = $this->getSerializer()->denormalize($eventData, Event::class);
            $event->setTerms($eventData['terms']);
            $this->setProperty($event, 'owner', $user);

            $this->setReference(sprintf('%s-%s', self::EVENT_ID_PREFIX, $eventData['id']), $event);
            $manager->persist($event);
        }

        $manager->flush();
    }
}
