<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Opportunity
 *
 * @property \MapasCulturais\Entities\EvaluationMethodConfiguration $evaluationMethodConfiguration
 * @property \MapasCulturais\Entity $ownerEntity
 *
 * @ORM\Table(name="opportunity", indexes={
 *      @ORM\Index(name="opportunity_entity_idx", columns={"object_type", "object_id"}),
 *      @ORM\Index(name="opportunity_parent_idx", columns={"parent_id"}),
 *      @ORM\Index(name="opportunity_owner_idx", columns={"agent_id"}),
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Opportunity")
 * @ORM\HasLifecycleCallbacks
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Project"       = "\MapasCulturais\Entities\ProjectOpportunity",
        "MapasCulturais\Entities\Event"         = "\MapasCulturais\Entities\EventOpportunity",
        "MapasCulturais\Entities\Agent"         = "\MapasCulturais\Entities\AgentOpportunity",
        "MapasCulturais\Entities\Space"         = "\MapasCulturais\Entities\SpaceOpportunity",
   })
 */
abstract class Opportunity extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityMetaLists,
        Traits\EntityTaxonomies,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityNested,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityArchive;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="opportunity_id_seq", allocationSize=1, initialValue=1)
     *
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=true)
     */
    protected $_type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_from", type="datetime", nullable=true)
     */
    protected $registrationFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_to", type="datetime", nullable=true)
     */
    protected $registrationTo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published_registrations", type="boolean", nullable=false)
     */
    protected $publishedRegistrations = false;

    /**
     * @var array
     *
     * @ORM\Column(name="registration_categories", type="json_array", nullable=true)
     */
    protected $registrationCategories = [];

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
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var \MapasCulturais\Entities\Opportunity[] Children opportunities
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Opportunity", mappedBy="parent", fetch="LAZY", cascade={"remove"})
     */
    protected $_children;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\EvaluationMethodConfiguration
     *
     * @ORM\OneToOne(targetEntity="MapasCulturais\Entities\EvaluationMethodConfiguration", mappedBy="opportunity")
     */
    protected $evaluationMethodConfiguration;

    /**
     * @var \MapasCulturais\Entities\RegistrationFileConfiguration[] RegistrationFileConfiguration
     *
     * @ORM\OneToMany(targetEntity="\MapasCulturais\Entities\RegistrationFileConfiguration", mappedBy="owner", fetch="LAZY")
     */
    public $registrationFileConfigurations;

    /**
     * @var \MapasCulturais\Entities\RegistrationFieldConfiguration[] RegistrationFieldConfiguration
     *
     * @ORM\OneToMany(targetEntity="\MapasCulturais\Entities\RegistrationFieldConfiguration", mappedBy="owner", fetch="LAZY")
     */
    public $registrationFieldConfigurations;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\OpportunityFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\OpportunityAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;


    /**
     * @var \MapasCulturais\Entities\OpportunityTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityTermRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\OpportunitySealRelation[] OpportunitySealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunitySealRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__sealRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityPermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**
     * @var integer
     *
     * @ORM\Column(name="subsite_id", type="integer", nullable=true)
     */
    protected $_subsiteId;
    
    abstract function getSpecializedClassName();

    /**
     * Returns the Evaluation Method Definition Object
     * @return \MapasCulturais\Definitions\EvaluationMethod
     */
    public function getEvaluationMethodDefinition() {
        return $this->evaluationMethodConfiguration->getDefinition();
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        return $this->evaluationMethodConfiguration->getEvaluationMethod();
    }

    function setEvaluationMethodConfiguration(EvaluationMethodConfiguration $eval, $cascade = true){
        $this->evaluationMethodConfiguration = $eval;

        if($cascade){
            $eval->setOpportunity($this, false);
        }
    }
    
    function getEvaluationCommittee($return_relation = true){
        $result = $this->evaluationMethodConfiguration->getAgentRelations(null, true);
        
        if(!$return_relation) {
            $result = array_map(function($r){ return $r->agent; }, $result);
        }
        
        return $result;
    }

    /*
     * @TODO: renomear esta funcao para um nome mais adequado.
     */
    function getEvaluations($include_empty = false){
        // @TODO: melhorar performance. talvez utilizando a ApiQuery na entidade RegistrationEvaluation ?
        $committee = $this->getEvaluationCommittee(false);
        
        $registrations = $this->getSentRegistrations();
        
        $result = [];
        
        foreach($registrations as $reg){
            foreach($committee as $agent){
                $user = $agent->getOwnerUser();
                if($reg->canUser('viewUserEvaluation', $user)){
                    $evaluation = $reg->getUserEvaluation($user);
                    if($evaluation || $include_empty){
                        $item = [
                            'valuer' => $agent->simplify('id,name,singleUrl'),
                            'evaluation' => $evaluation,
                            'registration' => $reg->simplify('id,number,category,singleUrl,owner,consolidatedResult')
                        ];
                        
                        $result[] = $item;
                    }
                }
            }
        }
        
        return $result;
    }

    public function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Oportunidades');
        else
            return \MapasCulturais\i::__('Oportunidade');
    }

    static function getValidations() {
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome da oportunidade é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A introdução é obrigatória'),
            ],
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo da oportunidade é obrigatório'),
            ],
            'registrationFrom' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '!empty($this->registrationTo)' => \MapasCulturais\i::__('Data final obrigatória caso data inicial preenchida')
            ],
            'registrationTo' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '$this->validateRegistrationDates()' => \MapasCulturais\i::__('A data final das inscrições deve ser maior ou igual a data inicial')
            ]
        ];
    }

    static function getClassName() {
        return get_class();
    }

    function getExtraPermissionCacheUsers(){
        $users = [];
        if($this->publishedRegistrations) {
            $registrations = App::i()->repo('Registration')->findBy(['opportunity' => $this, 'status' => Registration::STATUS_APPROVED]);
            $r = new Registration;
            foreach($registrations as $r){
                $users = array_merge($users, $r->getUsersWithControl());
            }
        }
        
        if($this->evaluationMethodConfiguration){
            $users = array_merge($users, $this->evaluationMethodConfiguration->getUsersWithControl());
        }

        return $users;
    }
    
    function getExtraEntitiesToRecreatePermissionCache(){
        $entities = $this->getAllRegistrations();
        if($this->parent){
            $entities[] = $this->parent;
        }
        return $entities;
    }

    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }

    function getAllRegistrations(){
        // ============ IMPORTANTE =============//
        // @TODO implementar findSentByOpportunity no repositório de inscrições
        $registrations = App::i()->repo('Registration')->findBy(['opportunity' => $this]);

        return $registrations;
    }
    
    function getSendEvaluationsUrl(){
        return $this->controller->createUrl('sendEvaluations', [$this->id]);
    }

    /**
     * Returns sent registrations
     *
     * @return \MapasCulturais\Entities\Registration[]
     */
    function getSentRegistrations(){
        $registrations = $this->getAllRegistrations();

        $result = [];
        foreach($registrations as $re){
            if($re->status > 0)
                $result[] = $re;
        }
        return $result;
    }

    /**
     * Returns registrations by status
     * @param $status
     * @return \MapasCulturais\Entities\Registration[]
     */
    function getRegistrationsByStatus($status){
        return array_filter($this->getAllRegistrations(), function($reg) use ($status) {
            return $reg->status === $status;
        });
    }



    function setRegistrationFrom($date){
        if($date instanceof \DateTime){
            $this->registrationFrom = $date;
        }elseif($date){
            $this->registrationFrom = new \DateTime($date);
            $this->registrationFrom->setTime(0,0,0);
        }else{
            $this->registrationFrom = null;
        }
    }

    function setRegistrationTo($date){
        if($date instanceof \DateTime){
            $this->registrationTo = $date;
        }elseif($date){
            $this->registrationTo = \DateTime::createFromFormat('Y-m-d H:i', $date);
        }else{
            $this->registrationTo = null;
        }
    }

    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateRegistrationDates() {
        if($this->registrationFrom && $this->registrationTo){
            return $this->registrationFrom <= $this->registrationTo;

        }elseif($this->registrationFrom || $this->registrationTo){
            return false;

        }else{
            return true;
        }
    }

    function isRegistrationOpen(){
        $cdate = new \DateTime;
        return $cdate >= $this->registrationFrom && $cdate <= $this->registrationTo;
    }

    function setRegistrationCategories($value){
        $new_value = $value;
        if(is_string($value) && trim($value)){
            $cats = [];
            foreach(explode("\n", trim($value)) as $opt){
                $opt = trim($opt);
                if($opt && !in_array($opt, $cats)){
                    $cats[] = $opt;
                }
            }
            $new_value = $cats;
        }

        if($new_value != $this->registrationCategories){
            $this->checkPermission('modifyRegistrationFields');
        }

        $this->registrationCategories = $new_value;
    }

    function publishRegistrations(){
        $this->checkPermission('publishRegistrations');

        $this->publishedRegistrations = true;

        // atribui os selos as inscrições selecionadas
        $app = App::i();
        $registrations = $app->repo('Registration')->findBy(array('opportunity' => $this, 'status' => Registration::STATUS_APPROVED));

        foreach ($registrations as $registration) {
            $registration->setAgentsSealRelation();
        }

        $app->enqueueEntityToPCacheRecreation($this);

        $this->save(true);
    }

    function sendUserEvaluations($user = null){
        $app = App::i();

        if(is_null($user)){
            $user = $app->user;
        }

        $this->checkPermission('sendUserEvaluations', $user);

        $evaluations = $app->repo('RegistrationEvaluation')->findByOpportunityAndUser($this, $user);

        $app->disableAccessControl();
        
        foreach($evaluations as $evaluation){
            $evaluation->status = RegistrationEvaluation::STATUS_SENT;
            $evaluation->save(true);
        }

        $relation = $this->evaluationMethodConfiguration->getUserRelation($user);
        $relation->status = EvaluationMethodConfigurationAgentRelation::STATUS_SENT;
        $relation->save(true);

        $app->em->flush();
        
        $app->enableAccessControl();
    }

    function useRegistrationAgentRelation(\MapasCulturais\Definitions\RegistrationAgentRelation $def){
        $meta_name = $def->getMetadataName();
        return $this->$meta_name != 'dontUse';
    }


    function getUsedAgentRelations(){
        $app = App::i();
        $r = [];
        foreach($app->getRegistrationAgentsDefinitions() as $def)
            if($this->useRegistrationAgentRelation($def))
                $r[] = $def;
        return $r;
    }

    function isRegistrationFieldsLocked(){
        $app = App::i();
        $cache_id = $this . ':' . __METHOD__;
        if($app->rcache->contains($cache_id)){
            return $app->rcache->fetch($cache_id);
        }else{
            $num = $app->repo('Registration')->countByOpportunity($this, true);
            $locked = $num > 0;

            $app->rcache->save($cache_id, $locked);
            return $locked;
        }
    }
    
    function isUserEvaluationsSent($user = null){
        $relation = $this->evaluationMethodConfiguration->getUserRelation($user);
        
        if(!$relation){
            return false;
        }
        
        return $relation->status === EvaluationMethodConfigurationAgentRelation::STATUS_SENT;
    }

    protected function genericPermissionVerification($user){
        if($this->ownerEntity->canUser('@control', $user)){
            return true;
        }

        return parent::genericPermissionVerification($user);
    }

    protected function canUserModifyRegistrationFields($user){
        if($user->is('guest')){
            return false;
        }

        if ($this->isUserAdmin($user)) {
            return true;
        }

        if($this->isRegistrationFieldsLocked()){
            return false;
        }

        return $this->canUser('modify', $user);

    }

    protected function canUserPublishRegistrations($user){
        if($user->is('guest')){
            return false;
        }

        if($this->registrationTo >= new \DateTime){
            return false;
        }

        return $this->canUser('@control', $user);
    }


    protected function canUserRegister($user){
        if($user->is('guest'))
            return false;

        return $this->isRegistrationOpen();
    }

    protected function canUserSendUserEvaluations($user){
        $can_evaluate = $this->canUserEvaluateRegistrations($user);
        
        $evaluations_ok = true;
        foreach($this->getSentRegistrations() as $reg){
            if($reg->canUser('evaluate')){
                $evaluation = $reg->getUserEvaluation($user);
                if(is_null($evaluation) || $evaluation->status != RegistrationEvaluation::STATUS_EVALUATED){
                    $evaluations_ok = false;
                    break;
                }
            }
        }
        
        return $can_evaluate && $evaluations_ok;
    }

    protected function canUserEvaluateRegistrations($user){
        if($user->is('guest')){
            return false;
        }

        if($this->canUser('@control')) {
            return true;
        }

        if($this->registrationTo > (new \DateTime) || $this->publishedRegistrations){
            return false;
        }

        $relation = $this->evaluationMethodConfiguration->getUserRelation($user);

        return $relation && $relation->status === AgentRelation::STATUS_ENABLED;
    }

    protected function canUserReopenValuerEvaluations($user){
        if(!$this->canUser('@controll', $user)){
            return false;
        }

        if($this->publishedRegistrations){
            return false;
        }

        return true;
    }

    protected function canUserViewEvaluations($user){
        return $this->evaluationMethodConfiguration->canUser('@control');
    }

    /** @ORM\PreRemove */
    public function unlinkEvents(){
        foreach($this->events as $event)
            $event->opportunity = null;
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
