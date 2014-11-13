<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RegistrationAgentRelation extends AgentRelation{

    /**
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    public function save($flush = false) {
        $old_relation = $this->repo()->findOneBy(array('group' => $this->group));
        if($old_relation && !$this->equals($old_relation)){
            $old_relation->delete($flush);
        }
        parent::save($flush);
    }
}