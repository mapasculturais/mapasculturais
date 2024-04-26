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
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Space::class);
    }

    public function create(array $data): Space
    {
        $space = new Space();
        $space->setName($data['name']);
        $space->setLocation($data['location']);
        $space->setPublic($data['public']);
        $space->setShortDescription($data['shortDescription']);
        $space->setLongDescription($data['longDescription']);

        $this->mapaCulturalEntityManager->persist($space);
        $this->mapaCulturalEntityManager->flush();

        return $space;
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

    public function softDelete(Space $space): void
    {
        $space->setStatus(-10);
        $this->mapaCulturalEntityManager->persist($space);
        $this->mapaCulturalEntityManager->flush();
    }
}
