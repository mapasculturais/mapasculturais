<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Term;

class TermRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();

        $this->repository = $this->mapaCulturalEntityManager->getRepository(Term::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('term')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Term
    {
        return $this->repository->find($id);
    }

    public function save(Term $term): void
    {
        $this->mapaCulturalEntityManager->persist($term);
        $this->mapaCulturalEntityManager->flush();
    }
}
