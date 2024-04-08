<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Space;

class SpaceRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->getEntityManager()->getRepository(Space::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('space')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): Space
    {
        return $this->repository->find($id);
    }
}
