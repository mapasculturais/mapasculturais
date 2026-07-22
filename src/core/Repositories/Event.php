<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Repositório para entidades de evento
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de entidades do tipo Event no sistema,
 * com foco em buscas por espaço, projeto, agente e intervalo de datas.
 * 
 * @package MapasCulturais\Repositories
 */
class Event extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    /**
     * Obtém os IDs dos espaços do subsite atual
     * 
     * @param bool $implode Se true, retorna string SQL; se false, retorna array
     * @return string|array IDs dos espaços
     * @access protected
     */
    protected function _getCurrentSubsiteSpaceIds($implode = true){
        $app = App::i();
        if($app->getCurrentSubsiteId()){
            $space_ids = $app->repo('Space')->getCurrentSubsiteSpaceIds($implode);
        } else {
            $space_ids = "SELECT id FROM space WHERE status > 0";
        }

        return $space_ids;
    }

    /**
     * Encontra eventos por espaço
     * 
     * @param mixed $space Espaço ou array de espaços/IDs
     * @param string|\DateTime|null $date_from Data de início (padrão: hoje)
     * @param string|\DateTime|null $date_to Data de fim (padrão: igual a date_from)
     * @param int|null $limit Limite de resultados
     * @param int|null $offset Offset dos resultados
     * @return array Eventos encontrados
     */
    public function findBySpace($space, $date_from = null, $date_to = null, $limit = null, $offset = null){

        $app = App::i();

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

        if(is_array($ids) && $app->getCurrentSubsiteId()){
            $space_ids = $this->_getCurrentSubsiteSpaceIds(false);
            $ids = array_intersect($ids, $space_ids);

        }

        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        if(is_array($ids)){
            $ids = implode(',', $ids);
        }
        
        $sql = "
            SELECT
                e.id
            FROM
                event e
            JOIN
                event_occurrence eo
                    ON eo.event_id = e.id
                        AND eo.space_id IN ($ids)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND
                e.id IN (
                    SELECT
                        event_id
                    FROM
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
                    WHERE
                        space_id IN ($ids)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $params = [
            'date_from' => $date_from,
            'date_to' => $date_to
        ];


        $result = $this->_getEventsBySQL($sql, $params);

        return $result;
    }

    /**
     * Encontra eventos por projeto
     * 
     * @param mixed $project Projeto ou array de projetos/IDs
     * @param string|\DateTime|null $date_from Data de início (padrão: hoje)
     * @param string|\DateTime|null $date_to Data de fim (padrão: igual a date_from)
     * @param int|null $limit Limite de resultados
     * @param int|null $offset Offset dos resultados
     * @return array Eventos encontrados
     */
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

        if(is_null($date_from)){
            $date_from = date('Y-m-d');
        }else if($date_from instanceof \DateTime){
            $date_from = $date_from->format('Y-m-d');
        }

        if(is_null($date_to)){
            $date_to = $date_from;
        }else if($date_to instanceof \DateTime){
            $date_to = $date_to->format('Y-m-d');
        }

        $dql_limit = $dql_offset = '';

        if($limit){
            $dql_limit = 'LIMIT ' . $limit;
        }

        if($offset){
            $dql_offset = 'OFFSET ' . $offset;
        }

        $space_ids = $this->_getCurrentSubsiteSpaceIds(true);
        
        if(is_array($ids)){
            $ids = implode(',', $ids);
        }

        $sql = "
            SELECT
                e.id
            FROM
                event e
            JOIN
                event_occurrence eo
                    ON eo.event_id = e.id
                        AND eo.space_id IN ($space_ids)
                        AND eo.status > 0
            WHERE
                e.status > 0 AND
                e.project_id IN ($ids) AND
                e.id IN (
                    SELECT
                        event_id
                    FROM
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
                    WHERE
                        space_id IN ($space_ids)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $params = [
            'date_from' => $date_from,
            'date_to' => $date_to
        ];

        $result = $this->_getEventsBySQL($sql, $params);
        
        return $result;
    }


    /**
     * Encontra eventos por agente
     * 
     * @param \MapasCulturais\Entities\Agent $agent Agente
     * @param string|\DateTime|null $date_from Data de início (padrão: hoje)
     * @param string|\DateTime|null $date_to Data de fim (padrão: igual a date_from)
     * @param int|null $limit Limite de resultados
     * @param int|null $offset Offset dos resultados
     * @return array Eventos encontrados
     */
    public function findByAgent(\MapasCulturais\Entities\Agent $agent, $date_from = null, $date_to = null, $limit = null, $offset = null){
        
        if(is_null($date_from)){
            $date_from = date('Y-m-d');
        }else if($date_from instanceof \DateTime){
            $date_from = $date_from->format('Y-m-d');
        }

        if(is_null($date_to)){
            $date_to = $date_from;
        }else if($date_to instanceof \DateTime){
            $date_to = $date_to->format('Y-m-d');
        }

        $dql_limit = $dql_offset = '';

        if($limit){
            $dql_limit = 'LIMIT ' . $limit;
        }

        if($offset){
            $dql_offset = 'OFFSET ' . $offset;
        }

        $space_ids = $this->_getCurrentSubsiteSpaceIds(true);

        $sql = "
            SELECT
                e.id
            FROM
                event e
            JOIN
                event_occurrence eo
                    ON eo.event_id = e.id
                        AND eo.space_id IN ($space_ids)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND (
                    e.id IN(
                        SELECT
                            object_id
                        FROM
                            agent_relation
                        WHERE
                            object_type = 'MapasCulturais\\Entities\\Event' AND
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
                        space_id IN ($space_ids)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $params = [
            'date_from' => $date_from,
            'date_to' => $date_to,
            'agent_id' => $agent->id
        ];

        $result = $this->_getEventsBySQL($sql, $params);

        return $result;
    }


    /**
     * Encontra eventos por intervalo de datas
     * 
     * @param string|\DateTime|null $date_from Data de início (padrão: hoje)
     * @param string|\DateTime|null $date_to Data de fim (padrão: igual a date_from)
     * @param int|null $limit Limite de resultados
     * @param int|null $offset Offset dos resultados
     * @param bool $only_ids Se true, retorna apenas IDs
     * @return array Eventos ou IDs encontrados
     */
    public function findByDateInterval($date_from = null, $date_to = null, $limit = null, $offset = null, $only_ids = false){
                
        if(is_null($date_from)){
            $date_from = date('Y-m-d');
        } else if($date_from instanceof \DateTime){
            $date_from = $date_from->format('Y-m-d');
        }

        if(is_null($date_to)){
            $date_to = $date_from;
        }else if($date_to instanceof \DateTime){
            $date_to = $date_to->format('Y-m-d');
        }
        $dql_limit = $dql_offset = '';

        if($limit){
            $dql_limit = 'LIMIT ' . $limit;
        }

        if($offset){
            $dql_offset = 'OFFSET ' . $offset;
        }

        $space_ids = $this->_getCurrentSubsiteSpaceIds(true);

        $sql = "
            SELECT
                e.id
            FROM
                event e
            JOIN
                event_occurrence eo
                    ON eo.event_id = e.id
                        AND eo.space_id IN ($space_ids)
                        AND eo.status > 0

            WHERE
                e.status > 0 AND
                e.id IN (
                    SELECT
                        event_id
                    FROM
                        recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
                    WHERE
                        space_id IN ($space_ids)
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $params = ['date_from' => $date_from, 'date_to' => $date_to];
        
        if($only_ids){
            $result = $this->_getIdsBySQL($sql, $params);
        }else{
            $result = $this->_getEventsBySQL($sql, $params);
        }

        return $result;
    }
    
    /**
     * Obtém eventos executando SQL e processando resultados
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros da consulta
     * @return array Eventos encontrados
     * @access protected
     */
    function _getEventsBySQL($sql, $params = []){
        $ids = $this->_getIdsBySQL($sql, $params);
        $events = $this->_getEventsByIds($ids);
        
        return $events;
    }
    
    /**
     * Obtém IDs de eventos executando SQL
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parâmetros da consulta
     * @return array IDs dos eventos
     * @access protected
     */
    function _getIdsBySQL($sql, $params = []){
        $conn = $this->_em->getConnection();
    
        if($params){
            $rs = $conn->fetchAllAssociative($sql, $params);
        } else {
            $rs = $conn->fetchAllAssociative($sql);
        }

        $rs = $rs ?: [];
        
        $ids = array_map(function($e){ return $e['id']; }, $rs);
        
        return $ids;
    }

    /**
     * Obtém eventos por seus IDs
     * 
     * @param array $ids IDs dos eventos
     * @return array Eventos encontrados
     * @access protected
     */
    function _getEventsByIds($ids){
        if(!$ids){
            return [];
        }
        
        $class = $this->getClassName();
        $q = $this->_em->createQuery("SELECT e FROM $class e WHERE e.id IN(:ids)");
        $q->setParameter('ids', $ids);
        
        $result = $q->getResult();
        
        return $result;
    }
}
