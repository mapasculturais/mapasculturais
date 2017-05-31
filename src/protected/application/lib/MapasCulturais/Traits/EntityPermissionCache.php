<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait EntityPermissionCache {

    private static $__permissions = [];
    private $__enabled = true;

    public static function usesPermissionCache() {
        return true;
    }

    static function getPermissionCacheClassName(){
        return self::getClassName() . 'PermissionCache';
    }

    function permissionCacheExists() {
        return $this->__enabled && !is_null($this->__permissionsCache) && count($this->__permissionsCache) > 0;
    }

    function _cachedCanUser($action, \MapasCulturais\UserInterface $user) {
        foreach ($this->__permissionsCache as $cache) {
            if ($cache->action === $action && $cache->userId === $user->id) {
                return true;
            }
        }

        return false;
    }

    function getPermissionsList() {
        $class_name = $this->getClassName();
        if (!isset(self::$__permissions[$class_name])) {
            $permissions = ['@control'];
            foreach (get_class_methods($class_name) as $method) {
                if (strpos($method, 'canUser') === 0 && $method != 'canUser') {
                    $permissions[] = lcfirst(substr($method, 7));
                }
            }

            self::$__permissions[$class_name] = $permissions;
        }

        return self::$__permissions[$class_name];
    }
    
    function getPCacheObjectType(){
        $class_name = $this->getClassName();
        $metadata = App::i()->em->getClassMetadata($class_name);
        if($root_class = $metadata->rootEntityName){
            $class_name = $root_class;
        }
        
        return $class_name;
    }
    
    function createPermissionsCacheForUsers($users = null, $flush = true, $delete_old = true) {
        $this->refresh();
        
        if(!$this->id){
            return;
        }
        
        if($this->usesAgentRelation()){
            $this->deleteUsersWithControlCache();
        }
        
        $app = App::i();
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        $permissions = $this->getPermissionsList();
        
        $deleted = false;
        if(is_null($users)){
            if($delete_old){
                $deleted = true;
                $this->deletePermissionsCache();
            }
            
            if($this->usesAgentRelation()){
                $users = $this->getUsersWithControl();
            } else if($this->owner) {
                $users = $this->owner->getUsersWithControl();
            } else {
                $users = [$this->getOwnerUser()];
            }
            
            if(method_exists($this, 'getExtraPermissionCacheUsers')){
                $users = array_merge($users, $this->getExtraPermissionCacheUsers());
            }
        }
                
        $this->__enabled = false;
        
        $alredy_created_users = [];
        foreach ($users as $user) {
            if($delete_old && !$deleted){
                $this->deletePermissionsCache($user->id);
            }
            
            if($user->is('admin', $this->_subsiteId)){
                continue;
            }
            
            if(isset($alredy_created_users["$user"])){
                continue;
            } else {
                $alredy_created_users["$user"] = true;
            }
            
            foreach ($permissions as $permission) {
                if($permission === 'view' && $this->status > 0 && !$class_name::isPrivateEntity() && !method_exists($this, 'canUserView')) {
                    continue;
                }
                if($this->canUser($permission, $user)){
                    
                    $conn->insert('pcache', [
                        'user_id' => $user->id,
                        'action' => $permission,
                        'object_type' => $class_name,
                        'object_id' => $this->id,
                        'create_timestamp' => 'now()'
                    ]);
                }
            }
        }
        
        $this->__enabled = true;
        
        if($flush){
            $app->em->flush();
        }
        
    }
    
    function deletePermissionsCache($user_id = null){
        $app = App::i();
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        if(!$this->id){
            return;
        }
        if($user_id){
            $conn->executeQuery("DELETE FROM pcache WHERE object_type = '{$class_name}' AND object_id = {$this->id} AND user_id = {$user_id}");
        } else {
            $conn->executeQuery("DELETE FROM pcache WHERE object_type = '{$class_name}' AND object_id = {$this->id}");
        }
    }
    
    function addToRecreatePermissionsCacheList($skip_extra = false){
        $app = App::i();
        
        $app->addEntityToRecreatePermissionCacheList($this);
        
        $class_relations = $app->em->getClassMetadata($this->getClassName())->getAssociationMappings();
        
        foreach($class_relations as $prop => $def){
            $rel_class = $def['targetEntity'];
            if($def['type'] == 4 && !$def['isOwningSide'] && $rel_class::usesPermissionCache()){
                foreach($this->$prop as $entity){
                    if($entity instanceof \MapasCulturais\Entities\Project){
                    }
                    $entity->addToRecreatePermissionsCacheList(true);
                }
            }
            
        }
        
        if(!$skip_extra && method_exists($this, 'getExtraEntitiesToRecreatePermissionCache')){
            $entities = $this->getExtraEntitiesToRecreatePermissionCache();

            foreach($entities as $entity){
                $entity->addToRecreatePermissionsCacheList(true);
            }
        }
    }
}
