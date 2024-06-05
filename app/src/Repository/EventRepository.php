<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\EntityStatusEnum;
use Doctrine\Persistence\ObjectRepository;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\EventOccurrence;

class EventRepository extends AbstractRepository
{
    private ObjectRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = $this->mapaCulturalEntityManager->getRepository(Event::class);
    }

    public function findAll(): array
    {
        return $this->repository
            ->createQueryBuilder('events')
            ->getQuery()
            ->getArrayResult();
    }

    public function find(int $id): ?Event
    {
        return $this->repository->find($id);
    }

    public function findEventsBySpaceId(int $spaceId): array
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select([
                'e.id',
                'e.name',
                'e.shortDescription',
                'eo.startsOn',
                'eo.endsOn',
                'eo.startsAt',
                'eo.endsAt',
                'eo.price',
                'eo.priceInfo',
                'eo.frequency',
            ])
            ->from(EventOccurrence::class, 'eo')
            ->join(Event::class, 'e', 'WITH', 'eo.event = e.id')
            ->where('eo.space = :spaceId')
            ->setParameter(':spaceId', $spaceId);

        return $queryBuilder->getQuery()->getResult();
    }

    public function save($event): void
    {
        $event->save();
    }

    public function softDelete(Event $event): void
    {
        $event->setStatus(EntityStatusEnum::TRASH->getValue());
        $this->save($event);
    }

    public function update(Event $event): void
    {
        $this->mapaCulturalEntityManager->persist($event);
        $this->mapaCulturalEntityManager->flush();
    }
}
