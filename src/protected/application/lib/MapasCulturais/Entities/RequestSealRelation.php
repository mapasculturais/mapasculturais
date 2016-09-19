<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 *
 * @property \MapasCulturais\Entities\Seal $destination The seal to be related
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestSealRelation extends Request{

    function setSealRelation(SealRelation $relation){
        $this->destination = $relation->owner;
        $this->origin = $relation->seal;
        $this->metadata['class'] = $relation->getClassName();
        $this->metadata['relationId'] = $relation->id;

    }

    protected function getSealRelation(){
        $app = App::i();
        $relation = $app->repo($this->metadata['class'])->find($this->metadata['relationId']);
        return $relation;
    }

    function _doApproveAction() {
        $relation = $this->getSealRelation();
        if($relation){
        	$app = App::i();
        	$app->log->debug("SealRelationId".$relation->id);
            $relation->status = SealRelation::STATUS_ENABLED;
            $relation->save(true);
        }
    }

    function _doRejectAction() {
        $relation = $this->getSealRelation();
        if($relation){
            $relation->delete();
        }
    }
}