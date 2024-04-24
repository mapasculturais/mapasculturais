<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Agent;

class AgentRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Agent::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('agent')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Agent
    {
        return $this->repository->find($id);
    }

    public function softDelete(Agent $agent): void
    {
        $agent->setStatus(-10);
        $this->mapaCulturalEntityManager->persist($agent);
        $this->mapaCulturalEntityManager->flush();
    }
}
