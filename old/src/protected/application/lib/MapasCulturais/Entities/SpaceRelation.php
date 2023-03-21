<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * SpaceRelation
 *
 *
 * @property-read int $id The Id of the relation.
 *
 * @todo http://thoughtsofthree.com/2011/04/defining-discriminator-maps-at-child-level-in-doctrine-2-0/
 *
 * @ORM\Table(name="space_relation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Registration"  = "\MapasCulturais\Entities\RegistrationSpaceRelation"
   })
 */
abstract class SpaceRelation extends \MapasCulturais\Entity
{
    const STATUS_PENDING = -5;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="space_relation_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
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
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="space_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $space;

    function jsonSerialize() {
        $result = parent::jsonSerialize();
        $result['owner'] = $this->owner->simplify('className,id,name,terms,avatar,singleUrl');
        $result['space'] = $this->space->simplify('id,name,type,terms,avatar,singleUrl');

        return $result;
    }

    protected function canUserCreate($user){
        $app = App::i();

        $space_control = !$app->isWorkflowEnabled() || $this->space->canUser('@control', $user);

        return $this->owner->canUser('createSpaceRelation', $user) && $space_control;

    }

    protected function canUserRemove($user){
        $app = App::i();

        $space_control = $app->isWorkflowEnabled() && $this->space->canUser('@control', $user);

        if($user->id == $this->space->getOwnerUser()->id)
            return true;

        else
            return $this->owner->canUser('removeSpaceRelation', $user) || $space_control;
    }

    public function _setTarget(\MapasCulturais\Entity $target){
        $this->objectId = $target->id;
    }

    function save($flush = false) {
        try{
            parent::save($flush);
            
            if($this->owner->usesPermissionCache()){
                $this->owner->deleteUsersWithControlCache();
            }
        }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
           if(!App::i()->isWorkflowEnabled())
               throw $e;

           $app = App::i();
           $app->disableAccessControl();
           $this->status = self::STATUS_PENDING;
           parent::save($flush);
           $app->enableAccessControl();

           $request = new RequestSpaceRelation;
           $request->spaceRelation = $this;
           $request->save(true);

           throw new \MapasCulturais\Exceptions\WorkflowRequest([$request]);

        }
    }

    function delete($flush = false) {
        $this->checkPermission('remove');
        
        $ruid = RequestSpaceRelation::generateRequestUid($this->owner->getClassName(), $this->owner->id, $this->space->getClassName(), $this->space->id, ['class' => $this->getClassName(), 'relationId' => $this->id]);
        $requests = App::i()->repo('RequestSpaceRelation')->findBy(['requestUid' => $ruid]);
        foreach($requests as $r)
            $r->delete($flush);

        parent::delete($flush);
    }
}
