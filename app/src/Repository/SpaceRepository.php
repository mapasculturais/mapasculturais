<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\EntityStatusEnum;
use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Space;

class SpaceRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Space::class);
    }

    public function save(Space $space): void
    {
        $space->saveMetadata();
        $space->saveTerms();
        $this->mapaCulturalEntityManager->persist($space);
        $this->mapaCulturalEntityManager->flush();
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('space')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Space
    {
        return $this->repository->find($id);
    }

    public function softDelete(Space $space): void
    {
        $space->setStatus(EntityStatusEnum::TRASH->getValue());
        $this->mapaCulturalEntityManager->persist($space);
        $this->mapaCulturalEntityManager->flush();
    }
}
