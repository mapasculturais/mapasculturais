<?php
namespace MapasCulturais\Traits;

use \MapasCulturais\App;

/**
 * Implements methods to find entities by a keyword.
 *
 * Use this trait only in subclasses of **\MapasCulturais\Repository**.
 *
 * @hook repo({ENTITY}).getIdsByKeywordDQL.join
 * @hook repo({ENTITY}).getIdsByKeywordDQL.where
 */
trait RepositoryAgentRelation{

    /**
     * This repository uses AgentRelation
     * @return true
     */
    public static function usesAgentRelation(){
        return true;
    }

    /**
     * Returns the found entities
     *
     * @param int $offset
     * @return \MapasCulturais\Entity[]
     */
    function findByAgentRelationUser($user, $has_control = false, $agent_relation_status = 1, $status = 1) {
        $entityClass = $this->getClassName();

        if ($entityClass::usesTaxonomies()) {
            $dql = "
                SELECT
                    e, m, tr
                FROM
                    {$entityClass} e
                    JOIN e.__agentRelations er
                    JOIN er.agent a
                    LEFT JOIN e.__metadata m
                    LEFT JOIN e.__termRelations tr
                WHERE
                    a.user = :user AND
                    er.status = :ars AND
                    er.hasControl = :hc AND
                    e.status = :s";
        } else {
            $dql = "
                SELECT
                    e
                FROM
                    {$entityClass} e
                    JOIN e.__agentRelations er
                    JOIN er.agent a
                WHERE
                    a.user = :user AND
                    er.status = :ars AND
                    er.hasControl = :hc AND
                    e.status = :s";
        }

		$query = App::i()->em->createQuery($dql);
        // if ($app->config['app.usePermissionsCache']) {
        //     $query->useResultCache (true, $app->config['app.permissionsCache.lifetime']);
        // }
        $query->setParameter('user', $user);
        $query->setParameter('hc', $has_control);
        $query->setParameter('ars', $agent_relation_status);
        $query->setParameter('s', $status);

        $entityList = $query->getResult();
        return $entityList;
    }

    /**
     * Returns the found agents
     *
     * @param int $offset
     * @return \MapasCulturais\Agents[]
     */
    function findByAgentWithEntityControl($agent_relation_status = 1, $status = 1) {
        $entityClass = $this->getClassName();
        $app = App::i();

            $dql = "
                    SELECT
                        a, er
                    FROM
                        {$entityClass}AgentRelation er
                        JOIN er.agent a
                    WHERE
                        er.objectId = :id AND
                        er.status = :ars AND
                        er.hasControl = true ";
        $query = $app->em->createQuery($dql);
        // if ($app->config['app.usePermissionsCache']) {
        //     $query->useResultCache (true, $app->config['app.permissionsCache.lifetime']);
        // }
        $query->setParameter('id', $app->view->controller->requestedEntity->id);
        $query->setParameter('ars', $agent_relation_status);

        $entityList = $query->getResult();
        return $entityList;
    }
}
