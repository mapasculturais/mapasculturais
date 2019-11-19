<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;

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
        $app = App::i();
        if($this->getEntityState() !== 2){
            $this->refresh();
        }
        
        if(!$this->id){
            return;
        }

        if(php_sapi_name()==="cli"){
            echo "\n\t - RECREATING PERMISSIONS CACHE FOR $this ";
        }
        
        if($this->usesAgentRelation()){
            $this->deleteUsersWithControlCache();
        }

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

        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        $permissions = $this->getPermissionsList();
        $this->__enabled = false;
        $isPrivateEntity = $class_name::isPrivateEntity();
        $hasCanUserViewMethod = method_exists($this, 'canUserView');
        $isStatusNotDraft = ($this->status > Entity::STATUS_DRAFT);

        $already_created_users = [];
        foreach ($users as $user) {
            if($user->is('guest')){
                continue;
            }
            if (is_null($user)) {
                continue;
            }

            if(isset($already_created_users["$user"])){
                continue;
            }

            $already_created_users["$user"] = true;

            if($user->is('admin', $this->_subsiteId)){
                continue;
            }

            if($delete_old && !$deleted){
                $this->deletePermissionsCache();
            }

            foreach ($permissions as $permission) {
                if($permission === 'view' && $isStatusNotDraft && !$isPrivateEntity && !$hasCanUserViewMethod) {
                    continue;
                }

                if ($this->canUser($permission, $user)) {
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
        if(php_sapi_name()==="cli"){
            echo "OK \n";
        }
        $this->__enabled = true;
    }
    
    function deletePermissionsCache(){
        $app = App::i();
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        if(!$this->id){
            return;
        }
        $conn->executeQuery("DELETE FROM pcache WHERE object_type = '{$class_name}' AND object_id = {$this->id}");
    }
       
    function enqueueToPCacheRecreation($skip_extra = false){
        $app = App::i();
        if($app->isEntityEnqueuedToPCacheRecreation($this)){
            return false;
        }
        
        $app->enqueueEntityToPCacheRecreation($this);

        return true;
    }


    function recreatePermissionCache(){
        $app = App::i();
        if($app->isEntityPermissionCacheRecreated($this)){
            return false;
        }

        $app->setEntityPermissionCacheAsRecreated($this);
        
        $this->createPermissionsCacheForUsers();

        $class_relations = $app->em->getClassMetadata($this->getClassName())->getAssociationMappings();
        
        
        foreach($class_relations as $prop => $def){
            $rel_class = $def['targetEntity'];
            if($def['type'] == 4 && !$def['isOwningSide'] && $rel_class::usesPermissionCache()){
                foreach($this->$prop as $entity){
                    $entity->recreatePermissionCache();
                }
            }
            
        }

        
        if(method_exists($this, 'getExtraEntitiesToRecreatePermissionCache')){
            $entities = $this->getExtraEntitiesToRecreatePermissionCache();

            foreach($entities as $entity){
                $entity->recreatePermissionCache();
            }
        }
        

    }
}
