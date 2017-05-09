<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * SealRelation
 *
 *
 * @property-read int $id The Id of the relation.
 *
 * @todo http://thoughtsofthree.com/2011/04/defining-discriminator-maps-at-child-level-in-doctrine-2-0/
 *
 * @ORM\Table(name="seal_relation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Project"       = "\MapasCulturais\Entities\ProjectSealRelation",
        "MapasCulturais\Entities\Event"         = "\MapasCulturais\Entities\EventSealRelation",
        "MapasCulturais\Entities\Agent"         = "\MapasCulturais\Entities\AgentSealRelation",
        "MapasCulturais\Entities\Space"         = "\MapasCulturais\Entities\SpaceSealRelation"
   })
 */
abstract class SealRelation extends \MapasCulturais\Entity
{
    const STATUS_PENDING = -5;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seal_relation_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * A entidade que recebe o selo
     * 
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

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
     * @var \MapasCulturais\Entities\Seal
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Seal", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seal_id", referencedColumnName="id")
     * })
     */
    protected $seal;

    /**
     * O agente que está aplicando o selo (que não necessariamente é o dono do selo, pode ser um agente com permissão
     * ou o dono de um projeto que aplica o selo quando a inscrição é selecionada)
     * 
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $agent;

    /**
     * Gerada automaticamente no metodo save() com o profile do usuario logado.
     * 
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * })
     */
    protected $owner_relation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validate_date", type="date", nullable=true)
     */
    protected $validateDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="renovation_request", type="boolean", nullable=false)
     */
    protected $renovation_request;
    
    function setSeal(Seal $seal){
        if($this->isNew()){
            $this->seal = $seal;

            $period = new \DateInterval("P" . ((string)$seal->validPeriod) . "M");
            $this->validateDate = clone $this->createTimestamp;
            $this->validateDate->add($period);
            
        } else {
            throw new \Exception();
        }
    }

    function jsonSerialize() {
        $result = parent::jsonSerialize();
        $result['owner'] = $this->owner->simplify('className,id,name,avatar,singleUrl');
        $result['seal'] = $this->seal->simplify('id,name,avatar,singleUrl,validateDate');

        return $result;
    }

    protected function canUserCreate($user){
        $app = App::i();

        $can = !$app->isWorkflowEnabled() || $this->seal->canUser('@control', $user);

        return $this->owner->canUser('createSealRelation', $user) && $can;
    }

    protected function canUserRemove($user){
        $app = App::i();

        $can = !$app->isWorkflowEnabled() || $this->seal->canUser('@control', $user);

        return $this->owner->canUser('removeSealRelation', $user) || $can;
    }
    
    protected function canUserPrint($user) {
        return $this->owner->canUser('@control', $user) || $this->seal->canUser('@control', $user);
    }
    
    protected function isExpired() {
        if($this->seal->validPeriod > 0) {
            
            $today = new \DateTime();
            $expirationDate = $this->validateDate;
            return $expirationDate < $today;
            
        } else {
            return false;
        }
    }

    public function save($flush = false) {
        $app = App::i();
        try {
            if($this->owner instanceof Agent){
                $this->owner_relation = $this->owner;
            } else {
                $this->owner_relation = $this->owner->owner;
            }
            parent::save($flush);
            
        } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
            if (!App::i()->isWorkflowEnabled())
                throw $e;

            $app = App::i();
            $app->disableAccessControl();
            $this->status = self::STATUS_PENDING;
            
            
            
            
            
            
            
            parent::save($flush);
            $app->enableAccessControl();

            $request = new RequestSealRelation;
            $request->setSealRelation($this);
            $request->save(true);

            throw new \MapasCulturais\Exceptions\WorkflowRequest([$request]);
        }
    }

    function delete($flush = false) {
        $this->checkPermission('remove');
        // ($originType, $originId, $destinationType, $destinationId, $metadata)
        $ruid = RequestSealRelation::generateRequestUid($this->owner->getClassName(), $this->owner->id, $this->seal->getClassName(), $this->seal->id, ['class' => $this->getClassName(), 'relationId' => $this->id]);
        $requests = App::i()->repo('RequestSealRelation')->findBy(['requestUid' => $ruid]);
        foreach($requests as $r)
            $r->delete($flush);

        parent::delete($flush);
    }
}
