<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * AgentRelation
 *
 * @property-read int $id The Id of the relation.
 * @property string $group Agent relation group name.
 * @property bool $hasControl
 * @property int $status
 * @property int $objectId
 * @property \MapasCulturais\Entities\Agent $agent
 * @property \DateTime $createTimestamp
 *
 * @todo http://thoughtsofthree.com/2011/04/defining-discriminator-maps-at-child-level-in-doctrine-2-0/
 *
 * @ORM\Table(name="agent_relation", indexes={
 *      @ORM\Index(name="agent_relation_owner_type", columns={"object_type"}),
 *      @ORM\Index(name="agent_relation_owner_id", columns={"object_id"}),
 *      @ORM\Index(name="agent_relation_owner", columns={"object_type", "object_id"}),
 *      @ORM\Index(name="agent_relation_agent", columns={"agent_id"}),
 *      @ORM\Index(name="agent_relation_has_control", columns={"has_control"}),
 *      @ORM\Index(name="agent_relation_status", columns={"status"}),
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="object_type")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Opportunity"   = "\MapasCulturais\Entities\OpportunityAgentRelation",
        "MapasCulturais\Entities\Project"       = "\MapasCulturais\Entities\ProjectAgentRelation",
        "MapasCulturais\Entities\Event"         = "\MapasCulturais\Entities\EventAgentRelation",
        "MapasCulturais\Entities\Agent"         = "\MapasCulturais\Entities\AgentAgentRelation",
        "MapasCulturais\Entities\Space"         = "\MapasCulturais\Entities\SpaceAgentRelation",
        "MapasCulturais\Entities\Seal"          = "\MapasCulturais\Entities\SealAgentRelation",
        "MapasCulturais\Entities\Registration"  = "\MapasCulturais\Entities\RegistrationAgentRelation",
        "MapasCulturais\Entities\EvaluationMethodConfiguration" = "\MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation",
        "MapasCulturais\Entities\ChatThread"    = "\MapasCulturais\Entities\ChatThreadAgentRelation",
   })
 * @ORM\HasLifecycleCallbacks
 */
abstract class AgentRelation extends \MapasCulturais\Entity
{
    const STATUS_PENDING = -5;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="agent_relation_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=true)
     */
    protected $group;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_control", type="boolean", nullable=false)
     */
    protected $hasControl = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=true)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $agent;

     /**
     * @var object
     *
     * @ORM\Column(name="metadata", type="json_array", nullable=true)
     */
    protected $metadata;


    function jsonSerialize() {
        $result = parent::jsonSerialize();
        $result['owner'] = $this->owner->simplify('className,id,name,terms,avatar,singleUrl');
        $result['agent'] = $this->agent->simplify('id,name,type,terms,avatar,singleUrl');
        $result['ownerUserId'] = $this->owner->ownerUser->id;
        $result['agentUserId'] = $this->agent->user->id;

        return $result;
    }

    protected function canUserCreate($user){
        $app = App::i();

        $agent_control = !$app->isWorkflowEnabled() || $this->agent->canUser('@control', $user);

        if($this->hasControl)
            return $this->owner->canUser('createAgentRelationWithControl', $user) && $agent_control;
        else
            return $this->owner->canUser('createAgentRelation', $user) && $agent_control;
    }

    protected function canUserRemove($user){
        $app = App::i();

        $agent_control = $app->isWorkflowEnabled() && $this->agent->canUser('@control', $user);

        if($user->id == $this->agent->getOwnerUser()->id)
            return true;

        else if($this->hasControl)
            return $this->owner->canUser('removeAgentRelationWithControl', $user) || $agent_control;

        else
            return $this->owner->canUser('removeAgentRelation', $user) || $agent_control;
    }

    protected function canUserChangeControl($user){
        if($this->hasControl)
            return $this->owner->canUser('removeAgentRelationWithControl', $user);
        else
            return $this->owner->canUser('createAgentRelationWithControl', $user);
    }

    public function _setTarget(\MapasCulturais\Entity $target){
        $this->objectId = $target->id;
    }

    function save($flush = false) {
        try{
            parent::save($flush);
            
            if($this->owner->usesPermissionCache()){
                $this->owner->deleteUsersWithControlCache();
                $this->owner->enqueueToPCacheRecreation();
            }
        }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
           if(!App::i()->isWorkflowEnabled())
               throw $e;

           $app = App::i();
           $app->disableAccessControl();
           $this->status = self::STATUS_PENDING;
           parent::save($flush);
           $app->enableAccessControl();

           $request = new RequestAgentRelation;
           $request->agentRelation = $this;
           $request->save(true);

           throw new \MapasCulturais\Exceptions\WorkflowRequest([$request]);

        }
    }

    function delete($flush = false) {
        $this->checkPermission('remove');
        // ($originType, $originId, $destinationType, $destinationId, $metadata)
        $ruid = RequestAgentRelation::generateRequestUid($this->owner->getClassName(), $this->owner->id, $this->agent->getClassName(), $this->agent->id, ['class' => $this->getClassName(), 'relationId' => $this->id]);
        $requests = App::i()->repo('RequestAgentRelation')->findBy(['requestUid' => $ruid]);
        foreach($requests as $r)
            $r->delete($flush);

        parent::delete($flush);        

        if($this->owner->usesPermissionCache()){
            $this->owner->deleteUsersWithControlCache();
            $this->owner->enqueueToPCacheRecreation();
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
