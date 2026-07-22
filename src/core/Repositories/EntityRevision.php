<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Repositório para revisões de entidades
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de revisões de entidades no sistema,
 * permitindo o controle de versões e histórico de alterações.
 * 
 * @package MapasCulturais\Repositories
 */
class EntityRevision extends \MapasCulturais\Repository{
    use Traits\EntityGeoLocation;

    /**
     * Encontra a última revisão por tipo de objeto e ID
     * 
     * @param string $object_type Tipo do objeto
     * @param int $object_id ID do objeto
     * @return \MapasCulturais\Entities\EntityRevision|null Última revisão encontrada
     */
    public function findLastRevisionByObjectTypeAndId(string $object_type, int $object_id) {
        $query = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$object_id} AND e.objectType = '{$object_type}'
                                            ORDER BY e.id DESC");
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    /**
     * Encontra a última revisão de uma entidade
     * 
     * @param \MapasCulturais\Entity $entity Entidade
     * @return \MapasCulturais\Entities\EntityRevision|null Última revisão encontrada
     */
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

    /**
     * Encontra todas as revisões de uma entidade
     * 
     * @param \MapasCulturais\Entity $entity Entidade
     * @return array Todas as revisões da entidade
     */
    public function findEntityRevisions($entity) {
        $objectId = $entity->id;
        $objectType = $entity->getClassName();
        $query = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$objectId} AND e.objectType = '{$objectType}'
                                            ORDER BY e.id DESC");
        return $query->getResult();
    }

    /**
     * Encontra a revisão de uma entidade por data
     * 
     * @param \MapasCulturais\Entity $entity Entidade
     * @param \DateTime $date Data limite
     * @return \MapasCulturais\Entities\EntityRevision|null Revisão encontrada
     */
    public function findEntityRevisionsByDate($entity, $date) {
        $objectId = $entity->id;
        $objectType = $entity->getClassName();
        $query = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$objectId} AND e.objectType = '{$objectType}'
                                            AND e.createTimestamp <= :sendTimeStemp ORDER BY e.id DESC");

        $params = [
            'sendTimeStemp' => $date
        ];      

        $query->setParameters($params);
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    /**
     * Cria um objeto de revisão a partir do ID da revisão
     * 
     * @param int $id ID da revisão
     * @return \stdClass Objeto com dados da revisão
     */
    public function findCreateRevisionObject($id) {
        $app = App::i();
        $qryRev = $this->_em->createQuery("SELECT e
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.id = {$id}");
        $qryRev->setMaxResults(1);
        $entityRevision = $qryRev->getOneOrNullResult();
        $objectType = $entityRevision->objectType->getValue();
        $actualEntity = $app->repo($objectType)->find($entityRevision->objectId);
        $entityRevisioned = new \stdClass();
        $entityRevisioned->controller_id =  $actualEntity->getControllerId();
        $entityRevisioned->id = $actualEntity->id;
        $entityRevisioned->entityClassName = $objectType;
        $entityRevisioned->userCanView = $actualEntity->canUser('viewPrivateData');
        $entityRevisioned->entity = $actualEntity;

        $registeredMetadata = $app->getRegisteredMetadata($objectType);

        foreach(array_keys($registeredMetadata) as $metadata) {
            $entityRevisioned->$metadata = null;
        }

        foreach($entityRevision->data as $dataRevision) {
            if(!is_array($dataRevision) && !is_object($dataRevision)) {
                $data = $dataRevision;
            } else {
                $data = $dataRevision->value;
            }

            if($dataRevision->key == 'location' && $data->longitude != 0 && $data->latitude !=0) { 
                $entityRevisioned->location = new \MapasCulturais\Types\GeoPoint($data->longitude,$data->latitude);
            } elseif($dataRevision->key == 'createTimestamp' || $dataRevision->key == 'updateTimestamp') {
                $attribute = $dataRevision->key;
                if(isset($data->date)) {
                    $entityRevisioned->$attribute = \DateTime::createFromFormat('Y-m-d H:i:s.u',$data->date);
                }
            } else {
                $attribute = $dataRevision->key;
                $entityRevisioned->$attribute = $data;
            }
        }
        return $entityRevisioned;
    }

    /**
     * Encontra o ID da última revisão de uma entidade
     * 
     * @param string $classEntity Classe da entidade
     * @param int $entityId ID da entidade
     * @return int ID da última revisão (0 se não houver)
     */
    public function findEntityLastRevisionId($classEntity, $entityId) {
        $query = $this->_em->createQuery("SELECT e.id
                                            FROM MapasCulturais\Entities\EntityRevision e
                                            WHERE e.objectId = {$entityId} AND e.objectType = '{$classEntity}'
                                            ORDER BY e.id DESC");

        $query->setMaxResults(1);
        $return = $query->getOneOrNullResult();
        if(is_array($return) && count($return) > 0) {
            $return = $return['id'];
        } else {
            $return = 0;
        }
        return $return;
    }
}
