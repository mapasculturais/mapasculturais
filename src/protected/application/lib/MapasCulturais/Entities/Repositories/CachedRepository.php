<?php
namespace MapasCulturais\Entities\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\LockMode;

use MapasCulturais\App;

class CachedRepository extends EntityRepository{
    function find($id, $lockMode = LockMode::NONE, $lockVersion = null) {
        if(!$id)
            return null;


        $query = $this->_em->createQuery('SELECT e FROM ' . $this->getClassName() . ' e WHERE e.id = :id');
        $query->setParameter('id', $id);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(5 * 60);
        
        return $query->getOneOrNullResult();
    }
}