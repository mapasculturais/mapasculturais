<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class Subsite extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword;

    public function getUserByRole($role,$subsite_id = false) {
        $query = 'SELECT u, a FROM MapasCulturais\Entities\User u
            JOIN u.profile a
            JOIN u.roles r WITH r.name = :role';
        if($subsite_id) {
            $query .=  " AND r.subsiteId = :subsite_id";
        }
        $user_query = $this->_em->createQuery($query);
        $user_query->setParameter('role', $role);
        if($subsite_id) {
            $user_query->setParameter('subsite_id', $subsite_id);
        }
        $users = $user_query->getResult();
        return $users;

    }
}
