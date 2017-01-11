<?php

namespace MapasCulturais\Traits;

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
    
    

    function createPermissionsCacheForUsers(array $users, $flush = true) {
        $app = \MapasCulturais\App::i();
        
        $permissions = $this->getPermissionsList();
        $this->__enabled = false;
        
//        $permission_class = $this->getClassName() . 'PermissionCache';
        
        $conn = $app->em->getConnection();
        
        foreach ($users as $u) {
            if($u->is('admin')){
                continue;
            }
            foreach ($permissions as $permission) {
                if($permission == 'view' && $this->status > 0) {
                    continue;
                }
                
                if($this->canUser($permission, $u)){
                    $app->log->debug("PCACHE User {$u->id} <-> {$this->entityType} {$this->id} :: {$permission} ");
                    
                    $conn->insert('pcache', [
                        'user_id' => $u->id,
                        'action' => $permission,
                        'object_type' => $this->getClassName(),
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

}
