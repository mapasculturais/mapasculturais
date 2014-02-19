<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Entities\Repositories\Event")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends \MapasCulturais\Entity
{
    use \MapasCulturais\Traits\EntityTypes,
        \MapasCulturais\Traits\EntityMetadata,
        \MapasCulturais\Traits\EntityFiles,
        \MapasCulturais\Traits\EntityMetaLists,
        \MapasCulturais\Traits\EntityTaxonomies,
        \MapasCulturais\Traits\EntityAgentRelation,
        \MapasCulturais\Traits\EntityVerifiable;



    protected static $validations = array(
        'name' => array(
            'required' => 'O nome do evento é obrigatório'
        ),
        'shortDescription' => array(
            'required' => 'O descrição curta é obrigatória'
        )

    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    protected $_type = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=false)
     */
    protected $shortDescription = '';

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    protected $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="rules", type="text", nullable=true)
     */
    protected $rules;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    protected $_avatar;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventOccurrence", mappedBy="event", cascade="remove", orphanRemoval=true)
    */
    protected $occurrences = array();

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;

    /**
     * Returns the owner of this event
     * @return \MapasCulturais\Entities\Agent
     */
    function getOwner(){

        if(!$this->id)
            return App::i()->user->profile;

        return $this->owner;
    }


    function setOwnerId($owner_id){
        $owner = App::i()->repo('Agent')->find($owner_id);
        if($owner)
            $this->owner = $owner;
    }

    function getAvatar(){
        if(!$this->_avatar)
            $this->_avatar = $this->getFile('avatar');

        return $this->_avatar;
    }



    public function findOccurrencesBySpace(\MapasCulturais\Entities\Space $space, $date_from = null, $date_to = null, $limit = null, $offset = null){
        $app = App::i();
        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\EventOccurrence','e');


        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'starts_on', '_startsOn');
        $rsm->addFieldResult('e', 'until', '_until');
        $rsm->addFieldResult('e', 'starts_at', '_startsAt');
        $rsm->addFieldResult('e', 'ends_at', '_endsAt');

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                MD5(CONCAT(starts_on, starts_at, id)) as id,
                starts_on, until, starts_at, ends_at
            FROM
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                WHERE eo.space_id = :space_id
                AND eo.event_id = :event_id

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'space_id' => $space->id,
            'event_id' => $this->id
        ));

        $result = $query->getResult();

        return $result ? $result : array();
    }


    public function findOccurrences($date_from = null, $date_to = null, $limit = null, $offset = null){
        $app = App::i();
        if(is_null($date_from))
            $date_from = date('Y-m-d');
        else if($date_from instanceof \DateTime)
            $date_from = $date_from->format('Y-m-d');

        if(is_null($date_to))
            $date_to = $date_from;
        else if($date_to instanceof \DateTime)
            $date_to = $date_to->format('Y-m-d');

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\EventOccurrence','e');


        $rsm->addFieldResult('e', 'id', 'id');
        $rsm->addFieldResult('e', 'starts_on', '_startsOn');
        $rsm->addFieldResult('e', 'until', '_until');
        $rsm->addFieldResult('e', 'starts_at', '_startsAt');
        $rsm->addFieldResult('e', 'ends_at', '_endsAt');

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                MD5(CONCAT(starts_on, starts_at, id)) as id,
                starts_on, until, starts_at, ends_at
            FROM
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                WHERE eo.event_id = :event_id

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'event_id' => $this->id
        ));

        $result = $query->getResult();

        return $result ? $result : array();
    }

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PostLoad */
    public function postLoad($args = null){ parent::postLoad($args); }

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
