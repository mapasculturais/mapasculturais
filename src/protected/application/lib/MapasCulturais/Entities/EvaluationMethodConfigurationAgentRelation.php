<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EvaluationMethodConfigurationAgentRelation extends AgentRelation {
    const STATUS_SENT = 10;

    /**
     * @var \MapasCulturais\Entities\EvaluationMethodConfiguration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EvaluationMethodConfiguration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;
    
    function save($flush = false) {
        parent::save($flush);
        $this->owner->opportunity->addToRecreatePermissionsCacheList();
    }
    
    function delete($flush = false) {
        $evaluations = \MapasCulturais\App::i()->repo('RegistrationEvaluation')->findByOpportunityAndUser($this->owner->opportunity, $this->agent->user);
        foreach($evaluations as $eval){
            $eval->delete();
        }
        parent::delete($flush);
        $this->owner->opportunity->addToRecreatePermissionsCacheList();
    }
    
}
