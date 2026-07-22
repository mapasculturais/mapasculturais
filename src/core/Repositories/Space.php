<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Repositório para entidades de espaço
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de entidades do tipo Space no sistema,
 * com foco em operações relacionadas a subsites e eventos.
 * 
 * @package MapasCulturais\Repositories
 */
class Space extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    /**
     * Obtém os IDs dos espaços do subsite atual
     * 
     * @param bool $implode Se true, retorna string SQL; se false, retorna array
     * @return string|array|null IDs dos espaços
     */
    public function getCurrentSubsiteSpaceIds($implode = false){
        $app = App::i();
        if($subsite_id = $app->getCurrentSubsiteId()){
            if($implode) {
                return "SELECT id FROM space WHERE subsite_id = $subsite_id AND status > 0";
            }
            
            $cache_id = 'SUBSITE::SPACE-IDS';

            if($app->config['app.useSubsiteIdsCache'] && $app->cache->contains($cache_id)){
                $space_ids = $app->cache->fetch($cache_id);
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

    /**
     * Encontra espaços por eventos e intervalo de datas
     * 
     * @param array $event_ids IDs dos eventos
     * @param string|\DateTime|null $date_from Data de início (padrão: hoje)
     * @param string|\DateTime|null $date_to Data de fim (padrão: igual a date_from)
     * @param int|null $limit Limite de resultados
     * @param int|null $offset Offset dos resultados
     * @return array Espaços encontrados
     */
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
