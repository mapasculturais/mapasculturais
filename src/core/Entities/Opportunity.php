<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entity;
use MapasCulturais\ApiQuery;
use MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\Definitions\Metadata as MetadataDefinition;
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
 * @property RegistrationFileConfiguration $registrationFileConfigurations
 * @property RegistrationFieldConfiguration $registrationFieldConfigurations
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
        Traits\EntityArchive{
            Traits\EntityNested::setParent as nestedSetParent;
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
     * @ORM\Column(name="registration_proponent_types", type="json", nullable=true)
     */
    protected $registrationProponentTypes;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_ranges", type="json", nullable=true)
     */
    protected $registrationRanges;

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
     * @var object
     *
     * @ORM\Column(name="avaliable_evaluation_fields", type="json", nullable=true)
     */
    protected $avaliableEvaluationFields = [];
    
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

        $result = App::i()->repo('RegistrationFileConfiguration')->findBy(['owner' => $this]);

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.registrationFileConfigurations", [&$result]);
        
        return $result;
    }

    /**
     * 
     * @return RegistrationFieldConfiguration[]
     */
    public function getRegistrationFieldConfigurations() {
        $app = App::i();
        
        $result = App::i()->repo('RegistrationFieldConfiguration')->findBy(['owner' => $this]);

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
        if($this->parent){
            $entities[] = $this->parent;
        }
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

    /**
     * Indica se as inscrições estão abertas
     * @return bool 
     */
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

        $evaluations = $app->repo('RegistrationEvaluation')->findByOpportunityAndUser($this, $user);

        $app->disableAccessControl();
        
        foreach($evaluations as $evaluation){
            if($evaluation->status == 1) {
                $evaluation->status = RegistrationEvaluation::STATUS_SENT;
                $evaluation->save(true);
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

            // Fields
            foreach($importSource->fields as &$field) {
                
                if(isset($field->config)){
                    $field->config = (array) $field->config;
                    if (isset($field->config['require']) && $field->config['require']) {
                        $field->config['require'] = (array) $field->config['require'];
                    }
                }

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

                $newFile->owner = $this;
                $newFile->title = $file->title;
                $newFile->description = $file->description;
                $newFile->required = $file->required;
                $newFile->categories = $file->categories;
                $newFile->displayOrder = $file->displayOrder;
                $newFile->conditional = $file->conditional;
                $newFile->conditionalValue = $file->conditionalValue;

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
                $this->$key = $value;
            }

            $this->save(true);

            $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).importFields:after", [&$importSource, &$created_fields, &$created_files]);

        }
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
     * Retorna um resumo do número de inscrições de uma oportunidade
     * 
     * @return array
     */
    public function getSummary()
    {
        if($this->isNew()) {
            return [];
        }

        /** @var App $app */
        $app = App::i();

        if($app->config['app.useOpportunitySummaryCache']) {
            $cache_key = __METHOD__ . ':' . $this->id; 
            if ($app->cache->contains($cache_key)) {
                return $app->cache->fetch($cache_key);
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
                
                if ($this->evaluationMethodConfiguration){
                    if (in_array($value['status'], [0,1])){
                        $data[$status] = $value['qtd'];
                    } 
                } else {
                    $data[$status] = $value['qtd'];
                }
            }
        }

        if($app->config['app.useOpportunitySummaryCache']) {
            $app->cache->save($cache_key, $data, $app->config['app.opportunitySummaryCache.lifetime']);
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

            $cfg = [
                'label' => $field->title,
                'type' => $field->fieldType === 'checkboxes' ? 'checklist' : $field->fieldType ,
                'private' => true,
                'registrationFieldConfiguration' => $field
            ];

            $def = $field->getFieldTypeDefinition();
            
            if($def->requireValuesConfiguration){
                $cfg['options'] = $field->fieldOptions;
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

        if($this->publishedRegistrations){
            return false;
        }

        if (!$this->evaluationMethodConfiguration) {
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
        $em = $this->evaluationMethodConfiguration;
        
        if($em) {
            return $this->evaluationMethodConfiguration->canUser('@control', $user);
        } else {
            return false;
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
