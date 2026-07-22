<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Entities;

/**
 * Repositório para requisições de ocorrência de eventos
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de requisições relacionadas a ocorrências de eventos.
 * 
 * @package MapasCulturais\Repositories
 */
class RequestEventOccurrence extends \MapasCulturais\Repository{
    
    /**
     * Encontra requisições por ocorrência de evento
     * 
     * @param Entities\EventOccurrence $occ Ocorrência de evento
     * @return array Requisições encontradas
     */
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