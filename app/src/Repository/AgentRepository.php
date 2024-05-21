<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\EntityStatusEnum;
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

    public function save(Agent $agent): void
    {
        $this->mapaCulturalEntityManager->persist($agent);
        $this->mapaCulturalEntityManager->flush();
    }

    public function softDelete(Agent $agent): void
    {
        $agent->setStatus(EntityStatusEnum::TRASH->getValue());
        $this->mapaCulturalEntityManager->persist($agent);
        $this->mapaCulturalEntityManager->flush();
    }
}
