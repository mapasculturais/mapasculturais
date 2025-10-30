<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property Registration $owner
 */
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class RegistrationAgentRelation extends AgentRelation{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Registration")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
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