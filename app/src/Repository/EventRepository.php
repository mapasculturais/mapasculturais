<?php

declare(strict_types=1);

namespace App\Repository;

use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\EventOccurrence;

class EventRepository extends AbstractRepository
{

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
}
