<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\i;

/**
 * EventMeta
 *
 * @ORM\Table(name="event_attendance", indexes={
 *      @ORM\Index(name="event_attendance_type_idx", columns={"type"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class EventAttendance extends \MapasCulturais\Entity {
    const TYPE_CONFIRMATION = 'confirmation';
    const TYPE_INTEREST = 'interested';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_attendance_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="reccurrence_string", type="text", nullable=true)
     */
    protected $reccurrenceString;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_timestamp", type="datetime", nullable=false)
     */
    protected $startTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_timestamp", type="datetime", nullable=false)
     */
    protected $endTimestamp;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var \MapasCulturais\Entities\EventOccurrence
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EventOccurrence")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_occurrence_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $_eventOccurrence;

    /**
     * @var \MapasCulturais\Entities\Event
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $_event;


    /**
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="space_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $_space;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;


    function __construct($recurrence_string = null) {
        parent::__construct();
        
        if($recurrence_string){
            $this->setReccurrenceString($recurrence_string);
        }

        $this->user = App::i()->user;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'reccurrenceString' => $this->reccurrenceString,
            'user' => $this->user->id,
            'createTimestamp' => $this->createTimestamp,
            '_eventOccurrence' => $this->_eventOccurrence->id,
            '_event' => $this->event->id,
            '_space' => $this->space->id
        ];
    }

    function setUser($user){
        $app = App::i();
        if(is_numeric($user)){
            $user = $app->repo('User')->find($user);
        } else if(is_string($user)){
            $user = $app->repo('User')->getByProcurationToken($user);
        }

        $this->user = $user;
    }

    static function getValidations() {
        return [
            'type' => [
                'required' => i::__('O tipo do comparecimento é obrigatório'),
                "v::in(['confirmation','interested'])" => i::__('O tipo deve ser "confirmation" ou "interested"')
            ],
            'reccurrenceString' => [
                'required' => i::__('A string da recorrência é obrigatória'),
                '$this->validateReccurrenceString()' => i::__('A string da recorrência é inválida')
            ]
        ];
    }

    function validateReccurrenceString(){
        $app = App::i();

        $matches = null;
        
        if(!preg_match("#^\d+\.\d{4}-\d{2}-\d{2}\.\d{2}:\d{2}:\d{2}\.(\d{4}-\d{2}-\d{2})?\.\d{2}:\d{2}:\d{2}$#", $this->reccurrenceString, $matches)){
            return false;
        }

        list(
            $occurrence_id, 
            $starts_on,
            $starts_at,
            $ends_on,
            $ends_at
        ) = explode('.', $this->reccurrenceString);

        $ends_on = $ends_on ?: $starts_on;        
        
        // @TODO: usar um sql simples pra só pegar o id
        if(!$app->repo('EventOccurrence')->find($occurrence_id)){
            return false;
        }

        if("{$starts_on}{$starts_at}" > "{$ends_on}{$ends_at}"){
            return false;
        }
        return true;
    }

    /**
     * String que representa a recorrência
     * 
     * composto por "occurrence_id.envet_id.space_id.starts_on.starts_at.ends_on.ends_at"
     *
     * @param [type] $recurrence_string
     * @return void
     */
    function setReccurrenceString($recurrence_string){
        $this->reccurrenceString = $recurrence_string;

        if(!$this->validateReccurrenceString()){
            return false;
        }

        list(
            $occurrence_id, 
            $starts_on,
            $starts_at,
            $ends_on,
            $ends_at
        ) = explode('.', $recurrence_string);

        $ends_on = $ends_on ?: $starts_on;
        
        $event_occurrence = App::i()->repo('EventOccurrence')->find($occurrence_id);

        $this->_setEventOccurrence($event_occurrence);

        $this->startTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', "{$starts_on} {$starts_at}");
        $this->endTimestamp = \DateTime::createFromFormat('Y-m-d H:i:s', "{$ends_on} {$ends_at}");

        return true;
    }

    protected function _setEventOccurrence(EventOccurrence $event_occurrence){
        $this->_eventOccurrence = $event_occurrence;
        $this->_space = $event_occurrence->space;
        $this->_event = $event_occurrence->event;
    }

    /**
     * EventOccurrence
     *
     * @return \MapasCulturais\Entities\EventOccurrence
     */
    function getEventOccurrence(){
        return $this->_eventOccurrence;
    }

    /**
     * Event
     *
     * @return \MapasCulturais\Entities\Event
     */
    function getEvent(){
        return $this->_event;
    }

    /**
     * Space
     *
     * @return \MapasCulturais\Entities\Space
     */
    function getSpace(){
        return $this->_space;
    }

    protected function canUserCreate($user){
        if(is_null($user) || $user->is('guest')){
            return false;
        }
        if($user->is('admin')){
            return true;
        }

        if($user->equals($this->user)){
            return true;
        }

        return false;
    }

    function save($flush = false) {
        parent::save($flush);

        $query = App::i()->em->createQuery('
        DELETE FROM 
            MapasCulturais\Entities\EventAttendance e 
        WHERE 
            e.id <> :id AND
            e.user = :u AND 
            e.reccurrenceString = :reccurrenceString');

        $query->setParameters([
            'id' => $this->id,
            'u' => $this->user,
            'reccurrenceString' => $this->reccurrenceString
        ]);

        $query->execute();
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
