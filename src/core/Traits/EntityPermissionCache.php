<?php
namespace MapasCulturais\Traits;

use Doctrine\ORM\UnitOfWork;
use MapasCulturais\App;
use MapasCulturais\Entity;

/**
 * @property-read string $permissionCacheClassName
 * @property-read string[] $permissionsList
 */
trait EntityPermissionCache {
    public $__skipQueuingPCacheRecreation = false;

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
    
    function getCurrentUserPermissions() {
        $permissions_list = $this->getPermissionsList();
        $permissions = [];
        foreach($permissions_list as $action) {
            $permissions[$action] = $this->canUser($action);
        }

        return $permissions;
    }

    function getPCacheObjectType(){
        $class_name = $this->getClassName();
        $metadata = App::i()->em->getClassMetadata($class_name);
        if($root_class = $metadata->rootEntityName){
            $class_name = $root_class;
        }
        
        return $class_name;
    }

    static protected array $createdPermissionCache = [];

    function createPermissionsCacheForUsers(array $users = null, $flush = false, $delete_old = true) {
        /** @var \MapasCulturais\Entity $this */
        
        if(self::$createdPermissionCache["$this"] ?? false) {
            return;
        } else {
            self::$createdPermissionCache["$this"] = true;
        }

        $app = App::i();
        $entity_state = $this->getEntityState();
        if(!in_array($entity_state, [UnitOfWork::STATE_MANAGED, UnitOfWork::STATE_NEW])){
            $this->refresh();
        }
        
        if(!$this->id){
            return;
        }

        if(is_null($users)){
            if($delete_old) {
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

            if($roles = $app->repo("Role")->findAll()){
                foreach($roles as $role){
                    $users[] = $role->user;
                }
            }
            
            $app->applyHookBoundTo($this, "{$this->hookPrefix}.permissionCacheUsers", [&$users]);
        } else if($delete_old) {
            $this->deletePermissionsCache($users);
        }

        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        $permissions = $this->getPCachePermissionsList();
        $this->__enabled = false;
        $isPrivateEntity = $class_name::isPrivateEntity();
        $hasCanUserViewMethod = method_exists($this, 'canUserView');
        $isStatusNotDraft = ($this->status > Entity::STATUS_DRAFT);

        $already_created_users = [];
        $users = array_unique($users);

        foreach($users as $i => $u) {
            if(is_numeric($u)) {
                $users[$i] = $app->repo('User')->find($u);
            }
        }

        foreach ($users as $user) {
            if (is_null($user)) {
                continue;
            }

            if($user->is('guest')){
                continue;
            }

            if(isset($already_created_users["$user"])){
                continue;
            }

            $already_created_users["$user"] = true;

            if($user->is('admin', $this->_subsiteId)){
                continue;
            }

            $allowed_permissions = [];

            foreach ($permissions as $permission) {
                if($permission === '_control' || $permission === 'view' && $isStatusNotDraft && !$isPrivateEntity && !$hasCanUserViewMethod) {
                    continue;
                }

                if ($this->canUser($permission, $user)) {
                    $allowed_permissions[] = $permission;
                    $conn->insert('pcache', [
                        'user_id' => $user->id,
                        'action' => $permission,
                        'object_type' => $class_name,
                        'object_id' => $this->id,
                        'create_timestamp' => 'now()'
                    ]);
                }
            }

            if($app->config['app.log.pcache.users'] && $allowed_permissions){
                $allowed_permissions = implode(',', $allowed_permissions);
                $app->log->debug(' PCACHE >> ' . str_replace('MapasCulturais\\Entities\\', '', "{$this}:{$user}($allowed_permissions)"));
            }
        }
        
        $this->__enabled = true;
    }
    
    function deletePermissionsCache($users = null){
        $app = App::i();
        $conn = $app->em->getConnection();
        $class_name = $this->getPCacheObjectType();
        if(!$this->id){
            return;
        }
        
        $complement = "";
        if($users){
            $users_ids = implode(',', array_map(function($user) { 
                if(is_numeric($user)) {
                    return $user;
                } else {
                    return $user->id; 
                }
            }, $users));
            $complement.="AND user_id IN ({$users_ids})";
        }

        $conn->executeQuery("DELETE FROM pcache WHERE object_type = '{$class_name}' AND object_id = {$this->id} {$complement}");
    }
       
    function enqueueToPCacheRecreation(array $users = []){
        $app = App::i();
        if($users) {
            foreach($users as $user) {
                if(is_numeric($user)) {
                    $user = $app->repo('User')->find($user);
                }

                if($app->isEntityEnqueuedToPCacheRecreation($this, $user) || $this->__skipQueuingPCacheRecreation){
                    return false;
                }
                
                $app->enqueueEntityToPCacheRecreation($this, $user);
            }
        } else {
            if($app->isEntityEnqueuedToPCacheRecreation($this) || $this->__skipQueuingPCacheRecreation){
                return false;
            }
            
            $app->enqueueEntityToPCacheRecreation($this);
        }

        return true;
    }


    function recreatePermissionCache($users = null, $path = ''){
        $app = App::i();
        if($users) {
            foreach($users as $i => $u) {
                if(is_numeric($u)) {
                    $users[$i] = $app->repo('User')->find($u);
                }
            }
        }

        $path .= str_replace('MapasCulturais\\Entities\\', '', "$this");

        if($app->config['app.log.pcache']){
            $app->log->debug($path);
        }

        if($app->isEntityPermissionCacheRecreated($this)){
            return false;
        }

        $hook_prefix = $this->hookPrefix;
        $app->applyHookBoundTo($this, "{$hook_prefix}.recreatePermissionCache:before", [&$users]);

        $self = $this;

        $app->setEntityPermissionCacheAsRecreated($self);

        $conn = $app->em->getConnection();
        $conn->beginTransaction();

        try {
            $self->createPermissionsCacheForUsers($users);
            $conn->commit();
        } catch (\Exception $e ){
            $conn->rollBack();
            throw $e;
        }

        if($self instanceof \MapasCulturais\Entities\User) {
            return true;
        }

        $class_relations = $app->em->getClassMetadata($self->getClassName())->getAssociationMappings();

        $enqueue_extra_entities = $app->config['pcache.enqueueExtraEntities'];

        if(method_exists($self, 'getExtraEntitiesToRecreatePermissionCache')){
            $entities = $self->getExtraEntitiesToRecreatePermissionCache();
            $total = count($entities);
            foreach($entities as $i => $entity){
                if($enqueue_extra_entities && $entity->className != $this->className) {
                    $entity->enqueueToPCacheRecreation($users ?: []);
                } else {
                    $i++;
                    $entity->recreatePermissionCache($users, "{$path}->extra({$i}/$total):");
                }
            }
        }

        foreach($class_relations as $prop => $def){
            $rel_class = $def['targetEntity'];
            if($def['type'] == 4 && !$def['isOwningSide'] && $rel_class::usesPermissionCache()){
                $total = count($self->$prop);
                foreach($self->$prop as $i => $entity){
                    if($enqueue_extra_entities && $entity->className != $this->className) {
                        $entity->enqueueToPCacheRecreation($users ?: []);
                    } else {
                        $entity->recreatePermissionCache($users, "{$path}->{$prop}({$i}/$total):");
                    }
                }
            }
            
        }

        $app->persistPCachePendingQueue();

        $app->applyHookBoundTo($this, "{$hook_prefix}.recreatePermissionCache:after", [&$users]);
    }
}
