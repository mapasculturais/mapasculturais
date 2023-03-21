<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\App;

class Space extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    public function getCurrentSubsiteSpaceIds($implode = false){
        $app = App::i();
        if($subsite_id = $app->getCurrentSubsiteId()){
            $cache_id = 'SUBSITE::SPACE-IDS';

            if($app->config['app.useSubsiteIdsCache'] && $app->cache->contains($cache_id)){
                $space_ids = $app->cache->fetch($cache_id);
                if($implode && is_array($space_ids)){
                    $space_ids = implode(',', $space_ids);
                }
                return $space_ids;
            }
            $_api_result = $app->controller('space')->apiQuery(['@select' => 'id']);

            if($_api_result){
                $space_ids = array_map(function($e){
                    return $e['id'];
                }, $_api_result);

            }else{
                $space_ids = [0];
            }


            $app->cache->save($cache_id, $space_ids, $app->config['app.subsiteIdsCache.lifetime']);

        } else {
            $space_ids = null;
        }

        if($implode && is_array($space_ids)){
            $space_ids = implode(',', $space_ids);
        }
        return $space_ids;
    }

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
        $app = App::i();

        $space_ids = $this->getCurrentSubsiteSpaceIds(true);
        if(!is_null($space_ids)){
            $sql_space_ids = "AND e.id IN($space_ids)";
        } else {
            $sql_space_ids = "";
        }

        $strNativeQuery = "
            SELECT
                e.*
            FROM
                space e

            WHERE
                e.status > 0 $sql_space_ids AND
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
