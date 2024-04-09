<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Opportunity;

class OpportunityRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->getEntityManager()->getRepository(Opportunity::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('opportunity')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): Opportunity
    {
        return $this->repository->find($id);
    }
}
