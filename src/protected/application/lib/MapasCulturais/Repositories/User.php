<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\Entities;

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
    
    public function getByRole($role) {
        $user_query = $this->_em->createQuery('SELECT u FROM MapasCulturais\Entities\User u 
            JOIN u.roles r WITH r.name =:role');
        
        $user_query->setParameter('role', $role);
        $users = $user_query->getResult();
        return $users;

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

        return $q->getResult();
    }
}
