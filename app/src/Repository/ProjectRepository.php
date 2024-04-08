<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Project;

class ProjectRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->getEntityManager()->getRepository(Project::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('project')
            ->getQuery()
            ->getArrayResult();
    }
}
