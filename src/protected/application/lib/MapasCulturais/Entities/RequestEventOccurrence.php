<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @property \MapasCulturais\Entities\Space $destination The space where event occurrence will be created
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\RequestEventOccurrence")
 */
class RequestEventOccurrence extends Request{

    function setEventOccurrence(EventOccurrence $eo){
        $this->metadata['event_occurrence_id'] = $eo->id;
        $this->metadata['rule'] = $eo->rule;
    }

    /**
     *
     * @return EventOccurrence
     */
    function getEventOccurrence(){
        if(isset($this->metadata['event_occurrence_id']))
            return App::i()->repo('EventOccurrence')->find($this->metadata['event_occurrence_id']);
        else
            return null;
    }

    function getRule(){
        return isset($this->metadata['rule']) ? $this->metadata['rule'] : null;
    }

    function _doApproveAction() {
        $eo = $this->getEventOccurrence();
        if($eo){
            $eo->status = EventOccurrence::STATUS_ENABLED;
            $eo->save(true);
        }
    }

    function _doRejectAction() {
        $eo = $this->getEventOccurrence();
        if($eo){
            $eo->delete(true);
        }
        parent::_doRejectAction();
    }
}