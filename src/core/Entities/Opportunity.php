<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entity;
use MapasCulturais\ApiQuery;
use MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\Definitions\Metadata as MetadataDefinition;
use MapasCulturais\Exceptions\BadRequest;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;
use MapasCulturais\Utils;

/**
 * Opportunity
 *
 * @property-read int $id
 * @property-read int $status
 * @property-read \DateTime $createTimestamp
 * @property-read \DateTime $updateTimestamp
 * @property-read array $summary
 * @property-read boolean $autoPublish
 * @property \DateTime $publishTimestamp
 * @property-read boolean $publishedRegistrations
 * @property-read int $totalRegistrations
 *
 *
 * @property string $name
 * @property string $shortDescription
 * @property \DateTime $registrationFrom
 * @property \DateTime $registrationTo
 * @property array $registrationCategories
 * @property array $registrationProponentTypes
 * @property array $registrationRanges
 * @property self $parent
 * @property Agent $owner
 *
 *
 * @property EvaluationMethodConfiguration $evaluationMethodConfiguration
 * @property RegistrationStep[] $registrationSteps
 * @property RegistrationFileConfiguration[] $registrationFileConfigurations
 * @property RegistrationFieldConfiguration[] $registrationFieldConfigurations
 * @property \MapasCulturais\Entity $ownerEntity
 *
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
        Traits\EntityRevision,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityNested,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityLock,
        Traits\EntityArchive{
            Traits\EntityNested::setParent as nestedSetParent;
            Traits\EntityAgentRelation::canUserCreateAgentRelationWithControl as __canUserCreateAgentRelationWithControl;
            Traits\EntityAgentRelation::canUserRemoveAgentRelationWithControl as __canUserRemoveAgentRelationWithControl;
        }

    protected $__enableMagicGetterHook = true;
    protected $__enableMagicSetterHook = true;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="opportunity_id_seq", allocationSize=1, initialValue=1)
     *
     */
    public $id;

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
     * @ORM\Column(name="short_description", type="text", nullable=false)
     */
    protected $shortDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_from", type="datetime", nullable=false)
     */
    protected $registrationFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_to", type="datetime", nullable=false)
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
     * @ORM\Column(name="registration_categories", type="json", nullable=true)
     */
    protected array $registrationCategories = [];

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationStep", mappedBy="opportunity", cascade={"remove"}, orphanRemoval=true)
     */
    protected $registrationSteps;

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
     * @var \DateTime
     *
     * @ORM\Column(name="publish_timestamp", type="datetime", nullable=true)
     */
    protected $publishTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="auto_publish", type="boolean", options={"default" : false})
     */
    protected $autoPublish = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

     /**
     * @var string
     *
     * @ORM\Column(name="registration_proponent_types", type="json", nullable=false)
     */
    protected array $registrationProponentTypes = [];

    /**
     * @var string
     *
     * @ORM\Column(name="registration_ranges", type="json", nullable=true)
     */
    protected array $registrationRanges = [];

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
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
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
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
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\OpportunityFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityFile", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\OpportunityAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityAgentRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;


    /**
     * @var \MapasCulturais\Entities\OpportunityTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityTermRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\OpportunitySealRelation[] OpportunitySealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunitySealRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__sealRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\OpportunityPermissionCache", mappedBy="owner", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**
     * @var integer
     *
     * @ORM\Column(name="subsite_id", type="integer", nullable=true)
     */
    protected $_subsiteId;

    /**
    * @var \MapasCulturais\Entities\Subsite
    *
    * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Subsite")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
    * })
    */
   protected $subsite;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    protected $longDescription;

     /**
     * @var object
     *
     * @ORM\Column(name="avaliable_evaluation_fields", type="json", nullable=true)
     */
    protected $avaliableEvaluationFields = [];

    /**
     * @var dateTime
     *
     * @ORM\Column(name="continuous_flow", type="datetime", nullable=true)
     */
    protected $continuousFlow;
    
    abstract function getSpecializedClassName();

    public static function getPropertiesMetadata($include_column_name = false){
        $app = App::i();
        $result = parent::getPropertiesMetadata($include_column_name);
        $result["registrationProponentTypes"]["type"] = "multiselect";
        $options = [];
        foreach($app->config["registration.proponentTypes"] as $value){
            $options[$value] = $value;
        }
        $result["registrationProponentTypes"]["options"] = $options;
        $result["registrationProponentTypes"]["optionsOrder"] = $app->config["registration.proponentTypes"];

        return $result;
    }

    /**
     *
     * @return RegistrationFileConfiguration[]
     */
    public function getRegistrationFileConfigurations() {
        $app = App::i();

        $result = $app->repo('RegistrationFileConfiguration')->findBy(['owner' => $this]);

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.registrationFileConfigurations", [&$result]);

        return $result;
    }

    /**
     *
     * @return RegistrationFieldConfiguration[]
     */
    public function getRegistrationFieldConfigurations() {
        $app = App::i();

        $result = $app->repo('RegistrationFieldConfiguration')->findBy(['owner' => $this]);

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.registrationFieldConfigurations", [&$result]);

        return $result;
    }

    /**
     * Returns the Evaluation Method Definition Object
     * @return \MapasCulturais\Definitions\EvaluationMethod
     */
    public function getEvaluationMethodDefinition() {
        if ($this->evaluationMethodConfiguration) {
            return $this->evaluationMethodConfiguration->getDefinition();
        } else {
            return null;
        }
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        if ($this->evaluationMethodConfiguration) {
            return $this->evaluationMethodConfiguration->getEvaluationMethod();
        } else {
            return null;
        }
    }

    function setParent($parent = null) {
        $this->nestedSetParent($parent);
        if($parent){
            $this->ownerEntity = $this->getParent()->ownerEntity;
        }else{
            $this->ownerEntity = null;
        }
    }

    function setAvaliableEvaluationFields($value) {
        if(!$value || empty($value)){
            $this->avaliableEvaluationFields = [];
        }else{
            $this->avaliableEvaluationFields = $value;
        }
    }

    function setContinuousFlow($value) {
        if ($value !== null) {
            try {
                $this->continuousFlow = new \DateTime($value);
            } catch (\Exception $e) {
                $this->continuousFlow = null;
            }
        } else {
            $this->continuousFlow = null;
        }
    }
    
    function getEvaluationCommittee($return_relation = true){
        $app = App::i();

        if (!$this->evaluationMethodConfiguration) {
            return [];
        }

        $committee = $this->evaluationMethodConfiguration->getCommittee($return_relation);

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}.evaluationCommittee", [&$committee, $return_relation]);

        return $committee;
    }

    /*
     * @TODO: renomear esta funcao para um nome mais adequado.
     */
    function getEvaluations($include_empty = false){
        $app = App::i();

        // @TODO: melhorar performance. talvez utilizando a ApiQuery na entidade RegistrationEvaluation ?
        $committee = $this->getEvaluationCommittee(false);

        $registrations = $this->getSentRegistrations();

        $evaluations = [];

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

                        $evaluations[] = $item;
                    }
                }
            }
        }

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}.evaluations", [&$evaluations, $include_empty]);

        return $evaluations;
    }

    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return \MapasCulturais\i::__('Oportunidades');
        else
            return \MapasCulturais\i::__('Oportunidade');
    }

    public static function validateShortDescription()
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $validate =  [
                'required' => \MapasCulturais\i::__('A introdução é obrigatória'),
            ];
        }else{
            $validate = [];
        }

        return $validate;
    }

    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome da oportunidade é obrigatório')
            ],
            'shortDescription' => self::validateShortDescription(),
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo da oportunidade é obrigatório'),
            ],
            'registrationFrom' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
            ],
            'registrationTo' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '$this->validateRegistrationDates()' => \MapasCulturais\i::__('A data final das inscrições deve ser maior ou igual a data inicial')
            ],
	        'ownerEntity' => [
		        'required' => \MapasCulturais\i::__('A entidade é obrigatória'),
	        ],
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    static function getClassName() {
        return Opportunity::class;
    }

    function getExtraPermissionCacheUsers(){
        $users = [];

        if($this->evaluationMethodConfiguration){
            $users = array_merge($users, $this->evaluationMethodConfiguration->getUsersWithControl());
        }

        if($this->parent) {
            $users = array_merge($users, $this->parent->getUsersWithControl());
        }

        $users = array_merge($users, $this->ownerEntity->getUsersWithControl());

        return $users;
    }

    function getExtraEntitiesToRecreatePermissionCache(){
        $entities = $this->getAllRegistrations();
        
        return $entities;
    }

    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }

    function getAllRegistrations($status = null){
        // ============ IMPORTANTE =============//
        // @TODO implementar findSentByOpportunity no repositório de inscrições
        $app = App::i();

        if ($status == 'sent') {
            $status_dql = is_null($status) ? '' : 'r.status > 0 AND';
        } else {
            $status_dql = is_null($status) ? '' : "r.status = {$status} AND";
        }

        $query = $app->em->createQuery("
        SELECT
            r
        FROM
            MapasCulturais\\Entities\\Registration r
        WHERE
            $status_dql
            r.opportunity = :opportunity
        ");

        $query->setParameter('opportunity', $this);

        // $registrations = $query->getResult($query::HYDRATE_SIMPLEOBJECT);
        $registrations = $query->getResult();

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
        return $this->getAllRegistrations('sent');
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

    /**
     * Retorna total de inscrições na oportunidade
     * @return integer
     */
    function getTotalRegistrations(){
        $app = App::i();

        $params = ["opp" => $this];

        $query = $app->em->createQuery("
            SELECT
                COUNT(o) AS totalRegistrations
            FROM
                MapasCulturais\\Entities\\Registration o
            WHERE
                o.opportunity = :opp");

        $query->setParameters($params);

        $result = $query->getArrayResult();

        return $result[0]['totalRegistrations'];
    }

    function setRegistrationFrom($date){
        if($date instanceof \DateTime){
            $this->registrationFrom = $date;
        }elseif($date){
            $this->registrationFrom = new \DateTime($date);
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

    function setPublishTimestamp($date){
        if($date instanceof \DateTime){
            $this->publishTimestamp = $date;
        }elseif($date){
            $this->publishTimestamp = new \DateTime($date);
        }else{
            $this->publishTimestamp = null;
        }
    }

    function setOwnerEntity($entity){
    	if (empty($entity)) {
    		return;
	    }

        if ($entity instanceof Entity) {
            $this->ownerEntity = $entity;
        }
        else {
            $app = App::i();

            $ownerEntityClassName = substr($this->getSpecializedClassName(), 24, -11);
            $this->ownerEntity = $app->repo($ownerEntityClassName)->find($entity);
        }
    }

    /**
     * Recria ponteiros entre fases das inscrições
     * @return void
     * @throws PermissionDenied
     */
    public function fixNextPhaseRegistrationIds(): void
    {
        $this->checkPermission('modify');

        $app = App::i();
        $opportunity = $this;
        $conn = $app->em->getConnection();

        if($next_phase = $opportunity->nextPhase) {
            $conn->executeQuery("DELETE FROM registration_meta where key = 'nextPhaseRegistrationId' AND object_id in (SELECT id FROM registration WHERE opportunity_id = {$opportunity->id})");
            $conn->executeQuery("DELETE FROM registration_meta where key = 'previousPhaseRegistrationId' AND object_id in (SELECT id FROM registration WHERE opportunity_id = {$next_phase->id})");

            $_current_phase_registrations = $conn->fetchAll("SELECT id, number FROM registration WHERE opportunity_id = {$opportunity->id}");
            $_next_phase_registrations = $conn->fetchAll("SELECT id, number FROM registration WHERE opportunity_id = {$next_phase->id}");

            $current_phase_registrations = [];
            foreach($_current_phase_registrations as $registration) {
                $current_phase_registrations[$registration['number']] = $registration['id'];
            }

            $next_phase_registrations = [];
            foreach($_next_phase_registrations as $registration) {
                $next_phase_registrations[$registration['number']] = $registration['id'];
            }

            // Corrige o ponteiro para próxima fase
            foreach($next_phase_registrations as $number => $id) {
                if(!isset($current_phase_registrations[$number])) {
                    continue;
                }

                $parms = [
                    'object_id' => $current_phase_registrations[$number],
                    'key' => 'nextPhaseRegistrationId',
                    'value' => $id,
                ];

                $conn->executeQuery("INSERT INTO registration_meta (object_id, key, value, id) VALUES (:object_id, :key, :value, nextval('registration_meta_id_seq'::regclass))", $parms);
                $app->log->debug("Corrigido ponteiro nextPhase para inscrição {$id} ({$number})");
            }

             // Corrige o ponteiro para fase anterior
             foreach($current_phase_registrations as $number => $id) {
                if(!isset($next_phase_registrations[$number])) {
                    continue;
                }

                $parms = [
                    'object_id' => $next_phase_registrations[$number],
                    'key' => 'previousPhaseRegistrationId',
                    'value' => $id,
                ];

                $conn->executeQuery("INSERT INTO registration_meta (object_id, key, value, id) VALUES (:object_id, :key, :value, nextval('registration_meta_id_seq'::regclass))", $parms);
                $app->log->debug("Corrigido ponteiro previousPhase para inscrição {$id} ({$number})");
            }

        }
    }


    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateRegistrationDates() {
        if($this->registrationFrom && $this->registrationTo){
            $shouldValidateRegistrationTo = (!$this->isContinuousFlow) || ($this->isContinuousFlow && $this->hasEndDate);
            if ($shouldValidateRegistrationTo) {
                return $this->registrationFrom <= $this->registrationTo;
            } else {
                return true;
            }
        }elseif($this->registrationFrom || $this->registrationTo){
            return false;

        }else{
            return true;
        }
    }

    /**
     * Indica se as inscrições estão abertas
     * @return bool
     */
    function isRegistrationOpen(){
        $cdate = new \DateTime;
        return $cdate >= $this->registrationFrom && $cdate <= $this->registrationTo;
    }

    function setRegistrationCategories(string|array $categories) {
        $app = App::i();

        $new_categories = $categories;
        if(is_string($categories) && trim($categories)){
            $new_categories = Utils::nl2array($categories);
        }

        $removed_categories = array_diff($this->registrationCategories, $new_categories);
        $errors = [];

        foreach ($removed_categories as $removed_category) {
            $errors[$removed_category] = [];
            if($this->hasRegistrationOfCategory($removed_category)){
                $errors[$removed_category]['registration'] = true;
            }
            if($this->hasFieldOfCategory($removed_category)){
                $errors[$removed_category]['field'] = true;
            }

            if($errors[$removed_category]) {
                $errors[$removed_category]['message'] = '';
                if(isset($errors[$removed_category]['registration']) && isset($errors[$removed_category]['field'])) {
                    $errors[$removed_category]['message'] = sprintf(i::__('A categoria %s está sendo utilizada em campos e em inscrições.'), $removed_category);
                } else if (isset($errors[$removed_category]['registration'])) {
                    $errors[$removed_category]['message'] = sprintf(i::__('A categoria %s está sendo utilizada em inscrições.'), $removed_category);
                } else if (isset($errors[$removed_category]['field'])) {
                    $errors[$removed_category]['message'] = sprintf(i::__('A categoria %s está sendo utilizada em campos.'), $removed_category);
                }

                throw new PermissionDenied($app->user, message: $errors[$removed_category]['message']);

            } else {
                unset($errors[$removed_category]);
            }
        }

        $this->registrationCategories = $new_categories;
    }

    function setRegistrationProponentTypes(string|array $proponent_types) {
        $app = App::i();

        $new_proponent_types = $proponent_types;
        if(is_string($proponent_types) && trim($proponent_types)){
            $new_proponent_types = Utils::nl2array($proponent_types);
        }

        $removed_proponent_types = array_diff($this->registrationProponentTypes, $new_proponent_types);
        $errors = [];
        foreach ($removed_proponent_types as $removed_proponent_type) {
            $errors[$removed_proponent_type] = [];
            if($this->hasRegistrationOfProponentType($removed_proponent_type)){
                $errors[$removed_proponent_type]['registration'] = true;
            }
            if($this->hasFieldOfProponentType($removed_proponent_type)){
                $errors[$removed_proponent_type]['field'] = true;
            }

            if($errors[$removed_proponent_type]) {
                $errors[$removed_proponent_type]['message'] = '';
                if(isset($errors[$removed_proponent_type]['registration']) && isset($errors[$removed_proponent_type]['field'])) {
                    $errors[$removed_proponent_type]['message'] = sprintf(i::__('O tipo de proponente %s está sendo utilizado em campos e em inscrições.'), $removed_proponent_type);
                } else if (isset($errors[$removed_proponent_type]['registration'])) {
                    $errors[$removed_proponent_type]['message'] = sprintf(i::__('O tipo de proponente %s está sendo utilizado em inscrições.'), $removed_proponent_type);
                } else if (isset($errors[$removed_proponent_type]['field'])) {
                    $errors[$removed_proponent_type]['message'] = sprintf(i::__('O tipo de proponente %s está sendo utilizado em campos.'), $removed_proponent_type);
                }

                throw new PermissionDenied($app->user, message: $errors[$removed_proponent_type]['message']);

            } else {
                unset($errors[$removed_proponent_type]);
            }
        }

        $this->registrationProponentTypes = $new_proponent_types;
    }

    function setRegistrationRanges(array $registration_ranges){
        $app = App::i();
        
        $registration_ranges = array_map(function($range) {
            if (isset($range['label'])) {
                $range['label'] = trim($range['label']);
            }
            return $range;
        }, $registration_ranges);

        $new_registration_ranges = $registration_ranges;

        $current_range_labels = array_map(fn ($range) => $range['label'], $this->registrationRanges);
        $new_range_labels = array_map(fn ($range) => $range['label'], $new_registration_ranges);

        $removed_ranges = array_diff($current_range_labels, $new_range_labels);

        $errors = [];
        foreach ($removed_ranges as $removed_range) {
            $errors[$removed_range] = [];
            if($this->hasRegistrationOfRange($removed_range)){
                $errors[$removed_range]['registration'] = true;
            }
            if($this->hasFieldOfRange($removed_range)){
                $errors[$removed_range]['field'] = true;
            }

            if($errors[$removed_range]) {
                $errors[$removed_range]['message'] = '';
                if(isset($errors[$removed_range]['registration']) && isset($errors[$removed_range]['field'])) {
                    $errors[$removed_range]['message'] = sprintf(i::__('A faixa/linha %s está sendo utilizada em campos e em inscrições.'), $removed_range);
                } else if (isset($errors[$removed_range]['registration'])) {
                    $errors[$removed_range]['message'] = sprintf(i::__('A faixa/linha %s está sendo utilizada em inscrições.'), $removed_range);
                } else if (isset($errors[$removed_range]['field'])) {
                    $errors[$removed_range]['message'] = sprintf(i::__('A faixa/linha %s está sendo utilizada em campos.'), $removed_range);
                }
                throw new PermissionDenied($app->user, message: $errors[$removed_range]['message']);

            } else {
                unset($errors[$removed_range]);
            }
        }

        $this->registrationRanges = $new_registration_ranges;
    }

    /**
     * Verifica se existe uma inscrição com o valor especificado para o campo informado.
     *
     * @param string $field O campo a ser verificado (por exemplo, 'proponent_type')
     * @param string $value O valor a ser verificado
     * @return bool Verdadeiro se existir uma inscrição com o valor especificado, falso caso contrário
     *
     */
    protected function hasRegistrationOf(string $field, string $value): bool {
        $app = App::i();

        $registration = $app->repo('Registration')->findOneBy([
            'opportunity' => $this->firstPhase,
            $field => $value,
            'status' => [10,8,3,2,1]
        ]);

        return $registration ? true : false;
    }

    protected function hasFieldOf (string $registration_field, string $value): bool {

        foreach($this->allPhases as $phase) {
            /** @var Opportunity $phase */
            foreach([...$phase->registrationFieldConfigurations, ...$phase->registrationFileConfigurations] as $field) {
                if(is_array($field->$registration_field) && in_array($value, $field->$registration_field)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica se existe uma inscrição com o valor especificado para a categoria.
     *
     * @param string $category A categoria a ser verificada
     * @return bool Verdadeiro se existir uma inscrição com a categoria especificada, falso caso contrário
     */
    public function hasRegistrationOfCategory(string $category): bool {
        return $this->hasRegistrationOf('category', $category);
    }

    /**
     * Verifica se existe uma inscrição com o valor especificado para o tipo de proponente.
     *
     * @param string $proponent_type O tipo de proponente a ser verificado
     * @return bool Verdadeiro se existir uma inscrição com o tipo de proponente especificado, falso caso contrário
     */
    public function hasRegistrationOfProponentType(string $proponent_type): bool {
        return $this->hasRegistrationOf('proponentType', $proponent_type);
    }

    /**
     * Verifica se existe uma inscrição com o valor especificado para o intervalo.
     *
     * @param string $range_label O intervalo a ser verificado
     * @return bool Verdadeiro se existir uma inscrição com o intervalo especificado, falso caso contrário
     */
    public function hasRegistrationOfRange(string $range_label): bool {
        return $this->hasRegistrationOf('range', $range_label);
    }

    /**
     * Verifica se existe um campo com o valor especificado para a categoria.
     *
     * @param string $category A categoria a ser verificada
     * @return bool Verdadeiro se existir um campo com a categoria especificada, falso caso contrário
     */
    public function hasFieldOfCategory(string $category): bool {
        return $this->hasFieldOf('categories', $category);
    }

    /**
     * Verifica se existe um campo com o valor especificado para o tipo de proponente.
     *
     * @param string $proponent_type O tipo de proponente a ser verificado
     * @return bool Verdadeiro se existir um campo com o tipo de proponente especificado, falso caso contrário
     */
    public function hasFieldOfProponentType(string $proponent_type): bool {
        return $this->hasFieldOf('proponentTypes', $proponent_type);
    }

    /**
     * Verifica se existe um campo com o valor especificado para a faixa
     *
     * @param string $proponent_type a faixa a ser verificado
     * @return bool Verdadeiro se existir um campo com a faixa especificado, falso caso contrário
     */
    public function hasFieldOfRange(string $range_label): bool {
        return $this->hasFieldOf('registrationRanges', $range_label);
    }

    function publishRegistrations(){
        $this->checkPermission('publishRegistrations');

        $app = App::i();
        $app->em->beginTransaction();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).publishRegistrations:before");

        $this->publishedRegistrations = true;
        $this->save(true);

        $query = new ApiQuery(Registration::class, [
            'opportunity' => "EQ({$this->id})",
            'status'=>'EQ(10)'
        ]);

        $registration_ids = $query->findIds();

        foreach ($registration_ids as $registration_id) {
            $registration = $app->repo('Registration')->find($registration_id);

            // @todo: fazer dos selos em oportunidades um módulo separado (OpportunitySeals ??)
            $registration->setAgentsSealRelation();

            $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).publishRegistration", [$registration]);

            $app->em->flush();
            $app->em->clear();
        }

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).publishRegistrations:after");

        $app->em->commit();
    }

    function unPublishRegistrations()
    {
        $this->checkPermission('unPublishRegistrations');

        $app = App::i();
        $app->em->beginTransaction();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).unPublishRegistrations:before");

        $this->publishedRegistrations = false;
        $this->save(true);

        $query = new ApiQuery(Registration::class, [
            'opportunity' => "EQ({$this->id})",
            'status'=>'EQ(10)'
        ]);

        $registration_ids = $query->findIds();

        foreach ($registration_ids as $registration_id) {
            $registration = $app->repo('Registration')->find($registration_id);

            $registration->unsetAgentSealRelation();

            $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).unpublishRegistration", [$registration]);

            $app->em->flush();
            $app->em->clear();
        }

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).unPublishRegistrations:after");

        $app->em->commit();
    }

    function sendUserEvaluations($user = null){
        $app = App::i();

        if(is_null($user)){
            $user = $app->user;
        }

        $this->checkPermission('sendUserEvaluations', $user);

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).sendUserEvaluations:before", [$user]);

        /** @var RegistrationEvaluation[] $evaluations */
        $evaluations = $app->repo('RegistrationEvaluation')->findByOpportunityAndUser($this, $user);

        $app->disableAccessControl();

        foreach($evaluations as $evaluation){

            if($evaluation->status == RegistrationEvaluation::STATUS_EVALUATED) {
                $evaluation->send(true);
            }
        }

        $app->em->flush();

        $app->enableAccessControl();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).sendUserEvaluations:after", [$user]);
    }

    function importFields($importSource) {
        $this->checkPermission('modifyRegistrationFields');

        $app = App::i();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).importFields:before", [&$importSource]);

        $created_fields = [];
        $created_files = [];

        if (!is_null($importSource)) {
            $new_fields_by_old_field_name = [];

            // Pega o primeiro step da oportunidade
            $first_step = $app->repo('RegistrationStep')->findOneBy(['opportunity' => $this]);

            // Verifica se já existe algum campo configurado nesta oportunidade
            $fields = $this->registrationFieldConfigurations;
            $files = $this->registrationFileConfigurations;
            $has_field = false;

            if($fields || $files) {
                $has_field = true;
            }

            // Caso não tenha nenhum campo configurado e já exista um step que estará vazio, exclui o step
            if(!$has_field && $first_step) {
                $first_step->delete(true);
            }

            // Fields
            foreach($importSource->fields as &$field) {

                if(isset($field->config)){
                    $field->config = (array) $field->config;
                    if (isset($field->config['require']) && $field->config['require']) {
                        $field->config['require'] = (array) $field->config['require'];
                    }
                }

                $step = $this->getOrCreateStep($field->step->name ?? null, $field->step->displayOrder ?? null);

                $newField = new RegistrationFieldConfiguration;
                $newField->owner = $this;
                $newField->title = $field->title;
                $newField->description = $field->description;
                $newField->maxSize = $field->maxSize;
                $newField->fieldType = $field->fieldType;
                $newField->required = $field->required;
                $newField->categories = $field->categories;
                $newField->config = $field->config;
                $newField->fieldOptions = $field->fieldOptions;
                $newField->displayOrder = $field->displayOrder;
                $newField->conditional = $field->conditional;
                $newField->conditionalValue = $field->conditionalValue;
                $newField->proponentTypes = $field->proponentTypes;
                $newField->registrationRanges = $field->registrationRanges;
                $newField->step = $step->id;

                $field->newField = $newField;

                $new_fields_by_old_field_name[$field->fieldName] = $newField;

                // salva a primeira vez para obter um id
                 $newField->save(true);

                $created_fields[] = $newField;
            }

            foreach($importSource->fields as &$field) {
                $newField = $field->newField;
                if(!empty($field->conditionalField)){
                    $field_name = $field->conditionalField;

                    if(isset($new_fields_by_old_field_name[$field_name])) {

                        $newField->conditionalField = $new_fields_by_old_field_name[$field_name]->fieldName;

                        // salva a segunda vez para a tualizar o config
                        $newField->save(true);
                    }

                }

            }

            //Files (attachments)
            foreach($importSource->files as $file) {

                $newFile = new RegistrationFileConfiguration;

                $step = $this->getOrCreateStep($file->step->name ?? null, $file->step->displayOrder ?? null);

                $newFile->owner = $this;
                $newFile->title = $file->title;
                $newFile->description = $file->description;
                $newFile->required = $file->required;
                $newFile->categories = $file->categories;
                $newFile->displayOrder = $file->displayOrder;
                $newFile->conditional = $file->conditional;
                $newFile->conditionalValue = $file->conditionalValue;
                $newFile->step = $step->id;
                $newFile->proponentTypes = $file->proponentTypes;
                $newFile->registrationRanges = $file->registrationRanges;

                $app->em->persist($newFile);

                $file->newFile = $newFile;

                $newFile->save(true);

                $created_files[] = $newFile;


                if (is_object($file->template)) {

                    $originFile = $app->repo("RegistrationFileConfigurationFile")->find($file->template->id);

                    if (is_object($originFile)) { // se nao achamos o arquivo, talvez este campo tenha sido apagado

                        $tmp_file = sys_get_temp_dir() . '/' . $file->template->name;

                        if (file_exists($originFile->path)) {
                            copy($originFile->path, $tmp_file);

                            $newTemplateFile = array(
                                'name' => $file->template->name,
                                'type' => $file->template->mimeType,
                                'tmp_name' => $tmp_file,
                                'error' => 0,
                                'size' => filesize($tmp_file)
                            );

                            $newTemplate = new RegistrationFileConfigurationFile($newTemplateFile);

                            $newTemplate->owner = $newFile;
                            $newTemplate->description = $file->template->description;
                            $newTemplate->group = $file->template->group;

                            $app->em->persist($newTemplate);

                            $newTemplate->save(true);
                        }

                    }

                }
            }

            foreach($importSource->files as &$file) {
                $newFile = $file->newFile;
                if(!empty($file->conditionalField)){
                    $field_name = $file->conditionalField;

                    if(isset($new_fields_by_old_field_name[$field_name])) {

                        $newFile->conditionalField = $new_fields_by_old_field_name[$field_name]->fieldName;

                        // salva a segunda vez para a tualizar a condicional
                        $newFile->save(true);
                    }

                }

            }

            // Metadata
            foreach($importSource->meta as $key => $value) {
                if($key == 'continuousFlow') {
                    if($importSource->meta->isContinuousFlow && !$importSource->meta->hasEndDate) {
                        $this->$key = isset($value->date) ? new \DateTime($value->date) : null;
                    }
                    continue;
                }

                if($key == 'publishTimestamp') {
                    if($importSource->meta->isContinuousFlow && $importSource->meta->hasEndDate) {
                        $this->lastPhase->$key = isset($value->date) ? new \DateTime($value->date) : null;
                    }
                    continue;
                }
                
                if($key == 'registrationTo') {
                    if($importSource->meta->isContinuousFlow && $importSource->meta->hasEndDate) {
                        $this->$key = isset($value->date) ? new \DateTime($value->date) : null;
                    }
                    continue;
                }

                $this->$key = $value;
            }

            $this->save(true);

            $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).importFields:after", [&$importSource, &$created_fields, &$created_files]);

        }
    }

    /**
     * Obtém ou cria uma etapa de registro associada a uma oportunidade.
     *
     * Esta função verifica se uma etapa de registro com o nome especificado já existe
     * para a oportunidade atual. Se a etapa não existir, uma nova etapa é criada com
     * o nome e a ordem de exibição fornecidos. Caso o nome da etapa não seja informado,
     * um valor padrão "Etapa importada" é utilizado. Se a ordem de exibição não for informada,
     * a etapa é criada com a ordem padrão de valor 0.
     *
     * @param string|null $step_name       O nome da etapa de registro. Padrão: 'Etapa importada'.
     * @param int|null    $display_order   A ordem de exibição da etapa. Padrão: 0.
     *
     * @return RegistrationStep           Retorna a etapa de registro existente ou a nova etapa criada.
    */
    function getOrCreateStep($step_name = null, $display_order = null): RegistrationStep {
        $app = App::i();
        
        $step_name = $step_name ?? i::__('Etapa importada');
        
        $step = $app->repo('RegistrationStep')->findOneBy(['opportunity' => $this, 'name' => $step_name]);
        
        if (!$step) {
            $newStep = new RegistrationStep;
            $newStep->name = $step_name;
            $newStep->displayOrder = $display_order ?? 0;
            $newStep->opportunity = $this;
            $newStep->save();
    
            $step = $newStep;
        }
        
        return $step;
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

    /**
     * Retorna uma chave única para o cache do resumo da fase
     *
     * @return string A chave única para o cache do resumo da fase.
     */
    public function getSummaryCacheKey(): string
    {
        return "opportunity-summary-{$this->id}";
    }

    /**
     * Retorna um resumo do número de inscrições de uma oportunidade
     *
     * @return array
     */
    public function getSummary($skip_cache = false): array {
        if($this->isNew()) {
            return [];
        }

        /** @var App $app */
        $app = App::i();

        $cache_key = $this->summaryCacheKey;

        if(!$skip_cache && $app->config['app.useOpportunitySummaryCache']) {

            if ($app->mscache->contains($cache_key)) {
                return $app->mscache->fetch($cache_key);
            }
        }

        $params = ["opp" => $this];

        $query = $app->em->createQuery("
            SELECT
                o.status,
                COUNT(o.status) AS qtd
            FROM
                MapasCulturais\\Entities\\Registration o
            WHERE
                o.opportunity = :opp
            GROUP BY o.status");

        $query->setParameters($params);

        $data = [
            'registrations' => 0,
            'sent' => 0,
        ];

        $status_list = Registration::getStatuses();

        if($result = $query->getResult()){
            foreach($result as $value){
                $status = $status_list[$value['status']];
                $data['registrations'] += $value['qtd'];

                if ($value['status'] > 0) {
                    $data['sent'] += $value['qtd'];
                }

                $data[$status] = $value['qtd'];
            }
        }

        if($app->config['app.useOpportunitySummaryCache']) {
            $app->mscache->save($cache_key, $data, $app->config['app.opportunitySummaryCache.lifetime']);
        }

        $app->applyHookBoundTo($this, "opportunity.summary", [&$data]);

        return $data;
    }

    /**
     * Retorna os campos de seleção e/ou seleção múltipla e/ou booleanos ou todos
     *
     * @return array
     */
    function getFields($select = true, $multiselect = true, $boolean = true, $all = false, $include_previous_phases = true) {
        $data = [];
        $currentPhase = $this;
        $phases[] = $currentPhase;

        if($include_previous_phases) {
            $phases = $currentPhase->allPhases;
        }

        foreach($phases as $phase) {
            if($phase->isDataCollection) {
                if($fields = $phase->registrationFieldConfigurations) {
                    foreach($fields as $field) {
                        if($all
                            || ($select && $field->fieldType == 'select')
                            || ($multiselect && $field->fieldType == 'checkboxes')
                            || ($boolean && $field->fieldType == 'checkbox')
                        ){
                            if (!in_array($field, $data)) {
                                $data[] = $field;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function getRevisionData() {
        $registration_fields = $this->registrationFieldConfigurations;
        $registration_files = $this->registrationFileConfigurations;

        $revision_data = [];
        foreach($registration_fields as $field) {
            $revision_data[$field->fieldName] = $field->jsonSerialize();
        }

        foreach($registration_files as $field) {
            $revision_data[$field->fileGroupName] = $field->jsonSerialize();
        }

        return $revision_data;
    }

    function unregisterRegistrationMetadata(){
        $app = App::i();

        $registered_metadata = $app->getRegisteredMetadata(Registration::class);
        if (isset($registered_metadata['projectName'])) {
            $app->unregisterEntityMetadata(Registration::class, 'projectName');
        }

        foreach($this->registrationFieldConfigurations as $field){
            $field_name = $field->fieldName;
            if (isset($registered_metadata[$field_name])) {
                $app->unregisterEntityMetadata(Registration::class, $field_name);
            }
        }
    }

    function registerRegistrationMetadata($also_previous_phases = false){

        $app = App::i();

        $registered_metadata = $app->getRegisteredMetadata(Registration::class);

        if (!isset($registered_metadata['projectName']) && $this->projectName){
            $cfg = [ 'label' => \MapasCulturais\i::__('Nome do Projeto') ];

            $metadata = new MetadataDefinition('projectName', $cfg);
            $app->registerMetadata($metadata, Registration::class);
        }

        foreach($this->registrationFieldConfigurations as $field){
            if (isset($registered_metadata[$field->getFieldName()])) {
                continue;
            }

            if(in_array($field->fieldType, ['agent-owner-field', 'agent-collective-field'])) {
                $agent_properties_metadata = \MapasCulturais\Entities\Agent::getPropertiesMetadata();
                $agent_field_name = $field->config['entityField'] ?? null;
                $agent_field = $agent_properties_metadata[$agent_field_name] ?? null;
                $field_type = $agent_field['field_type'] ?? $agent_field['type'] ?? 'text';

                if(str_starts_with($field->config['entityField'], '@terms')) {
                    $field_type = 'multiselect';
                }

                if($field->config['entityField'] == '@location') {
                    $field_type = 'location';
                }

                if($field->config['entityField'] == '@links') {
                    $field_type = 'links';
                }

                if($field->config['entityField'] == '@bankFields'){
                    $field_type = 'bankFields';
                }
                
                if(in_array($field->config['entityField'], ['longDescription', 'shortDescription'])){
                    $field_type = 'textarea';
                }

            } else if ($field->fieldType == 'checkboxes') {
                $field_type = 'checklist';

            } else {
                $field_type = $field->fieldType;
            }

            $cfg = [
                'label' => $field->title,
                'type' => $field_type,
                'private' => true,
                'registrationFieldConfiguration' => $field
            ];

            $def = $field->getFieldTypeDefinition();

            if($def->requireValuesConfiguration){
                $cfg['options'] = [];
                foreach ($field->fieldOptions as $option) {
                    $option_parts = explode(':', $option, 2);
                    if (count($option_parts) == 2) {
                        $cfg['options'][$option_parts[0]] = $option_parts[1];
                    } else {
                        $cfg['options'][$option] = $option;
                    }
                }
            }

            if(is_callable($def->serialize)){
                $cfg['serialize'] = $def->serialize;
            }

            if(is_callable($def->unserialize)){
                $cfg['unserialize'] = $def->unserialize;
            }

            if($def->defaultValue){
                $cfg['default_value'] = $def->defaultValue;
            }

            if($def->validations){
                $cfg['validations'] = $def->validations;
            } else {
                $cfg['validations'] = [];
            }

            if($field->required){
                $cfg['validations']['required'] = \MapasCulturais\i::__('O campo é obrigatório');
            }

            $app->applyHookBoundTo($this, "controller(opportunity).registerFieldType({$field->fieldType})", [$field, &$cfg]);

            $metadata = new MetadataDefinition ($field->fieldName, $cfg);

            $app->registerMetadata($metadata, Registration::class);
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.registrationMetadata");

        if($also_previous_phases && $this->parent) {
            $this->previousPhase->registerRegistrationMetadata();
        }

    }

    protected function canUser_control($user) {

        if ($this->ownerEntity->canUser('@control', $user)) {
            return true;
        } else {
            return parent::canUser_control($user);
        }
    }

    protected function genericPermissionVerification($user){
        if($user->is('guest')){
            return false;
        }

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

        if ($this->canUser("@control") && !$this->isRegistrationOpen()) {
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

    protected function canUserRemove($user){
        if ($this->publishedRegistrations) {
            return false;
        }
        return parent::canUserRemove($user);
    }

    protected function canUserSendUserEvaluations($user){
        if (!$this->evaluationMethodConfiguration) {
            return false;
        }

        if($user->is('guest')) {
            return false;
        }

        $can_evaluate = $this->canUserEvaluateRegistrations($user);

        if($this->canUser('@control')) {
            return $can_evaluate ? true : false;
        }

        $today = new \DateTime('now');

        $em = $this->evaluationMethodConfiguration;

        return $can_evaluate && $today >= $em->evaluationFrom && $today <= $em->evaluationTo;
    }

    protected function canUserEvaluateRegistrations($user){
        if($user->is('guest')){
            return false;
        }

        if($this->canUser('@control')) {
            return true;
        }

        if (!$this->evaluationMethodConfiguration) {
            return false;
        }

        $relation = $this->evaluationMethodConfiguration->getUserRelation($user);

        return $relation && $relation->status === AgentRelation::STATUS_ENABLED;
    }

    protected function canUserViewEvaluations($user){
        $em = $this->evaluationMethodConfiguration;

        if($em) {
            return $this->evaluationMethodConfiguration->canUser('@control', $user);
        } else {
            return false;
        }
    }

    protected function canUserCreateAgentRelationWithControl($user){
        if ($this->ownerEntity->canUser('@control', $user)) {
            return true;
        } else {
            return $this->__canUserCreateAgentRelationWithControl($user);
        }
    }

    function canUserRemoveAgentRelationWithControl($user){
        if ($this->ownerEntity->canUser('@control', $user)) {
            return true;
        } else {
            return $this->__canUserRemoveAgentRelationWithControl($user);
        }
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
