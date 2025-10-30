<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventOccurrenceCancellation
 */
#[ORM\Table(name: "event_occurrence_cancellation")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
#[ORM\HasLifecycleCallbacks]
class EventOccurrenceCancellation extends \MapasCulturais\Entity
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "event_occurrence_cancellation_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\Column(name: "date", type: "date", nullable: true)]
    protected $date;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\EventOccurrence")]
    #[ORM\JoinColumn(name: "event_occurrence_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $eventOccurrence;

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    #[ORM\PrePersist]
    public function prePersist($args = null){ parent::prePersist($args); }
    
    #[ORM\PostPersist]
    public function postPersist($args = null){ parent::postPersist($args); }

    #[ORM\PreRemove]
    public function preRemove($args = null){ parent::preRemove($args); }
    
    #[ORM\PostRemove]
    public function postRemove($args = null){ parent::postRemove($args); }

    #[ORM\PreUpdate]
    public function preUpdate($args = null){ parent::preUpdate($args); }
    
    #[ORM\PostUpdate]
    public function postUpdate($args = null){ parent::postUpdate($args); }
}