<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\EntityStatusEnum;
use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\AgentOpportunity;
use MapasCulturais\Entities\Opportunity;

class OpportunityRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Opportunity::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('opportunity')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Opportunity
    {
        return $this->repository->find($id);
    }

    public function save(Opportunity $opportunity): void
    {
        $this->mapaCulturalEntityManager->persist($opportunity);
        $this->mapaCulturalEntityManager->flush();
    }

    public function findOpportunitiesByAgentId(int $agentId): array
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ao')
            ->from(AgentOpportunity::class, 'ao')
            ->where('ao.ownerEntity = :agentId')
            ->andWhere('ao.parent is null')
            ->setParameter('agentId', $agentId);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function softDelete(Opportunity $opportunity): void
    {
        $opportunity->setStatus(EntityStatusEnum::TRASH->getValue());
        $this->save($opportunity);
    }
}
