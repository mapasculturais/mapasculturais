<?php
namespace MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\i;
use MapasCulturais\Entities\EntityRevision as Revision;

/**
 * @property-read \MapasCulturais\Entities\EntityRevision $lastRevision
 * @property-read \MapasCulturais\Entities\EntityRevision[] $revisions
 */
trait EntityRevision{

    /**
     * This entity uses Revision
     *
     * @return bool true
     */
    public static function usesRevision(){
        return true;
    }

    protected function _getRevisionIds($entity_class, $entities) {
        $ids = [];
        foreach($entities as $entity) {
            $ids[] = $entity->id;
        }

        $ids = implode(',', $ids);

        $conn = App::i()->em->getConnection();
        $rs = $conn->fetchAll("
            SELECT 
                MAX(id) as revision_id, object_id
            FROM 
                entity_revision
            WHERE 
                object_type = '{$entity_class}' AND object_id in ({$ids}) 
            GROUP BY object_id
        ");
        $result = [];
        foreach($rs as $r) {
            $result[$r['object_id']] = $r['revision_id'];
        }
        return $result;
    }

    public function _getRevisionData() {
        $app = App::i();
        $class_metadata = $app->em->getClassMetadata($this->getClassName());
        $fields = $class_metadata->getFieldNames();
        $removedFields = ['id','_geoLocation','userId'];
        $entity_data = null;
        $revisionData = [];
        foreach($fields as $field) {
            if(!in_array($field,$removedFields)) {
                $revisionData[$field] = $this->$field;
            }
        }
        $relations = $class_metadata->getAssociationMappings();
        if(array_key_exists("owner",$relations)) {
            if(isset($this->owner)) {
                $entity_data = $this->owner->simplify("id,name,shortDescription");
                $entity_data->{'revision'} = $app->repo('EntityRevision')->findEntityLastRevisionId($this->owner->getClassName(),$entity_data->id);
                $revisionData["owner"] = $entity_data;
            }
        }

        if(array_key_exists("parent",$relations)) {
            if($this->parent) {
                $entity_data = $this->parent->simplify("id,name");
                $entity_data->{'revision'} = $app->repo('EntityRevision')->findEntityLastRevisionId($this->parent->getClassName(),$entity_data->id);
                $revisionData["parent"] = $entity_data;
            }
        }

        if(array_key_exists("__metadata",$relations) && count($this->__metadata) > 0) {
            foreach($this->__metadata as $key => $metadata) {
                $revisionData[$metadata->key] = $metadata->value;
            }
        }

        if(array_key_exists("_spaces",$relations) && count($this->_spaces) > 0) {
            $revisions = $this->_getRevisionIds(Entities\Space::class, $this->_spaces);

            foreach($this->_spaces as $space) {
                $entity_data = $space->simplify("id,name");
                $entity_data->{'revision'} = $revisions[$space->id];
                $revisionData['_spaces'][] = $entity_data;
            }
        }

        if(array_key_exists("_events",$relations) && count($this->_events) > 0) {
            $revisions = $this->_getRevisionIds(Entities\Event::class, $this->_events);
            foreach($this->_events as $event) {
                $entity_data = $event->simplify("id,name");
                $entity_data->{'revision'} = $revisions[$event->id];
                $revisionData['_events'][] = $entity_data;
            }
        }

        if($this->usesTaxonomies()) {
            foreach($this->__termRelations as $termRelation) {
                $revisionData['_terms'][$termRelation->term->taxonomySlug][] = $termRelation->term->term;
            }
        }

        if($this->usesSealRelation()) {
            foreach($this->__sealRelations as $sealRelation) {
                $entity_data = $sealRelation->seal->simplify();
                $revisionData['_seals'][] = $entity_data;
            }
        }

        if($this->usesAgentRelation()) {
            if($related_agents = $this->relatedAgents){
                $all_agents = [];
                foreach($related_agents as $agents) {
                    $all_agents = array_merge($all_agents, $agents);
                }
                $revisions = $this->_getRevisionIds(Entities\Agent::class, $all_agents);
    
                foreach($related_agents as $group => $agents) {
                    $revisionData['_agents'][$group] = [];
                    foreach($agents as $agent) {
                        $entity_data = $agent->simplify();
                        $entity_data->{'revision'} = $revisions[$agent->id];
                        $revisionData['_agents'][$group][] = $entity_data;
                    }
                }
            }
        }

        if($this->usesMetaLists()) {
            $groups = $app->getRegisteredMetaListGroupsByEntity($this);
            foreach(array_keys($groups) as $group) {
                $items = $this->getMetaLists($group);
                foreach($items as $item) {
                    $revisionData[$group][] = [
                        'id' => $item->id,
                        'title' => $item->title,
                        'value' => $item->value
                    ];
                }
            }
        }

        if(method_exists($this, 'getRevisionData')){
            $entity_data = $this->getRevisionData();
            if(is_array($entity_data) && count($entity_data) > 0) {
                $revisionData += $entity_data;
            }
        }
        return $revisionData;
    }

    public function _newCreatedRevision() {
        $revisionData = $this->_getRevisionData();
        $message = i::__("Registro criado.");
        
        $revision = new Revision($revisionData,$this,Revision::ACTION_CREATED,$message);
        $revision->save(true);
    }

    public function _newModifiedRevision() {
        $revisionData = $this->_getRevisionData();
        $action = Revision::ACTION_MODIFIED;
        $message = i::__("Registro atualizado.");
        
        $last_revision = $this->getLastRevision();
        $last_revision_data = $last_revision->getRevisionData();
        
        $old_status = $last_revision_data['status']->value;
        $new_status = $this->status;
        
        if($old_status != $new_status){
            switch ($new_status){
                case self::STATUS_ENABLED:
                    $action = Revision::ACTION_PUBLISHED;
                    $message = i::__("Registro publicado.");
                    break;
                
                case self::STATUS_ARCHIVED:
                    $action = Revision::ACTION_ARCHIVED;
                    $message = i::__("Registro arquivado.");
                    break;
                
                case self::STATUS_DRAFT:
                    if($old_status == self::STATUS_TRASH){
                        $message = i::__("Registro recuperado da lixeira.");
                        $action = Revision::ACTION_UNTRASHED;
                    } else if( $old_status == self::STATUS_ARCHIVED){
                        $message = i::__("Registro desarquivado.");
                        $action = Revision::ACTION_UNARCHIVED;
                    } else {
                        $action = Revision::ACTION_UNPUBLISHED;
                        $message = i::__("Registro despublicado.");
                    }
                    break;
                    
                case self::STATUS_TRASH:
                    $action = Revision::ACTION_TRASHED;
                    $message = i::__("Registro movido para a lixeira.");
                    break;
            }
        }

        $revision = new Revision($revisionData,$this,$action,$message);
        if($revision->modified) {
            $revision->save(true);
        }
    }

    public function _newDeletedRevision() {
        $revisionData = $this->_getRevisionData();
        $action = Revision::ACTION_DELETED;
        $message = i::__("Registro deletado.");
        $revision = new Revision($revisionData,$this,Revision::ACTION_DELETED,$message);
        $revision->save(true);
    }

    public function getLastRevision() {
        $app = App::i();
        $revision = $app->repo('EntityRevision')->findLastRevision($this);
        return $revision;
    }

    public function getRevisions() {
        $app = App::i();
        $revisions = $app->repo('EntityRevision')->findEntityRevisions($this);
        return $revisions;
    }

    public function getRevisionsByDate($date)
    {
        $app = App::i();
        $revisions = $app->repo('EntityRevision')->findEntityRevisionsByDate($this, $date);
        return $revisions;        
    }
}
