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
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;

    public function save($flush = false) {
        $old_relations = $this->repo()->findBy(['group' => $this->group, 'owner' => $this->owner]);
        foreach($old_relations as $rel){
            if(!$this->equals($rel)){
                $rel->delete($flush);
            }
        }
        parent::save($flush);
    }
}