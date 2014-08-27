<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class Space extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryCache;
    
    public function findByEventsAndDateInterval($event_ids = array(), $date_from = null, $date_to = null, $limit = null, $offset = null){
        if(!$event_ids)
            return array();

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

        $dql_event_ids = $event_ids ? 'AND eo.event_id IN (' . implode(',', $event_ids) . ')' : '';

        $strNativeQuery = "
            SELECT
                e.*
            FROM
                space e
            JOIN
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                ON eo.space_id = e.id $dql_event_ids

            WHERE e.status > 0

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to
        ));

        return $query->getResult();
    }

}