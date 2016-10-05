<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class Event extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    public function findBySpace($space, $date_from = null, $date_to = null, $limit = null, $offset = null){

        if($space instanceof \MapasCulturais\Entities\Space){
            $ids = $space->getChildrenIds();
            $ids[] = $space->id;

        }elseif($space && is_array($space) && is_object($space[0]) ){
            $ids = [-1];
            foreach($space as $s)
                if(is_object($s) && $s instanceof \MapasCulturais\Entities\Space && $s->status > 0)
                    $ids[] = $s->id;

        }elseif($space && is_array($space) && is_numeric ($space[0])){
            $ids = $space;

        }else{
            $ids = '0';
        }

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
                event_occurrence eo 
                    ON eo.event_id = e.id 
                        AND eo.space_id IN (:space_ids)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND
                e.id IN (
                    SELECT 
                        event_id 
                    FROM 
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) 
                    WHERE 
                        space_id IN (:space_ids)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'space_ids' => $ids
        ]);


        $result = $query->getResult();

        return $result;
    }

    public function findByProject($project, $date_from = null, $date_to = null, $limit = null, $offset = null){

        if($project instanceof \MapasCulturais\Entities\Project){
            $ids = $project->getChildrenIds();
            $ids[] = $project->id;

        }elseif($project && is_array($project) && is_object($project[0]) ){
            $ids = [-1];
            foreach($project as $s)
                if(is_object($s) && $s instanceof \MapasCulturais\Entities\Project && $s->status > 0)
                    $ids[] = $s->id;

        }elseif($project && is_array($project) && is_numeric ($project[0])){
            $ids = $project;

        }else{
            $ids = '0';
        }

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
                event_occurrence eo 
                    ON eo.event_id = e.id 
                        AND eo.space_id IN (SELECT id FROM space WHERE status > 0)
                        AND eo.status > 0
            WHERE
                e.status > 0 AND 
                e.project_id IN (:project_ids) AND
                e.id IN (
                    SELECT 
                        event_id 
                    FROM 
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) 
                    WHERE 
                        space_id IN (SELECT id FROM space WHERE status > 0)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'project_ids' => $ids
        ]);


        $result = $query->getResult();

        return $result;
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
                event_occurrence eo 
                    ON eo.event_id = e.id 
                        AND eo.space_id IN (SELECT id FROM space WHERE status > 0)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND (
                    e.id IN(
                        SELECT
                            object_id
                        FROM
                            agent_relation
                        WHERE
                            object_type = 'MapasCulturais\Entities\Event' AND
                            agent_id = :agent_id
                    ) OR

                    e.agent_id = :agent_id
                ) AND
                e.id IN (
                    SELECT 
                        event_id 
                    FROM 
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) 
                    WHERE 
                        space_id IN (SELECT id FROM space WHERE status > 0)
                )
                


            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'agent_id' => $agent->id
        ]);


        $result = $query->getResult();

        return $result;
    }


    public function findByDateInterval($date_from = null, $date_to = null, $limit = null, $offset = null, $only_ids = false){

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

        if($only_ids){
            $select = 'id';
            $rsm->addFieldResult('e', 'id', 'id');
        }else{
            $select = '*';
            $metadata = $this->getClassMetadata();

            foreach($metadata->fieldMappings as $map)
                $rsm->addFieldResult('e', $map['columnName'], $map['fieldName']);
        }
        
        
        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                e.{$select}
            FROM
                event e
            JOIN 
                event_occurrence eo 
                    ON eo.event_id = e.id 
                        AND eo.space_id IN (SELECT id FROM space WHERE status > 0)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND
                e.id IN (
                    SELECT 
                        event_id 
                    FROM 
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) 
                    WHERE 
                        space_id IN (SELECT id FROM space WHERE status > 0)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $this->_em->createNativeQuery($strNativeQuery, $rsm);

        $app = \MapasCulturais\App::i();
        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        if($only_ids){
            $result = array_map(function($e){ return $e['e_id']; }, $query->getScalarResult());
        }else{
            $result = $query->getResult();
        }

        $this->_em->clear();

        return $result;
    }

}