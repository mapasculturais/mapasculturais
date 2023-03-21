<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 *
 * @property \MapasCulturais\Entities\Space $destination The space to be related
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestSpaceRelation extends Request{

    function setSpaceRelation(SpaceRelation $relation){
        $this->destination = $relation->space;
        $this->origin = $relation->owner;
        $this->metadata['class'] = $relation->getClassName();
        $this->metadata['relationId'] = $relation->id;

    }

    protected function getSpaceRelation(){
        $app = App::i();
        $relation = $app->repo($this->metadata['class'])->find($this->metadata['relationId']);
        return $relation;
    }

    function _doApproveAction() {
        $relation = $this->getSpaceRelation();
        if($relation){
            $relation->status = SpaceRelation::STATUS_ENABLED;
            $relation->save(true);
        }
    }

    function _doRejectAction() {
        $relation = $this->getSpaceRelation();
        if($relation){
            $relation->delete(true);
        }
    }
}