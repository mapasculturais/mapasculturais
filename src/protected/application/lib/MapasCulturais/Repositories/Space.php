<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class Space extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    public function findByEventsAndDateInterval($event_ids = [], $date_from = null, $date_to = null, $limit = null, $offset = null){
        if(!$event_ids)
            return [];

        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\Space','e');

        $metadata = $this->getClassMetadata();

        foreach($metadata->fieldMappings as $map)
            $rsm->addFieldResult('e', $map['columnName'], $map['fieldName']);

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                e.*
            FROM
                space e

            WHERE 
                e.status > 0 AND
                e.id IN (
                    SELECT 
                        space_id 
                    FROM 
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) 
                    WHERE 
                        event_id IN (:event_ids)
                )
            

            $dql_limit $dql_offset";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'event_ids' => $event_ids
        ]);

        return $query->getResult();
    }

}