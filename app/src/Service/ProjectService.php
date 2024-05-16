<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProjectRepository;
use MapasCulturais\Entities\Project;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectService
{
    protected ProjectRepository $projectRepository;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function create($data): Project
    {
        $project = new Project();
        $project->setName($data->name);
        $project->setShortDescription($data->shortDescription);
        $project->setType($data->type);

        $this->projectRepository->save($project);

        return $project;
    }

    public function update(int $id, object $data): Project
    {
        $projectFromDB = $this->projectRepository->find($id);

        if (null === $projectFromDB || -10 === $projectFromDB->status) {
            throw new ResourceNotFoundException('Project not found or already deleted.');
        }

        $projectUpdated = $this->serializer->denormalize(
            data: $data,
            type: Project::class,
            context: ['object_to_populate' => $projectFromDB]
        );
        $projectUpdated->saveTerms();
        $projectUpdated->saveMetadata();

        $this->projectRepository->save($projectUpdated);

        return $projectUpdated;
    }
}
