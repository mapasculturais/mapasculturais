<?php

namespace MapasCulturais\Entities;

use MapasCulturais;
use MapasCulturais\i;
use MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * RegistrationMeta
 *
 * @property-read string $result
 * 
 * @property integer $id
 * @property mexed $result
 * @property object $evaluationData
 * @property Registration $registration
 * @property User $user
 * @property integer $status
 *
 * @ORM\Table(name="registration_evaluation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\RegistrationEvaluation")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationEvaluation extends \MapasCulturais\Entity {
    use Traits\EntityRevision;

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
     * @var string|object
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
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    protected $status = self::STATUS_DRAFT;

    function save($flush = false){
        parent::save($flush);
        $app = App::i();
        $opportunity = $this->registration->opportunity;
        
        // cache utilizado pelo endpoint findEvaluations
        $app->mscache->delete("api:opportunity:{$opportunity->id}:evaluations");
    }
    
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

    public function getStatusString(){
        switch ($this->status){
            case self::STATUS_DRAFT:
                return i::__('Rascunho');

            case self::STATUS_EVALUATED:
                return i::__('Avaliado');

            case self::STATUS_SENT:
                return i::__('Enviado');

        }
    }
    
    protected function genericPermissionVerification($user) {
        return $this->registration->opportunity->evaluationMethodConfiguration->canUser('@control', $user) && $this->user->profile->canUser('@control', $user);
    }
    
    protected function canUserModify($user) {
        if($user->is('guest')){
            return false;
        }

        if($this->registration->opportunity->publishedRegistrations){
            return false;
        }

        if($this->registration->opportunity->canUser('@control', $user)){
            return true;
        }

        if($this->registration->canUser('evaluate', $user) && $this->user->equals($user) && $this->status < self::STATUS_SENT){
            return true;
        }

        return false;
    }

    protected function canUserView($user) {
        if($user->is('guest')){
            return false;
        }

        if($user->profile->canUser('@control')){
            return true;
        }
        
        if($this->registration->opportunity->canUser('@control', $user)){
            return true;
        }

        if($this->registration->canUser('evaluate', $user) && $this->user->equals($user) && $this->status < self::STATUS_SENT){
            return true;
        }

        return false;
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
    public function postPersist($args = null){
        parent::postPersist($args);
        
        $this->registration->consolidateResult(true, $this);
    }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){
        parent::postRemove($args);
        
        $this->registration->consolidateResult(true, $this);
    }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){
        parent::postUpdate($args);
        
        $this->registration->consolidateResult(true, $this);
    }

    public function getResult() {
        return $this->result;
    }
}
