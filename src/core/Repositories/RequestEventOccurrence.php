<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Entities;

class RequestEventOccurrence extends \MapasCulturais\Repository{
    function findByEventOccurrence(Entities\EventOccurrence $occ){
        $request_uid = Entities\RequestEventOccurrence::generateRequestUid(
                $occ->event->className,
                $occ->event->id,
                $occ->space->className,
                $occ->space->id,
                [
                    'event_occurrence_id' => $occ->id,
                    'rule' => $occ->rule
                ]
            );

        return $this->findBy(['requestUid' => $request_uid]);
    }
}