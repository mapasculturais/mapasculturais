<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\App;

class EntityRevision extends \MapasCulturais\Repository{
    use Traits\EntityGeoLocation;

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
        $entityRevisioned = new \stdClass();//$entityRevision->objectType;
        $entityRevisioned->controller_id =  $actualEntity->getControllerId();
        $app->log->debug("Controller: " . $entityRevisioned->controller_id);
        $entityRevisioned->id = $actualEntity->id;
        foreach($entityRevision->data as $dataRevision) {
            if(!is_array($dataRevision) && !is_object($dataRevision)) {
                $data = $dataRevision;
            } else {
                $data = $dataRevision->value;
            }

            /*if($dataRevision->key == 'owner') {
                $entityRevisioned->$attribute = $app->repo('agent')->find($data->id);
            } elseif($dataRevision->key == 'location') { 
                $app->log->debug($data);
                $entityRevisioned->location = new \MapasCulturais\Types\GeoPoint(0,0);
            } else {*/
                $attribute = $dataRevision->key;
                $entityRevisioned->$attribute = $data;
            //}
        }
        return $entityRevisioned;
    }
}
