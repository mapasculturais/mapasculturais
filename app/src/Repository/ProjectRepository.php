<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\EntityStatusEnum;
use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Project;

class ProjectRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Project::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('project')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Project
    {
        return $this->repository->find($id);
    }

    public function save(Project $project): void
    {
        $this->mapaCulturalEntityManager->persist($project);
        $this->mapaCulturalEntityManager->flush();
    }

    public function softDelete(Project $project): void
    {
        $project->setStatus(EntityStatusEnum::TRASH->getValue());
        $this->mapaCulturalEntityManager->persist($project);
        $this->mapaCulturalEntityManager->flush();
    }
}
