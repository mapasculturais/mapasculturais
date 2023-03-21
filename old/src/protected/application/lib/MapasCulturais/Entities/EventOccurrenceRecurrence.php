<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventOccurrenceRecurrence
 *
 * @ORM\Table(name="event_occurrence_recurrence")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class EventOccurrenceRecurrence extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_occurrence_recurrence_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="month", type="integer", nullable=true)
     */
    protected $month;

    /**
     * @var integer
     *
     * @ORM\Column(name="day", type="integer", nullable=true)
     */
    protected $day;

    /**
     * @var integer
     *
     * @ORM\Column(name="week", type="integer", nullable=true)
     */
    protected $week;

    /**
     * @var \MapasCulturais\Entities\EventOccurrence
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EventOccurrence", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_occurrence_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $eventOccurrence;
    
    function jsonSerialize() {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'day' => $this->day,
            'week' => $this->week
        ];
    }

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
