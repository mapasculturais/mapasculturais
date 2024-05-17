<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Seal;

class SealRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();

        $this->repository = $this->mapaCulturalEntityManager->getRepository(Seal::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('seal')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Seal
    {
        return $this->repository->find($id);
    }

    public function create(Seal $seal): void
    {
        $this->mapaCulturalEntityManager->persist($seal);
        $this->mapaCulturalEntityManager->flush();
    }

    public function softDelete(Seal $space): void
    {
        $space->setStatus(-10);
        $this->mapaCulturalEntityManager->persist($space);
        $this->mapaCulturalEntityManager->flush();
    }
}
