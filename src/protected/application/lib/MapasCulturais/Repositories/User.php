<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\Entities;
use MapasCulturais\App;

class User extends \MapasCulturais\Repository{

    /**
     * Retorna um usuário pelo auth_uid e auth_provider
     * @param string $auth_uid
     * @param int $auth_provider
     * @return \MapasCulturais\Entities\User|null
     */
    public function getByAuth($auth_provider, $auth_uid){
        $user_query = $this->_em->createQuery('SELECT u FROM MapasCulturais\Entities\User u WHERE u.authProvider=:auth_provider AND u.authUid = :auth_uid');

        $user_query->setParameter('auth_provider', $auth_provider);
        $user_query->setParameter('auth_uid', $auth_uid);
        $user = $user_query->getOneOrNullResult();
        return $user;
    }
    
    public function getByRole($role,$subsite_id = 0) {
        $join_subsite = "";
        if($subsite_id > 0) {
            $join_subsite .= " JOIN r.subsite s WITH s.id =:subsite_id " ; 
        }
        
        $user_query = $this->_em->createQuery('SELECT r,u,a FROM MapasCulturais\Entities\Role r 
                JOIN r.user u WITH r.name =:role' . $join_subsite . ' JOIN u.profile a ORDER BY a.name');

        $user_query->setParameter('role', $role);
        if($subsite_id > 0) {
            $user_query->setParameter('subsite_id', $subsite_id);
        }
        $users = $user_query->getResult();
        return $users;

    }

    public function getByProcurationToken($procuration_token){
        $procuration = App::i()->repo('Procuration')->find($procuration_token);
        if($procuration){
            $user = $procuration->user;
        } else {
            $user = null;
        }

        return $user;
    }


    public function getAdmins($subsite_id){
        $class = $this->getClassName();

        $q = $this->_em->createQuery();

        if(is_null($subsite_id)){
            $_dql = 'r.subsiteId IS NULL';
        } else {
            $_dql = 'r.subsiteId = :subsiteId';
            $q->setParameter('subsiteId', $subsite_id);
        }

        $dql = "
            SELECT
                e
            FROM
                {$class} e
                JOIN e.roles r
                    WITH r.name IN ('saasSuperAdmin', 'saasAdmin') OR
                         (r.name IN ('superAdmin', 'admin') AND {$_dql})";


        $q->setDQL($dql);

        $admins = $q->getResult();
        
        App::i()->applyHookBoundTo($this, "repo(User).getAdmins", [$subsite_id, &$admins]);

        return $admins;
    }

    public function getRoles($user_id) {
        $user_query = $this->_em->createQuery('SELECT r.id id, r.name role, s.id subsite_id, s.name subsite FROM MapasCulturais\Entities\Role r
                JOIN r.user u WITH u.id =:user_id LEFT JOIN r.subsite s');

        $user_query->setParameter('user_id', $user_id);
        $users = $user_query->getResult();
        return $users;
    }

    public function getSubsitesCanAddRoles($user_id) {
        $user = App::i()->user;
        $query = $this->_em->createQuery('SELECT s FROM MapasCulturais\Entities\Subsite s WHERE s.id IN (
                SELECT b.id FROM MapasCulturais\Entities\Role r JOIN r.subsite b JOIN r.user u WITH u.id =:user_id)');
        
        if($user->is("saasSuperAdmin")) { 
            $query = $this->_em->createQuery('SELECT s FROM MapasCulturais\Entities\Subsite s');
        } else {
            $query->setParameter('user_id', $user_id);
        }
        
        $subsitesAllowed = [];
        $subsites = $query->getResult();

        foreach ($subsites as $subsite) {
             if($user->is("superAdmin", $subsite->id)) {
                $subsitesAllowed[] = $subsite;
             }
        }
        return $subsitesAllowed;
    }

    public function getSubsitesAdminRoles($user_id) {
        $user = App::i()->user;
        $query = $this->_em->createQuery('SELECT s FROM MapasCulturais\Entities\Subsite s WHERE s.id IN (
                SELECT b.id FROM MapasCulturais\Entities\Role r JOIN r.subsite b JOIN r.user u WITH u.id =:user_id)');
        $subsitesAllowed = [];
        
        if($user->is("saasSuperAdmin")) { 
            $query = $this->_em->createQuery('SELECT s FROM MapasCulturais\Entities\Subsite s');
        } else {
            $query->setParameter('user_id', $user_id);
        }
        
        $subsites = $query->getResult();

        foreach ($subsites as $subsite) {
             if($user->is("admin", $subsite->id)) {
                $subsitesAllowed[] = $subsite;
             }
        }

        if($user->is('admin', null)) {
            $subsitesAllowed[] = (object) ['id' => null];
        }


        return $subsitesAllowed;
    }

    public function getHistory($user_id) {
        $query = $this->_em->createQuery(
                   "SELECT e.id, e.objectId, e.objectType, e.action, e.message, e.createTimestamp
                    FROM MapasCulturais\Entities\EntityRevision e
                    JOIN e.user u WITH u.id =:user_id
                    ORDER BY e.id DESC");

        $query->setParameter('user_id', $user_id);
        $history = $query->getResult();
        return $history;
    }

}
