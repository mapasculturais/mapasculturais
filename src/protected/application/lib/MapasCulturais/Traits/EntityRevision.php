<?php
namespace MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entities\EntityRevision as Revision;

trait EntityRevision{
    use RepositoryEntityRevision;

    /**
     * This entity uses Revision
     *
     * @return bool true
     */
    public static function usesRevision(){
        return true;
    }

    public function _getRevisionData() {
        $app = App::i();
        $class_metadata = $app->em->getClassMetadata($this->getClassName());
        $fields = $class_metadata->getFieldNames();
        $removedFields = ['id','_geoLocation','createTimestamp','isVerified','userId','updateTimestamp'];
        foreach($fields as $field) {
            if(!in_array($field,$removedFields)) {
                $revisionData[$field] = $this->$field;
            }
        }
        $relations = $class_metadata->getAssociationMappings();

        if(array_key_exists("owner",$relations)) {
            $revisionData["owner"] = $this->owner->simplify("id,name");
        }

        if(array_key_exists("parent",$relations)) {
            if($this->parent) {
                $revisionData["parent"] = $this->parent->simplify("id,name");
            }
        }

        if(array_key_exists("__metadata",$relations) && count($this->__metadata) > 0) {
            foreach($this->__metadata as $key => $metadata) {
                $revisionData[$metadata->key] = $metadata->value;
            }
        }

        if(array_key_exists("_spaces",$relations) && count($this->_spaces) > 0) {
            foreach($this->_spaces as $space) {
                $revisionData['_spaces'][] = $space->simplify("id,name");
            }
        }

        if(array_key_exists("_events",$relations) && count($this->_events) > 0) {
            foreach($this->_events as $event) {
                $revisionData['_events'][] = $event->simplify("id,name");
            }
        }

        if(array_key_exists("_projects",$relations) && count($this->_projects) > 0) {
            foreach($this->_projects as $project) {
                $revisionData['_projects'][] = $project->simplify("id,name");
            }
        }

        if($this->usesTaxonomies()) {
            foreach($this->__termRelations as $termRelation) {
                $revisionData['_terms'][$termRelation->term->taxonomySlug][] = $termRelation->term->term;
            }
        }

        if($this->usesSealRelation()) {
            foreach($this->__sealRelations as $sealRelation) {
                $revisionData['_seals'][] = $sealRelation->seal->simplify();
            }
        }

        if($this->usesAgentRelation()) {
            foreach($this->__agentRelations as $agentRelation) {
                $revisionData['_agents'][$agentRelation->group][] = $agentRelation->agent->simplify();
            }
        }

        if(($links = $this->getMetaLists('links'))) {
            foreach($links as $link) {
                $revisionData['links'][] = [
                    'id' => $link->id,
                    'title' => $link->title,
                    'value' => $link->value
                ];
            }
        }

        if(($videos = $this->getMetaLists('videos'))) {
            foreach($videos as $video) {
                $revisionData['videos'][] = [
                    'id' => $video->id,
                    'title' => $video->title,
                    'value' => $video->value
                ];
            }
        }

        // if(($downloads = $this->getFiles('downloads'))) {
        //     foreach($downloads as $download) {
        //         $revisionData['downloads'][] = [
        //                 'id' => $download->id,
        //                 'description' => $download->description,
        //                 'url' => $download->url
        //         ];
        //     }
        // }

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
        $action = $this->controller->action;
        $message = "";
        if($action == Revision::ACTION_CREATED) {
            $message = "Registro criado.";
        }
        $revision = new Revision($revisionData,$this,Revision::ACTION_CREATED,$message);
        $revision->save(true);
    }

    public function _newModifiedRevision() {
        $revisionData = $this->_getRevisionData();
        $action = $this->controller->action;
        $message = "";

        if($action == Revision::ACTION_PUBLISHED) {
            $message = "Registro publicado.";
        } elseif($action == Revision::ACTION_UNPUBLISHED) {
            $message = "Registro despublicado.";
        } elseif($action == Revision::ACTION_ARCHIVED) {
            $message = "Registro arquivado.";
        } elseif($action == Revision::ACTION_UNARCHIVED) {
            $message = "Registro desarquivado.";
        } elseif($action == Revision::ACTION_TRASHED) {
            $message = "Registro movido para a lixeira.";
        } elseif($action == Revision::ACTION_UNTRASHED) {
            $message = "Registro removido para a lixeira.";
        } elseif($action == Revision::ACTION_DELETED) {
            $message = "Registro deletado.";
        } elseif($this->status > 0) {
            $action = Revision::ACTION_MODIFIED;
            $message = "Registro atualizado.";
        }

        $revision = new Revision($revisionData,$this,$action,$message);
        $revision->save(true);
    }

    public function _newDeletedRevision() {
        $revisionData = $this->_getRevisionData();
        $action = $this->controller->action;
        $message = "";
        if($action == Revision::ACTION_DELETED) {
            $message = "Registro deletado.";
        }
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
}
