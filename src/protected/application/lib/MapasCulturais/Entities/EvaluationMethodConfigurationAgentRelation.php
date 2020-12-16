<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relação que define um avaliador de uma oportunidade
 * 
 * @property \MapasCulturais\Entities\EvaluationMethodConfiguration $owner
 * @property \MapasCulturais\Entities\Agent $agent
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EvaluationMethodConfigurationAgentRelation extends AgentRelation {
    const STATUS_SENT = 10;
    const STATUS_DISABLED = 8;

    /**
     * @var \MapasCulturais\Entities\EvaluationMethodConfiguration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EvaluationMethodConfiguration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;
    
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

    function disable($flush = true){
        $this->owner->opportunity->checkPermission('@control');

        $this->status = self::STATUS_DISABLED;

        $this->save($flush);
    }

    function enable($flush = true){
        $this->owner->opportunity->checkPermission('@control');

        $this->status = self::STATUS_ENABLED;

        $this->save($flush);
    }
}
