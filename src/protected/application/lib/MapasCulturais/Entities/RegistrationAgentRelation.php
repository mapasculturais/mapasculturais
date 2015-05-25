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
    
    /**
     * @var \MapasCulturais\Entities\RegistrationAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;

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