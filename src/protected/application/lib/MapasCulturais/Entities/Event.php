<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;


/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Event")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityMetaLists,
        Traits\EntityTaxonomies,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive;


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

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventOccurrence", mappedBy="event", cascade="remove", orphanRemoval=true)
    */
    protected $occurrences = [];

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    protected $project = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;


    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\ProjectFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\EventAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\EventTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventTermRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\EventSealRelation[] EventSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventSealRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__sealRelations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    private $_newProject = false;
    
    public function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Eventos');
        else
            return \MapasCulturais\i::__('Evento');
    }
    
    static function getValidations() {
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do evento é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'project' => [
                '$this->validateProject()' => \MapasCulturais\i::__('Você não pode criar eventos neste projeto.')
            ]
        ];
    }
    
    function publish($flush = false){
        $this->checkPermission('publish');

        $app = App::i();

        $app->disableAccessControl();

        $this->status = self::STATUS_ENABLED;
        $this->save($flush);

        $app->enableAccessControl();
    }

    public function save($flush = false) {
        App::i()->hook("entity($this).save:requests", function(){
            if($this->_newProject !== false){
                try{
                    if($this->_newProject){
                        $this->_newProject->checkPermission('createEvents');
                    }

                    $this->project = $this->_newProject;

                }catch(\MapasCulturais\Exceptions\PermissionDenied $e){
                    if(!App::i()->isWorkflowEnabled())
                        throw $e;

                    $request = new RequestEventProject;
                    $request->origin = $this;
                    $request->destination = $this->_newProject;
                    $this->_newProject = false;

                    throw new \MapasCulturais\Exceptions\WorkflowRequestTransport($request);
                }
            }
        });
        parent::save($flush);
    }

    protected function validateProject(){
        if($this->_newProject){
            return $this->_newProject->canUser('requestEventRelation');
        }else{
            return true;
        }
    }

    function setProjectId($projectId){
        if($projectId){
            $project = App::i()->repo('Project')->find((int)$projectId);
            $this->setProject($project);
        }else{
            $this->setProject(null);
        }
    }

    function setProject($project) {
        if (is_object($this->project)) {
            if (!$this->project->equals($project)) {
                $this->_newProject = $project;
            }
        } else {
            $this->_newProject = $project;
        }
    }

    function getProject(){
        return $this->_newProject !== false ? $this->_newProject : $this->project;
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
        $rsm->addFieldResult('e', 'rule', 'rule');

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                eo.*
            FROM
                event_occurrence eo WHERE eo.id IN (
                    SELECT DISTINCT id FROM recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL)
                    WHERE space_id = :space_id
                    AND   event_id = :event_id
                )

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);

        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'space_id' => $space->id,
            'event_id' => $this->id
        ]);

        $result = $query->getResult();

        return $result ? $result : [];
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
        $rsm->addFieldResult('e', 'space_id', 'spaceId');

        $dql_limit = $dql_offset = '';

        if($limit)
            $dql_limit = 'LIMIT ' . $limit;

        if($offset)
            $dql_offset = 'OFFSET ' . $offset;

        $strNativeQuery = "
            SELECT
                nextval('occurrence_id_seq'::regclass) as id,
                starts_on, until, starts_at, ends_at, space_id
            FROM
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                WHERE eo.event_id = :event_id

            $dql_limit $dql_offset

            ORDER BY
                eo.starts_on, eo.starts_at";

        $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

        if($app->config['app.useEventsCache'])
            $query->useResultCache (true, $app->config['app.eventsCache.lifetime']);


        $query->setParameters([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'event_id' => $this->id
        ]);

        $result = $query->getResult();

        return $result ? $result : [];
    }

    protected function canUserCreate($user){
        $can = $this->_canUser($user, 'create'); // this is a method of Trait\EntityOwnerAgent

        if($can && $this->project){
            return $this->project->userHasControl($user);
        }else{
            return $can;
        }
    }

    protected function canUserModify($user){
        $can = $this->_canUser($user, 'modify'); // this is a method of Trait\EntityOwnerAgent
        if($this->_projectChanged && $can && $this->project){
            return $this->project->userHasControl($user);
        }else{
            return $can;
        }
    }

    protected function canUserPublish($user){
        if($user->is('guest')){
            return false;
        }

        if($user->is('admin')){
            return true;
        }

        if($this->canUser('@control', $user)){
            return true;
        }

        if($this->project && $this->project->canUser('@control', $user)){
            return true;
        }

        return false;
    }

    protected function canUserView($user){
        if($this->status === self::STATUS_ENABLED){
            return true;
        }else if($this->status === self::STATUS_DRAFT){
            return $this->canUser('@control', $user) || ($this->project && $this->project->canUser('@control', $user));
        }

        return false;
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
