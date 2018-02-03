<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 *
 * @property \MapasCulturais\Entities\Agent $destination The agent to be related
 * @property-read string $group The Agent relation Group
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestAgentRelation extends Request{

    function setAgentRelation(AgentRelation $relation){
        $this->destination = $relation->agent;
        $this->origin = $relation->owner;
        $this->metadata['class'] = $relation->getClassName();
        $this->metadata['relationId'] = $relation->id;

    }

    protected function getAgentRelation(){
        $app = App::i();
        $relation = $app->repo($this->metadata['class'])->find($this->metadata['relationId']);
        return $relation;
    }

    function _doApproveAction() {
        $relation = $this->getAgentRelation();
        if($relation){
            $relation->status = AgentRelation::STATUS_ENABLED;
            $relation->save(true);
        }
    }

    function _doRejectAction() {
        $relation = $this->getAgentRelation();
        if($relation){
            $relation->delete(true);
        }
    }
}