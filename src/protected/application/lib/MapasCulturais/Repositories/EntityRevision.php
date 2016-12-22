<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\App;

class EntityRevision extends \MapasCulturais\Repository{

    public function findLastRevision($entity) {
        $objectId = $entity->id;
        $objectType = $entity->getClassName();
        $query = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$objectId} AND e.objectType = '{$objectType}'
                                            ORDER BY e.id DESC");
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }
}
