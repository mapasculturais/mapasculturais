<?php
namespace MapasCulturais\Traits;


trait RepositoryCache{
    function usesCache(){
        return true;
    }
    
    function getEntityCacheId($entity_id){
        return $this->getClassName() . '->find:' . $entity_id;
    }
    
    function find($id, $lockMode = \Doctrine\DBAL\LockMode::NONE, $lockVersion = null) {
        if(!$id)
            return null;


        $query = $this->_em->createQuery('SELECT e FROM ' . $this->getClassName() . ' e WHERE e.id = :id');
        $query->setParameter('id', $id);
        $query->useResultCache(true);
        $query->setResultCacheId($this->getEntityCacheId($id));
        $query->setResultCacheLifetime(5 * 60);
        
        return $query->getOneOrNullResult();
    }
    
    function deleteEntityCache($entity_id){
        $em = $this->_em;

        $cacheDriver = $em->getConfiguration()->getResultCacheImpl();
        $cacheDriver->delete($this->getEntityCacheId($entity_id));
    }
}