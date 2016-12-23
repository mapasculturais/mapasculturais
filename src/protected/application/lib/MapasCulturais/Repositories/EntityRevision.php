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

    public function findEntityRevisions($entity) {
        $objectId = $entity->id;
        $objectType = $entity->getClassName();
        $query = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$objectId} AND e.objectType = '{$objectType}'
                                            ORDER BY e.id DESC");
        return $query->getResult();
    }

    public function findCreateRevisionObject($id) {
        $app = App::i();
        $qryRev = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.id = {$id}");
        $qryRev->setMaxResults(1);
        $entityRevision = $qryRev->getOneOrNullResult();
        $actualEntity = $app->repo($entityRevision->objectType)->find($entityRevision->objectId);
        $entityRevisioned = new $entityRevision->objectType;
        foreach($entityRevision->data as $dataRevision) {
            if(!is_array($dataRevision->data) && !is_object($dataRevision->data)) {
                $data = $dataRevision->data;
            } else {
                $data = $dataRevision->getValue();
            }
            $attibute = $dataRevision->key;
            $entityRevisioned->$attibute = $data;
        }
        //$entityRevisioned->dump();
        return $entityRevision;
    }
}
