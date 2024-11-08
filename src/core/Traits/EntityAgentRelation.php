<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App,
    MapasCulturais\Entities\Agent;
use MapasCulturais\Exceptions\PermissionDenied;

/**
 * Defines that this entity has agents related to it.
 *
 * @property-read \MapasCulturais\Entities\Agent[] $relatedAgents The agents related to this entity
 * @property-read \MapasCulturais\Entities\AgentRelation[] $agentRelations 
 * @property-read \MapasCulturais\Entities\AgentRelation[] $agentRelationsGrouped
 * @property-read string $agentRelationEntityClassName
 * @property-read int[] $idsOfUsersWithControl
 * @property-read \MapasCulturais\Entities\User[] $usersWithControl
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
        if(!$this->id){
            return [];
        }

        $relation_class = $this->getAgentRelationEntityClassName();
        
        if(!class_exists($relation_class)){
            return [];
        }
        
        $agent_statuses = [Agent::STATUS_ENABLED, Agent::STATUS_INVITED, Agent::STATUS_RELATED];
        $relations = [];
        
        $__relations = $this->__agentRelations;
        
        if(is_null($__relations)){
            $__relations = App::i()->repo($this->getAgentRelationEntityClassName())->findBy(['owner' => $this]);
        }
        
        foreach($__relations as $ar){
            if($include_pending_relations){
                $arstatus_ok = $ar->status > 0 || $ar->status === $relation_class::STATUS_PENDING;
            } else {
                $arstatus_ok = $ar->status > 0;
            }
            if($arstatus_ok && (is_null($has_control) || $ar->hasControl === $has_control) && in_array($ar->agent->status, $agent_statuses)){
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

    function getGroupRelationsAgent(){
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('object_type', 'object_type');

        $strNativeQuery = "SELECT object_type FROM public.agent_relation GROUP BY object_type;";

        $query = App::i()->em->createNativeQuery($strNativeQuery, $rsm);
        $entitiesTypes = $query->getArrayResult();
        
        $groups = [];
        foreach ($entitiesTypes as $entitieType) {
            $group = App::i()->repo($entitieType['object_type'] . 'AgentRelation')->findBy(['agent' => $this]);
            if($group){
                foreach ($group as $groupEntitie) {
                    $entitie = App::i()->repo($entitieType['object_type'])->find($groupEntitie->objectId);
                    $groups[] = array(
                        'group'   => $groupEntitie->group,
                        'entitie' => $entitie->name,
                        'url'     => $entitie->singleUrl
                    );
                }
            }
        }    
        
        return $groups;
    }

    function getAgentRelationsGrouped($group = null, $include_pending_relations = false){
        return $this->getRelatedAgents($group, true, $include_pending_relations);
    }

    function getIdsOfUsersWithControl(){
        $app = \MapasCulturais\App::i();

        $users = $this->getUsersWithControl();
        $ids = array_map(function($u){
            return $u->id;

        }, $users);

        return $ids;
    
    }

    function getUsersWithControl(array &$object_stack = []){
        $app = \MapasCulturais\App::i();

        $result = [$this->getOwnerUser()];
        $ids = [$result[0]->id];

        if($this->getClassName() !== 'MapasCulturais\Entities\Agent'){
            if($_owner = $this->getOwner()){
                foreach($_owner->getUsersWithControl() as $u){
                    if(!in_array($u->id, $ids)){
                        $ids[] = $u->id;
                        $result[] = $u;
                    }
                }
            }
        }

        if($this->usesNested()) {
            $object_stack[] = $this->id;
            
            $parent = $this->getParent();
            if(is_object($parent) && !$parent->equals($this) && !in_array($parent->id, $object_stack)){
                foreach($parent->getUsersWithControl($object_stack) as $u){
                    if(!in_array($u->id, $ids)){
                        $ids[] = $u->id;
                        $result[] = $u;
                    }
                }
            }
        }

        $relations = $this->getAgentRelations(true);

        foreach($relations as $relation){
            $u = $relation->agent->user;

            // excui o usuário guest se por algum motivo ele estiver na lista
            if ($u->is('guest')) {
                continue;
            }

            if(!in_array($u->id, $ids)){
                $ids[] = $u->id;
                $result[] = $u;
            }
        }

        return $result;

    }

    function userHasControl($user){
        if ($user->is('guest')) {
            return true;
        }
        if($this->isUserAdmin($user))
            return true;

        if($this->usesNested() && $this->parent && $this->parent->canUser('@control')) {
            return true;
        }

        $ids = $this->getIdsOfUsersWithControl() ?: [];

        return in_array($user->id, $ids);
    }

    function createAgentRelation(\MapasCulturais\Entities\Agent $agent, $group, $has_control = false, $save = true, $flush = true){
        $relation_class = $this->getAgentRelationEntityClassName();
        $relation = new $relation_class;
        $relation->agent = $agent;
        $relation->owner = $this;
        $relation->group = $group;

        if($errors = $relation->getValidationErrors()){
            throw new \Exception(\MapasCulturais\i::__('Error to create agent relation'));
        }

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

    function renameAgentRelationGroup($old_name, $new_name) {
        $this->checkPermission('modify');

        $app = App::i();
        
        if($old_name === 'group-admin') {
            return false;
        }
        
        $relations = $this->getRelatedAgents($old_name, true, true) ?: []; 
        
        $app->applyHookBoundTo($this, "{$this->hookPrefix}.renameAgentRelationGroup:before", [$old_name, &$new_name, $relations]);

        foreach($relations as $relation) {
            $relation->group = $new_name;
            $errors = $relation->getValidationErrors();
            if (isset($errors['group'])) {
                throw new PermissionDenied($app->user, $this, 'renameAgentRelationGroup');
            }

            $relation->save(true);
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.renameAgentRelationGroup:after", [$old_name, $new_name, $relations]);

        return true;
    }

    function removeAgentRelationGroup(string $name) {
        $this->checkPermission('removeAgentRelation');

        $app = App::i();

        if($name === 'group-admin') {
            return false;
        }

        $relations = $this->getRelatedAgents($name, true, true) ?: []; 

        foreach($relations as $relation) {
            $relation->delete(true);
        }

        return true;
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
        
        $this->refresh();
        
        if($this->usesPermissionCache()){
            $this->enqueueToPCacheRecreation();
        }
    }

    protected function canUserCreateAgentRelation($user){
        $result = $this->isUserAdmin($user) || $this->userHasControl($user);
        return $result;
    }

    protected function canUserCreateAgentRelationWithControl($user){
        $result = $this->isUserAdmin($user) || $user->id === $this->ownerUser->id;
        return $result;
    }

    function canUserRemoveAgentRelation($user){
        $result = $this->isUserAdmin($user) || $this->userHasControl($user);
        return $result;
    }

    function canUserRemoveAgentRelationWithControl($user){
        $result = $this->isUserAdmin($user) || $user->id === $this->ownerUser->id;
        return $result;
    }
}
