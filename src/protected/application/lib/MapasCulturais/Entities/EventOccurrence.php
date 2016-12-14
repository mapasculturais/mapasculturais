<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;


/**
 * EventOccurrence
 *
 * @ORM\Table(name="event_occurrence")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\EventOccurrence")
 * @ORM\HasLifecycleCallbacks
 */
class EventOccurrence extends \MapasCulturais\Entity
{
    const STATUS_PENDING = -5;

    private $flag_day_on = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_occurrence_id_seq", allocationSize=1, initialValue=1)
     */

    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="starts_on", type="date", nullable=true)
     */
    protected $_startsOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ends_on", type="date", nullable=true)
     */
    protected $_endsOn;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="starts_at", type="datetime", nullable=true)
     */
    protected $_startsAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ends_at", type="datetime", nullable=true)
     */
    protected $_endsAt;

    /**
     * @var frequency
     *
     * @ORM\Column(name="frequency", type="frequency", nullable=true)
     */
    protected $frequency;

    /**
     * @var integer
     *
     * @ORM\Column(name="separation", type="integer", nullable=false)
     */
    protected $separation = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    protected $count;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="until", type="date", nullable=true)
     */
    protected $_until;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone_name", type="text", nullable=false)
     */
    protected $timezoneName = 'Etc/UTC';

    /**
     * @var \MapasCulturais\Entities\Event
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Event", cascade="persist")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    protected $event;

    /**
     * @var eventId
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    protected $eventId;

    /**
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="space_id", referencedColumnName="id")
     * })
     */
    protected $space;


    /**
     * @var spaceId
     *
     * @ORM\Column(name="space_id", type="integer", nullable=false)
     */
    protected $spaceId;

    /**
     * @var string
     *
     * @ORM\Column(name="rule", type="text", nullable=false)
     */
    protected $rule;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    static function getValidations() {
        return [
            'startsOn' => [
                'required' => \MapasCulturais\i::__('Data de inicio é obrigatória'),
                '$value instanceof \DateTime' => \MapasCulturais\i::__('Data de inicio inválida')
            ],
            'endsOn' => [
                '$value instanceof \DateTime' => \MapasCulturais\i::__('Data final inválida'),
            ],
            'startsAt' => [
                'required' => \MapasCulturais\i::__('Hora de inicio é obrigatória'),
                '$value instanceof \DateTime || preg_match("#([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?#", $value)' => \MapasCulturais\i::__('Hora de inicio inválida'),
            ],
            'duration' => [
                //'required' => 'A duração é obrigatória',
                '$value instanceof \DateInterval' => \MapasCulturais\i::__('Duração inválida'),//'Hora final inválida'
            ],
            'frequency' => [
                'required' => \MapasCulturais\i::__('Frequência é obrigatória'),
                '$this->validateFrequency($value)' => \MapasCulturais\i::__('Frequência inválida')
            ],
            'separation' => [
                'v::positive()' => \MapasCulturais\i::__('Erro interno')
            ],
            'until' => [
                '$value instanceof \DateTime' => \MapasCulturais\i::__('Data final inválida'),
                '$value >= $this->startsOn' => \MapasCulturais\i::__('Data final antes da inicial')
            ],
            'event' => [
                'required' => \MapasCulturais\i::__('Evento é obrigatório')
            ],
            'space' => [
                'required' => \MapasCulturais\i::__('Espaço é obrigatório')
            ],
            'description' => [
                'required' => \MapasCulturais\i::__('A descrição legível do horário é obrigatória')
            ]

        ];
    }



    function validateFrequency($value) {
        if ($this->flag_day_on === false) return false;
        if (in_array($value, ['daily', 'weekly', 'monthly'])) {
            return !is_null($this->until);
        }

        return true;
    }

    static function convert($value='', $format='Y-m-d H:i')
    {
        if ($value === null || $value instanceof \DateTime) {
            return $value;
        }

        $d = \DateTime::createFromFormat($format, $value);
        if ($d && $d->format($format) == $value) {
            return $d;
        } else {
            return $value;
        }
    }

    function setStartsOn($value) {
        $this->_startsOn = self::convert($value, 'Y-m-d');
    }

    function getStartsOn() {
        return $this->_startsOn;
    }

    function setEndsOn($value) {
        $this->_endsOn = self::convert($value, 'Y-m-d');
    }

    function getEndsOn() {
        return $this->_endsOn;
    }


    function setStartsAt($value) {
        $this->_startsAt = self::convert($value, 'Y-m-d H:i');
    }

    function getStartsAt() {
        return $this->_startsAt;
    }


    function setEndsAt($value) {
        $this->_endsAt = self::convert($value, 'Y-m-d H:i');
    }

    function getEndsAt() {
        return $this->_endsAt;
    }

    function getDuration() {
        if($this->startsAt instanceof \DateTime && $this->endsAt instanceof \DateTime){
            $startsAtCopy = new \DateTime($this->startsAt->format('Y-m-d H:i:s'));
            $endsAtCopy = new \DateTime($this->endsAt->format('Y-m-d H:i:s'));
            $interval = $endsAtCopy->diff($startsAtCopy);
            return $interval;
        }else{
            return null;
        }
    }

    function setUntil($value) {
        $this->_until = self::convert($value, 'Y-m-d');
    }

    function getUntil() {
        return $this->_until;
    }

    function getRecurrences() {
        if ($this->id) {
            return App::i()->repo('EventOccurrenceRecurrence')->findBy(['eventOccurrence'=> $this]);
        } else {
            return [];
        }
    }

    function getDescription(){
        return isset($this->getRule()->description) ? $this->getRule()->description : "";
    }


    function getPrice(){
        return key_exists('price', $this->rule) ? $this->rule['price'] : '';
    }

    function setRule($value) {
        
        if ($value === '') {
            $this->rule = '';
            return;
        }
        $value = (array) $value;

        $this->startsAt = $value['startsOn'] . ' ' . $value['startsAt'];
        //$this->endsAt = @$value['startsOn'] . ' ' . @$value['endsAt'];

        if(!empty($value['duration'])){
            $value['duration'] = intval($value['duration']);
            $dateString = 'PT'.$value['duration'] .'M';

            if($this->startsAt instanceof \DateTime){
                $startsAtCopy = new \DateTime($this->startsAt->format('Y-m-d H:i'));
                $this->endsAt = $startsAtCopy->add(new \DateInterval($dateString));
            }
        }else{
            $value['duration'] = 0;
            $this->endsAt = $this->startsAt; // don't attributing causes the duration to be 1 minute
        }
        if($this->endsAt instanceof \DateTime){
            $value['endsAt'] = $this->endsAt->format('H:i');
        }

        $this->startsOn = $value['startsOn'];
        $this->until = $value['until'] ? $value['until'] : null;
        $this->frequency = $value['frequency'];

        $this->rule = json_encode($value);

        if ($this->validationErrors) {
            return;
        }

        foreach ($this->recurrences as $recurrence) {
            $recurrence->delete();
        }

        if ($value['frequency']) {
            $freq = $this->frequency;
            $days = isset($value['day']) ? $value['day'] : null;
            switch ($freq) {
                case 'weekly':
                    $this->flag_day_on = false;

                    if (is_null($days)) break;
                    foreach ($days as $key => $value) {
                        if ($value === 'off') break;

                        $this->flag_day_on = true;
                        $rec = new EventOccurrenceRecurrence;
                        $rec->eventOccurrence = $this;
                        $rec->day = (int) $key;
                        $rec->week = null;
                        $rec->month = null;

                        $rec->save();
                    }
                    break;

                case 'monthly':
                    if (isset($value['monthly']) && $value['monthly']==='week') {
                        $this->flag_day_on = false;

                        if (is_null($days)) break;
                        foreach ($days as $key => $value) {
                            if ($value === 'off') break;

                            $this->flag_day_on = true;
                            $rec = new EventOccurrenceRecurrence;
                            $rec->eventOccurrence = $this;
                            $rec->day = (int) $key;
                            $rec->week = 1;  # TODO: calc week
                            $rec->month = null;
                            $rec->save();
                        }
                    } else {
                        $rec = new EventOccurrenceRecurrence;
                        $rec->eventOccurrence = $this;
                        $rec->day = $this->startsOn === null ? 0 : $this->startsOn->format('j');
                        $rec->week = null;
                        $rec->month = null;
                    }

                    break;
            }
        }
    }

    function getRule() {
        return json_decode($this->rule);
    }

    function jsonSerialize() {
        return [
            'id' => $this->id,
            'rule'=> $this->getRule(),
            'startsOn' => $this->startsOn,
            'startsAt' => $this->startsAt,
            'endsOn' => $this->endsOn,
            'endsAt' => $this->endsAt,
            'duration' => $this->duration,
            'frequency' => $this->frequency,
            'separation' =>  $this->separation,
            'recurrences' => $this->getRecurrences(),
            'count' =>  $this->count,
            'until' =>  $this->until,
            'spaceId' =>  $this->spaceId,
            'space' => $this->space ? $this->space->simplify('id,name,singleUrl,shortDescription,avatar,location,terms') : null,
            'event' => $this->event ? $this->event->simplify('id,name,singleUrl,shortDescription,avatar') : null,
            'editUrl' => $this->editUrl,
            'deleteUrl' => $this->deleteUrl,
            'status' => $this->status
        ];
    }

    protected function canUserCreate($user){
        if($user->is('guest'))
            return false;

        if($user->is('admin'))
            return true;

        return ( $this->space->public || $this->space->canUser('modify', $user) ) && $this->event->canUser('modify', $user);
    }

    protected function canUserModify($user){
        if($user->is('guest'))
            return false;

        if($user->is('admin'))
            return true;

        return $this->space->canUser('modify', $user) && $this->event->canUser('modify', $user);
    }

    function save($flush = false) {
        try{
            parent::save($flush);

        }catch(\MapasCulturais\Exceptions\PermissionDenied $e){
            if(!App::i()->isWorkflowEnabled())
                throw $e;

            $app = App::i();
            $app->disableAccessControl();
            $this->status = self::STATUS_PENDING;
            parent::save($flush);
            $app->enableAccessControl();

            $request = new RequestEventOccurrence;
            $request->origin = $this->event;
            $request->destination = $this->space;
            $request->eventOccurrence = $this;
            $request->save(true);

            throw new \MapasCulturais\Exceptions\WorkflowRequest([$request]);
        }
    }

    function delete($flush = false) {
        $this->checkPermission('remove');
        // ($originType, $originId, $destinationType, $destinationId, $metadata)
        $ruid = RequestEventOccurrence::generateRequestUid($this->event->getClassName(), $this->event->id, $this->space->getClassName(), $this->space->id, ['event_occurrence_id' => $this->id, 'rule' => $this->getRule()]);
        $requests = App::i()->repo('RequestEventOccurrence')->findBy(['requestUid' => $ruid]);
        foreach($requests as $r)
            $r->delete($flush);

        parent::delete($flush);
    }

    /** @ORM\PreRemove */
    function _removeRequests(){
        if($this->status === self::STATUS_PENDING){
            $requests = App::i()->repo('RequestEventOccurrence')->findByEventOccurrence($this);
            if($requests)
                foreach ($requests as $req)
                    $req->delete();
        }
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
