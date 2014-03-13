<?php
namespace MapasCulturais\Entities\Repositories;

use Doctrine\ORM\EntityRepository;


class Event extends EntityRepository{

    public function findBySpace(\MapasCulturais\Entities\Space $space, $date_from = null, $date_to = null, $limit = null, $offset = null){
        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\Event','e');

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
                event e
            JOIN
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                ON eo.event_id = e.id AND eo.space_id = :space_id

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'space_id' => $space->id
        ));

        return $query->getResult();
    }

    public function findByAgent(\MapasCulturais\Entities\Agent $agent, $date_from = null, $date_to = null, $limit = null, $offset = null){
        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\Event','e');

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
                event e
            JOIN
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                ON eo.event_id = e.id
            WHERE e.id IN(SELECT object_id FROM agent_relation WHERE object_type = 'MapasCulturais\Entities\Event' AND agent_id = :agent_id)


            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'agent_id' => $agent->id
        ));

        return $query->getResult();
    }


    public function findByDateInterval($date_from = null, $date_to = null, $limit = null, $offset = null){
        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\Event','e');

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
                event e
            JOIN
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                ON eo.event_id = e.id

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to
        ));

        return $query->getResult();
    }

    }