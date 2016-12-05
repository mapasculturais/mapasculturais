<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Project")
 * @ORM\HasLifecycleCallbacks
 */
class Project extends \MapasCulturais\Entity
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
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="project_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
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
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    protected $longDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

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
     * @ORM\Column(name="use_registrations", type="boolean", nullable=false)
     */
    protected $useRegistrations = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published_registrations", type="boolean", nullable=false)
     */
    protected $publishedRegistrations = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var array
     *
     * @ORM\Column(name="registration_categories", type="json_array", nullable=true)
     */
    protected $registrationCategories = [];



    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var \MapasCulturais\Entities\Project[] Chield projects
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Project", mappedBy="parent", fetch="LAZY", cascade={"remove"})
     */
    protected $_children;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\Event[] Event
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Event", mappedBy="project", fetch="LAZY", cascade={"persist"})
     */
    protected $_events;

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
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\ProjectFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\ProjectAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;


    /**
     * @var \MapasCulturais\Entities\ProjectTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectTermRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\ProjectSealRelation[] ProjectSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectSealRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__sealRelations;
    
    public function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Projetos');
        else
            return \MapasCulturais\i::__('Projeto');
    }
    
    static function getValidations() {
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do projeto é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo do projeto é obrigatório'),
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
    
    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }

    function getAllRegistrations(){
        // ============ IMPORTANTE =============//
        // @TODO implementar findSentByProject no repositório de inscrições
        $registrations = App::i()->repo('Registration')->findBy(['project' => $this]);

        return $registrations;
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

        $this->save(true);
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
            $num = $app->repo('Registration')->countByProject($this, true);
            $locked = $num > 0;

            $app->rcache->save($cache_id, $locked);
            return $locked;
        }
    }

    protected function canUserCreateEvents($user) {
        if ($user->is('guest')) {
            return false;
        }

        if ($user->is('admin')) {
            return true;
        }

        if ($this->canUser('@control')) {
            return true;
        }

        return false;
    }

    protected function canUserRequestEventRelation($user) {
        if ($user->is('guest')) {
            return false;
        }

        if ($user->is('admin')) {
            return true;
        }

        if ($this->canUser('createEvents')) {
            return true;
        }

        foreach ($this->getAgentRelations() as $relation) {
            if ($relation->agent->userId == $user->id) {
                return true;
            }
        }

        if ($this->publishedRegistrations) {
            foreach ($this->getSentRegistrations() as $resgistration) {
                if ($resgistration->status === Registration::STATUS_APPROVED) {
                    if ($resgistration->canUser('@control', $user)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function canUserModifyRegistrationFields($user){
        if($user->is('guest')){
            return false;
        }

        if($user->is('admin')){
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


    protected function canUserRegister($user = null){
        if($user->is('guest'))
            return false;

        return $this->isRegistrationOpen();
    }

    /** @ORM\PreRemove */
    public function unlinkEvents(){
        foreach($this->events as $event)
            $event->project = null;
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
