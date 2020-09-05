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
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;
    
    function save($flush = false) {
        parent::save($flush);
        $this->owner->opportunity->enqueueToPCacheRecreation();
    }
    
    function delete($flush = false) {
        $evaluations = \MapasCulturais\App::i()->repo('RegistrationEvaluation')->findByOpportunityAndUser($this->owner->opportunity, $this->agent->user);
        foreach($evaluations as $eval){
            $eval->delete($flush);
        }
        $this->owner->opportunity->enqueueToPCacheRecreation();
        parent::delete($flush);
    }
    
    function reopen($flush = true){
        $this->owner->opportunity->checkPermission('reopenValuerEvaluations');

        $this->status = self::STATUS_ENABLED;

        $this->save($flush);
    }
}
