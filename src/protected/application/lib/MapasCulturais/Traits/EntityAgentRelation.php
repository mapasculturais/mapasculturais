<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App,
    MapasCulturais\Entities\Agent;


/**
 * Defines that this entity has agents related to it.
 *
 * @property-read \MapasCulturais\Entities\AgentRelation[] $relatedAgents The agents related to this entity
 *
 */
trait EntityAgentRelation {

    function usesAgentRelation(){
        return true;
    }

    function getAgentRelationEntityClassName(){
        return preg_replace('#Entities\\\([^\\\]+)$#', 'Entities\\\AgentRelations\\\$1', $this->getClassName());
    }

    function getAgentRelations(){
        if(!$this->id)
            return array();

        $app = App::i();

        $cache_id = "{$this->className}:{$this->id}:RELATED_AGENTS";

        if($app->objectCacheEnabled() && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $relation_class = $this->getAgentRelationEntityClassName();
        if(!class_exists($relation_class))
            return array();

        $dql = "
            SELECT
                ar,
                a,
                u
            FROM
                $relation_class ar
                JOIN ar.agent a
                JOIN a.user u
            WHERE
                ar.owner = :owner AND
                ar.status > 0 AND
                a.status IN (:in)
            ORDER BY a.name";

        $query = App::i()->em->createQuery($dql);

        $query->setParameters(array(
            'owner' => $this,
            'in' => array(Agent::STATUS_ENABLED, Agent::STATUS_INVITED, Agent::STATUS_RELATED)
        ));

        $result = $query->getResult();

        if($app->objectCacheEnabled())
            $app->cache->save($cache_id, $result, $app->objectCacheTimeout());


        return $result;
    }

    function clearAgentRelationCache(){
        $cache_id = "{$this->className}:{$this->id}:RELATED_AGENTS";
        App::i()->cache->delete($cache_id);

    }

    /**
     * Returns the agents related to this entity.
     *
     * If the group name is given returns all agents related to this entity with the given group, otherwise
     * returns all related agents grouped by the group name.
     *
     * @todo Terminar esta função.
     *
     * @return \MapasCulturais\Entities\Agent[] The Agents related to this entity.
     */
    function getRelatedAgents($group = null, $return_relations = false){
        if(!$this->id)
            return array();

        $relation_class = $this->getAgentRelationEntityClassName();
        if(!class_exists($relation_class))
            return array();

        $result = array();

        foreach ($this->getAgentRelations() as $agentRelation)
            $result[$agentRelation->group][] = $return_relations ? $agentRelation : $agentRelation->agent;

        ksort($result);

        if(is_null($group))
            return $result;
        elseif(key_exists($group, $result))
            return $result[$group];
        else
            return array();

    }

    function getAgentRelationsGrouped($group = null){
        return $this->getRelatedAgents($group, true);

    }

    function getUsersWithControl(){
        $result = array($this->ownerUser);
        $relations = $this->getAgentRelations();
        foreach($relations as $relation){
            if($relation->hasControl && !in_array($relation->agent->user, $result)){
                $result[] = $relation->agent->user;
            }
        }

        return $result;
    }

    function userHasControl($user){
        foreach($this->getUsersWithControl() as $u)
            if($u->id == $user->id)
                return true;

        return false;
    }

    function createAgentRelation(\MapasCulturais\Entities\Agent $agent, $group, $has_control = false, $flush = true){
        $this->checkPermission('createAgentRelation');

        if($has_control)
            $this->checkPermission('createAgentRelationWithControl');

        $relation_class = $this->getAgentRelationEntityClassName();
        $relation = new $relation_class;
        $relation->agent = $agent;
        $relation->owner = $this;
        $relation->group = $group;
        $relation->save($flush);

        if($has_control)
            $this->addControlToRelatedAgent($agent);

        $this->clearAgentRelationCache();

        return $relation;
    }

    function removeAgentRelation(\MapasCulturais\Entities\Agent $agent, $group){
        $relation_class = $this->getAgentRelationEntityClassName();
        $repo = App::i()->repo($relation_class);
        $relation = $repo->findOneBy(array('group' => $group, 'agent' => $agent, 'owner' => $this));
        if($relation){
            $this->checkPermission('removeAgentRelation');
            if($relation->hasControl)
                $this->checkPermission('removeAgentRelationWithControl');

            $relation->delete(true);

            $this->clearAgentRelationCache();
        }
    }

    function setRelatedAgentControl($agent, $control){
        if($control)
            $this->checkPermission('createAgentRelationWithControl');
        else
            $this->checkPermission('removeAgentRelationWithControl');

        $relation_class = $this->getAgentRelationEntityClassName();

        $em = App::i()->em;

        $q = $em->createQuery("
            UPDATE
                $relation_class r
            SET
                r.hasControl = :control
            WHERE
                r.agent = :agent AND
                r.owner = :owner");

        $q->setParameters(array('agent' => $agent, 'owner' => $this, 'control' => $control ? 'true' : 'false'));

        $q->execute();

        $em->flush();

        $this->clearAgentRelationCache();
    }

    protected function canUserCreateAgentRelation($user){
        $result = $user->is('admin') || $this->userHasControl($user);
        return $result;
    }

    protected function canUserCreateAgentRelationWithControl($user){
        $result = $user->is('admin') || $user->id === $this->ownerUser->id;
        return $result;
    }

    function canUserRemoveAgentRelation($user){
        $result = $user->is('admin') || $this->userHasControl($user);
        return $result;
    }

    function canUserRemoveAgentRelationWithControl($user){
        $result = $user->is('admin') || $user->id === $this->ownerUser->id;
        return $result;
    }
}