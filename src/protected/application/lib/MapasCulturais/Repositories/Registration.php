<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\App;
use MapasCulturais\Traits;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Registration extends \MapasCulturais\Repository {
    use Traits\RepositoryKeyword;
    /**
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @param \MapasCulturais\Entities\User $user
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByOpportunityAndUser(\MapasCulturais\Entities\Opportunity $opportunity, $user){
        if($user->is('guest') || !$opportunity->id){
            return [];
        }

        $dql = "
            SELECT
                r
            FROM
                MapasCulturais\Entities\Registration r
                LEFT JOIN  MapasCulturais\Entities\RegistrationAgentRelation rar WITH rar.owner = r
            WHERE
                r.opportunity = :opportunity AND
                (
                    r.owner IN (:agents) OR
                    rar.agent IN (:agents)
                )";

        $q = $this->_em->createQuery($dql);

        $q->setParameters([
            'opportunity' => $opportunity,
            'agents' => $user->agents ? $user->agents->toArray() : [-1]
        ]);

        return $q->getResult();
    }

    /**
     *
     * @param \MapasCulturais\Entities\User $user
     * @param mixed $status = all all|sent|Entities\Registration::STATUS_*|[Entities\Registration::STATUS_*, Entities\Registration::STATUS_*]
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByUser($user, $status = 'all', int $limit = null){
        if($user->is('guest')){
            return [];
        }

        $status_where = "";
        if($status === 'all'){
            $status = false;
        }else if($status === 'sent'){
            $status = false;
            $status_where = "r.status > 0 AND";
        }else if(is_int($status)){
            $status_where = "r.status = :status AND";
        }else if(is_array($status)){
            $status_where = "r.status IN (:status) AND";
        }

        $dql = "
            SELECT
                r
            FROM
                MapasCulturais\Entities\Registration r
                LEFT JOIN  MapasCulturais\Entities\RegistrationAgentRelation rar WITH rar.owner = r
            WHERE
                $status_where
                (
                    r.owner IN (:agents) OR
                    rar.agent IN (:agents)
                )";

        $q = $this->_em->createQuery($dql);
        $q->setParameter('agents', $user->agents ? $user->agents->toArray() : [-1]);
        $q->setMaxResults($limit);
        if( $status !== false ){
            $q->setParameter('status', $status);
        }

        return $q->getResult();
    }

    function countByOpportunity(\MapasCulturais\Entities\Opportunity $opportunity, $include_draft = false, $status = 0){
        if(!$opportunity->id){
            return 0;
        }

        $dql_status = '';

        if(!$include_draft){
            $dql_status = "AND r.status > $status";
        }

        $dql = "SELECT COUNT(r.id) FROM {$this->getClassName()} r WHERE r.opportunity = :oppor $dql_status";

        $q = $this->_em->createQuery($dql);

        $q->setParameter('oppor', $opportunity);

        $num = $q->getSingleScalarResult();

        return $num;
    }

    function countByOpportunityAndOwner(\MapasCulturais\Entities\Opportunity $opportunity, \MapasCulturais\Entities\Agent $owner){
        if(!$opportunity->id || !$owner->id){
            return 0;
        }

        $dql = "SELECT COUNT(r.id) FROM {$this->getClassName()} r WHERE r.opportunity = :oppor AND r.owner = :owner";

        $q = $this->_em->createQuery($dql);

        $q->setParameter('oppor', $opportunity);
        $q->setParameter('owner', $owner);

        $num = $q->getSingleScalarResult();

        return $num;
    }

    /**
     * Returns the **FROM** part of DQL used to find the entities by keyword
     * 
     * @param string $keyword
     * @return string
     * 
     * @hook **repo({ENTITY}).getIdsByKeywordDQL.join**
     */
    protected function _getKeywordDQLFrom($keyword){
        $class = $this->getClassName();

        $join = '';

        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.join', [&$join, $keyword]);

        return "$class e JOIN e.owner o $join";
    }


    /**
     * Returns the **WHERE** part of DQL used to find the entities by keyword
     * 
     * @param string $keyword
     * @return string
     * 
     * @hook **repo({ENTITY}).getIdsByKeywordDQL.where**
     */
    protected function _getKeywordDQLWhere($keyword){
        $class = $this->getClassName();

        $where = '';

        App::i()->applyHookBoundTo($this, 'repo(' . $class::getHookClassPath() . ').getIdsByKeywordDQL.where', [&$where, $keyword]);

        return "
            (
                unaccent(lower(e.number)) LIKE unaccent(lower(:keyword)) OR 
                unaccent(lower(e.category)) LIKE unaccent(lower(:keyword)) OR 
                unaccent(lower(o.name)) LIKE unaccent(lower(:keyword))
            )
            $where";
    }
}