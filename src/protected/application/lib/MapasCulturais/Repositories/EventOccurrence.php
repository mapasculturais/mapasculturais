<?php

namespace MapasCulturais\Repositories;

use MapasCulturais\App;

class EventOccurrence extends \MapasCulturais\Repository {

    function findByEventsAndSpaces(array $events, array $spaces, $date_from = null, $date_to = null, $limit = null, $offset = null) {
        $map_function = function($e) {
            return $e->id;
        };
        $event_ids = array_map($map_function, $events);
        $space_ids = array_map($map_function, $spaces);

        $app = App::i();

        if (is_null($date_from))
            $date_from = date('Y-m-d');
        else if ($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if (is_null($date_to))
            $date_to = $date_from;
        else if ($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\EventOccurrence', 'eo');


        $rsm->addFieldResult('eo', 'id', 'id');
        $rsm->addFieldResult('eo', 'starts_on', '_startsOn');
        $rsm->addFieldResult('eo', 'until', '_until');
        $rsm->addFieldResult('eo', 'starts_at', '_startsAt');
        $rsm->addFieldResult('eo', 'ends_at', '_endsAt');
        $rsm->addFieldResult('eo', 'rule', 'rule');

        $rsm->addFieldResult('eo', 'space_id', 'spaceId');
        $rsm->addFieldResult('eo', 'event_id', 'eventId');
        
        $dql_limit = $dql_offset = '';

        if ($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if ($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                eo.*
            FROM
                event_occurrence eo
                
            WHERE eo.id IN (
                    SELECT DISTINCT id FROM recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
                    WHERE space_id IN(:spaces)
                    AND   event_id IN(:events)
                ) 

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

        if ($app->config['app.useEventsCache'])
            $query->useResultCache(true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'spaces' => $space_ids,
            'events' => $event_ids
        ));

        $result = $query->getResult();

        return $result ? $result : array();
    }

}
