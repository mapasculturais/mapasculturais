<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Project;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROJECT_ID_PREFIX = 'project';
    public const PROJECT_ID_1 = 1;
    public const PROJECT_ID_2 = 2;
    public const PROJECT_ID_3 = 3;

    public const PROJECTS = [
        [
            'id' => self::PROJECT_ID_1,
            'name' => 'Projeto de Cultura',
            'shortDescription' => 'descrição curta',
            'longDescription' => 'Uma descrição mais detalhada sobre o projeto...',
            'startsOn' => '2024-05-01 00:00:00.000000',
            'endsOn' => '2024-05-31 15:39:00.000000',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'facebook',
            'twitter' => 'twitter',
            'instagram' => 'instagram',
            'linkedin' => 'linkedin',
            'vimeo' => 'vimeo',
            'spotify' => 'spotify',
            'youtube' => 'youtube',
            'pinterest' => 'pinterest',
            'emailPublico' => 'email.publico@email.com',
            'emailPrivado' => 'email.privado@email.com',
            'telefonePublico' => '(85) 99999-9999',
            'telefone1' => '(85) 99999-9999',
            'telefone2' => '(85) 99999-9999',
            'type' => 16,
            'terms' => [
                'tag' => ['teste'],
            ],
        ],
        [
            'id' => self::PROJECT_ID_2,
            'name' => 'Projeto de Cultura',
            'shortDescription' => 'descrição curta',
            'longDescription' => 'Uma descrição mais detalhada sobre o projeto...',
            'startsOn' => '2024-05-01 00:00:00.000000',
            'endsOn' => '2024-05-31 15:39:00.000000',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'Facebook',
            'twitter' => 'twitter',
            'instagram' => 'instagram',
            'linkedin' => 'linkindln',
            'vimeo' => 'vimeo',
            'spotify' => 'spotfy',
            'youtube' => 'youtube',
            'pinterest' => 'pinterest',
            'emailPublico' => 'email.publico@email.com',
            'emailPrivado' => 'email.privado@email.com',
            'telefonePublico' => '(85) 99999-9999',
            'telefone1' => '(85) 99999-9999',
            'telefone2' => '(85) 99999-9999',
            'type' => 16,
            'terms' => [
                'tag' => ['teste'],
            ],
        ],
        [
            'id' => self::PROJECT_ID_3,
            'name' => 'Projeto de Cultura',
            'shortDescription' => 'descrição curta',
            'longDescription' => 'Uma descrição mais detalhada sobre o projeto...',
            'startsOn' => '2024-05-01 00:00:00.000000',
            'endsOn' => '2024-05-31 15:39:00.000000',
            'site' => 'https://www.google.com.br/?hl=pt-BR',
            'facebook' => 'Facebook',
            'twitter' => 'twitter',
            'instagram' => 'instagram',
            'linkedin' => 'linkedin',
            'vimeo' => 'vimeo',
            'spotify' => 'spotify',
            'youtube' => 'youtube',
            'pinterest' => 'pinterest',
            'emailPublico' => 'email.publico@email.com',
            'emailPrivado' => 'email.privado@email.com',
            'telefonePublico' => '(85) 99999-9999',
            'telefone1' => '(85) 99999-9999',
            'telefone2' => '(85) 99999-9999',
            'type' => 16,
            'terms' => [
                'tag' => ['teste'],
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

        foreach (self::PROJECTS as $projectData) {
            $project = $this->serializer->denormalize($projectData, Project::class);
            $manager->persist($project);
            $this->setProperty($project, 'owner', $user);
            $project->setTerms($projectData['terms']);
            $this->setReference(sprintf('%s-%s', self::PROJECT_ID_PREFIX, $projectData['id']), $project);
            $project->saveMetadata();
            $project->saveTerms();
            $manager->persist($project);
        }

        $manager->flush();
    }
}
