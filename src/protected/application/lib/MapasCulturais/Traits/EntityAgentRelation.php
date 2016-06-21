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

    public static function usesAgentRelation(){
        return true;
    }

    static function getAgentRelationEntityClassName(){
        return self::getClassName() . 'AgentRelation';
    }

    function getAgentRelations($has_control = null, $include_pending_relations = false){
        if(!$this->id)
            return [];

        $relation_class = $this->getAgentRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];
        
        $statuses = $include_pending_relations ? [$relation_class::STATUS_ENABLED, $relation_class::STATUS_PENDING] : [$relation_class::STATUS_ENABLED];
        $agent_statuses = [Agent::STATUS_ENABLED, Agent::STATUS_INVITED, Agent::STATUS_RELATED];
        $relations = [];
        
        $__relations = $this->__agentRelations;
        
        if(is_null($__relations)){
            $__relations = App::i()->repo($this->getAgentRelationEntityClassName())->findBy(['owner' => $this]);
        }
        
        foreach($__relations as $ar){
            if(in_array($ar->status, $statuses) && (is_null($has_control) || $ar->hasControl === $has_control) && in_array($ar->agent->status, $agent_statuses)){
                $relations[] = $ar;
            }
        }

        return $relations;
    }

    /**
     * Returns the agents related to this entity.
     *
     * If the group name is given returns all agents related to this entity with the given group, otherwise
     * returns all related agents grouped by the group name.
     *
     * @return \MapasCulturais\Entities\Agent[]|\MapasCulturais\Entities\AgentRelation[] The Agents related to this entity.
     */
    function getRelatedAgents($group = null, $return_relations = false, $include_pending_relations = false){
        if(!$this->id)
            return [];

        $relation_class = $this->getAgentRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $result = [];

        foreach ($this->getAgentRelations(null, $include_pending_relations) as $agentRelation)
            $result[$agentRelation->group][] = $return_relations ? $agentRelation : $agentRelation->agent;

        ksort($result);

        if(is_null($group))
            return $result;
        elseif(key_exists($group, $result))
            return $result[$group];
        else
            return [];

    }

    function getAgentRelationsGrouped($group = null, $include_pending_relations = false){
        return $this->getRelatedAgents($group, true, $include_pending_relations);

    }

    function getIdsOfUsersWithControl(){
        $app = \MapasCulturais\App::i();

        $cache_id = "$this::usersWithControl";

        if($app->config['app.usePermissionsCache'] && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }else{
            $users = $this->getUsersWithControl();
            $ids = array_map(function($u){
                return $u->id;

            }, $users);

            return $ids;
        }
    }

    function getUsersWithControl(){
        $app = \MapasCulturais\App::i();

        // cache ids
        $cache_id = "$this::usersWithControl";

        if($app->config['app.usePermissionsCache'] && $app->cache->contains($cache_id)){
            $ids = $app->cache->fetch($cache_id);
            $q = $app->em->createQuery("SELECT u FROM MapasCulturais\Entities\User u WHERE u.id IN (:ids)");
            $q->useQueryCache(true);
            $q->setQueryCacheLifetime($app->config['app.permissionsCache.lifetime']);
            $q->setParameter('ids', $ids);
            return $q->getResult();
        }

        $result = [$this->getOwnerUser()];
        $ids = [$result[0]->id];
        if(is_object($ids[count($ids) - 1])) die(var_dump($ids));

        if($this->getClassName() !== 'MapasCulturais\Entities\Agent'){
            foreach($this->getOwner()->getUsersWithControl() as $u){
                if(!in_array($u->id, $ids)){
                    $ids[] = $u->id;
                    if(is_object($ids[count($ids) - 1])) die(var_dump($ids));
                    $result[] = $u;
                }
            }
        }

        if($this->usesNested()) {
            $parent = $this->getParent();

            if(is_object($parent) && !$parent->equals($this)){
                foreach($parent->getUsersWithControl() as $u){
                    if(!in_array($u->id, $ids)){
                        $ids[] = $u->id;
                        if(is_object($ids[count($ids) - 1])) die(var_dump($ids));
                        $result[] = $u;
                    }
                }
            }
        }

        $relations = $this->getAgentRelations(true);

        foreach($relations as $relation){
            $u = $relation->agent->user;
            if(!in_array($u->id, $ids)){
                $ids[] = $u->id;
                if(is_object($ids[count($ids) - 1])) die(var_dump($ids));
                $result[] = $u;
            }
        }

        if($app->config['app.usePermissionsCache']){
            $app->cache->save($cache_id, $ids, $app->config['app.permissionsCache.lifetime']);
        }


        return $result;

    }

    function userHasControl($user){
        if($user->is('admin'))
            return true;

        $ids = $this->getIdsOfUsersWithControl();

        return in_array($user->id, $ids);
    }

    function createAgentRelation(\MapasCulturais\Entities\Agent $agent, $group, $has_control = false, $save = true, $flush = true){
        $relation_class = $this->getAgentRelationEntityClassName();
        $relation = new $relation_class;
        $relation->agent = $agent;
        $relation->owner = $this;
        $relation->group = $group;

        if($has_control)
            $relation->hasControl = true;

        if($save)
            $relation->save($flush);

        $this->refresh();
        return $relation;
    }

    function removeAgentRelation(\MapasCulturais\Entities\Agent $agent, $group, $flush = true){
        $relation_class = $this->getAgentRelationEntityClassName();
        $repo = App::i()->repo($relation_class);
        $relation = $repo->findOneBy(['group' => $group, 'agent' => $agent, 'owner' => $this]);
        if($relation){
            $relation->delete($flush);
        }
        
        $this->refresh();
    }

    function setRelatedAgentControl($agent, $control){
    	// canUserCreateAgentRelationWithControl
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

        $q->setParameters(['agent' => $agent, 'owner' => $this, 'control' => $control ? 'true' : 'false']);

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