<?php

namespace MapasCulturais\Entities;

use MapasCulturais;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * RegistrationMeta
 *
 * @property-read string $result
 *
 * @ORM\Table(name="registration_evaluation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\RegistrationEvaluation")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationEvaluation extends \MapasCulturais\Entity {
    const STATUS_EVALUATED = self::STATUS_ENABLED;
    const STATUS_SENT = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_evaluation_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="string", nullable=true)
     */
    protected $result;

    /**
     * @var string
     *
     * @ORM\Column(name="evaluation_data", type="json_array", nullable=false)
     */
    protected $evaluationData = [];

    /**
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $registration;

    /**
     * @var \MapasCulturais\Entities\USer
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    protected $status = self::STATUS_DRAFT;

    function getEvaluationData(){
        return (object) $this->evaluationData;
    }

    function setEvaluationData($data){
        $this->evaluationData = (object) $data;

        $evaluation_method = $this->getEvaluationMethod();

        $this->result = $evaluation_method->getEvaluationResult($this);
    }

    /**
     * Returns the Evaluation Method Definition Object
     * @return \MapasCulturais\Definitions\EvaluationMethod
     */
    public function getEvaluationMethodDefinition() {
        return $this->registration->getEvaluationMethodDefinition();
    }

    /**
     * Returns the Evaluation Method Configuration
     * @return \MapasCulturais\Definitions\EvaluationMethodConfiguration
     */
    public function getEvaluationMethodConfiguration() {
        return $this->registration->evaluationMethodConfiguration;
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        return $this->registration->getEvaluationMethod();
    }

    public function getResultString(){
        if($this->status === self::STATUS_DRAFT){
            return '';
        }
        
        $evaluation_method = $this->getEvaluationMethod();

        return $evaluation_method->evaluationToString($this);
    }
    
    protected function genericPermissionVerification($user) {
        return $this->registration->opportunity->evaluationMethodConfiguration->canUser('@control', $user) && $this->user->profile->canUser('@control', $user);
    }
    
    protected function canUserModify($user) {
        return $this->registration->canUser('evaluate', $user) && $this->status < self::STATUS_SENT;
    }

    public function jsonSerialize() {
        $result = parent::jsonSerialize();

        $result['resultString'] = $this->getResultString();
        $result['user'] = $this->user->id;
        $result['agent'] = $this->user->profile->simplify('id,name,singleUrl');
        $result['registration'] = $this->registration->simplify('id,number,singleUrl');
        $result['singleUrl'] = $this->getSingleUrl();

        return $result;
    }

    function getSingleUrl() {
        return App::i()->createUrl('registration', 'view', [$this->registration->id, 'uid' => $this->user->id]);
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
