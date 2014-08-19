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
        return $this->getClassName() . 'AgentRelation';
    }

    function getAgentRelations($has_control = null){
        if(!$this->id)
            return array();

        $relation_class = $this->getAgentRelationEntityClassName();
        if(!class_exists($relation_class))
            return array();
        
        $params = array(
            'owner' => $this,
            'in' => array(Agent::STATUS_ENABLED, Agent::STATUS_INVITED, Agent::STATUS_RELATED)
        );
        
        $dql_has_control = '';
        
        if(is_bool($has_control)){
            $params['has_control'] = $has_control;
            $dql_has_control = "ar.hasControl = :has_control AND";
        }
        
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
                $dql_has_control
                a.status IN (:in)
            ORDER BY a.name";

        $query = App::i()->em->createQuery($dql);

        $query->setParameters($params);

        $result = $query->getResult();
        return $result;
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
        $result = array($this->getOwnerUser());
        $relations = $this->getAgentRelations(true);
        
        foreach($relations as $relation){
            $result[] = $relation->agent->user;
        }
        return $result;
    }

    function userHasControl($user){
        foreach($this->getUsersWithControl() as $u)
            if($u->id == $user->id)
                return true;
            
        if($this->usesOwnerAgent() && $this->owner->userHasControl($user))
            return true;
            
        if($this->usesNested() && is_object($this->parent) && $this->parent->userHasControl($user))
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
        
        if($has_control)
            $relation->hasControl = true;
        
        $relation->save($flush);

        return $relation;
    }

    function removeAgentRelation(\MapasCulturais\Entities\Agent $agent, $group, $flush = true){
        $relation_class = $this->getAgentRelationEntityClassName();
        $repo = App::i()->repo($relation_class);
        $relation = $repo->findOneBy(array('group' => $group, 'agent' => $agent, 'owner' => $this));
        if($relation){
            $this->checkPermission('removeAgentRelation');
            if($relation->hasControl)
                $this->checkPermission('removeAgentRelationWithControl');

            $relation->delete($flush);

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