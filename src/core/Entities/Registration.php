<?php
namespace MapasCulturais\Entities;

use DateTime;
use MapasCulturais\i;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\EvaluationMethod;
use MapasCulturais\GuestUser;

/**
 * Registration
 * @property Agent $owner The owner of this registration
 * @property Opportunity $opportunity
 * @property string $category
 * @property string $proponentType
 * @property string $range
 * 
 * @property-read EvaluationMethodConfiguration $evaluationMethodConfiguration
 * @property-read EvaluationMethod $evaluationMethod
 * @property-read mixed $evaluationResultValue valor do resultado consolidado das avaliações
 * @property-read string $evaluationResultString string do resultado consolidado das avaliações
 * @property-read RegistrationEvaluation[] $sentEvaluations lista de avaliações enviadas
 * @property-read array|object $spaceData retorna o snapshot dos dados do espaço relacionado
 * @property-read array|object $agentsData retorna o snapshot dos dados dos agentes relacionados e do agente owner
 * @property-read object $valuersExceptionsList retorna a configuração de exceções da lista de avaliadores, aqueles que não entram na regra de distribuição padrão
 * @property-read array $valuersIncludeList retorna a lista de avaliadores incluídos
 * @property-read array $valuersExcludeList retorna a lista de avaliadores excluídos
 * @property-read array $valuers retorna a lista de avaliadores excluídos
 * @property-read array $statuses Nomes dos status
 *
 * @ORM\Table(name="registration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Registration")
 * @ORM\HasLifecycleCallbacks
 */
class Registration extends \MapasCulturais\Entity
{
    use Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityOwnerAgent,
        Traits\EntityAgentRelation,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityRevision {
            Traits\EntityMetadata::canUserViewPrivateData as __canUserViewPrivateData;
        }


    const STATUS_SENT = self::STATUS_ENABLED;
    const STATUS_APPROVED = 10;
    const STATUS_WAITLIST = 8;
    const STATUS_NOTAPPROVED = 3;
    const STATUS_INVALID = 2;


    protected $__enableMagicGetterHook = true;
    protected $__enableMagicSetterHook = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"default": "pseudo_random_id_generator()"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="MapasCulturais\DoctrineMappings\RandomIdGenerator")
     */
    public $id;


    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=24, nullable=true)
     */
    public $number;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255, nullable=true)
     */
    protected $category;


    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $opportunity;


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
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_timestamp", type="datetime", nullable=true)
     */
    protected $sentTimestamp;


    /**
     * @var array
     *
     * @ORM\Column(name="agents_data", type="json", nullable=true)
     */
    protected $agentsData = [];
    
    /**
     * @var integer
     *
     * @ORM\Column(name="consolidated_result", type="string", length=255, nullable=true)
     */
    protected $consolidatedResult = self::STATUS_DRAFT;
    
    /**
     * @var array
     *
     * @ORM\Column(name="space_data", type="json", nullable=true)
     */
    protected $_spaceData = [];

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DRAFT;

      /**
     * @var string
     *
     * @ORM\Column(name="proponent_type", type="string", nullable=false)
     */
    protected $proponentType;

    /**
     * @var string
     *
     * @ORM\Column(name="range", type="string", nullable=false)
     */
    protected $range;
    
    /**
     * @var object
     *
     * @ORM\Column(name="valuers_exceptions_list", type="json", nullable=false)
     */
    protected $__valuersExceptionsList;

    /**
     * @var object
     *
     * @ORM\Column(name="valuers", type="json", nullable=false)
     */
    protected $__valuers;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationMeta", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
    */
    protected $__metadata = [];

    /**
     * @var \MapasCulturais\Entities\RegistrationFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationFile", fetch="EXTRA_LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationPermissionCache", mappedBy="owner", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**

     * @var \MapasCulturais\Entities\RegistrationAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationAgentRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\RegistrationSpaceRelation[] Space Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationSpaceRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__spaceRelation;

    /**
     * @var integer
     *
     * @ORM\Column(name="subsite_id", type="integer", nullable=true)
     */
    protected $_subsiteId;

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float", nullable=true)
     */
    protected $score;

    /**
     * @var boolean
     *
     * @ORM\Column(name="eligible", type="boolean", nullable=true)
     */
    protected $eligible;

    /**
     * @var dateTime
     *
     * @ORM\Column(name="editable_until", type="datetime", nullable=true)
     */
    protected $editableUntil;

    /**
     * @var dateTime
     *
     * @ORM\Column(name="edit_sent_timestamp", type="datetime", nullable=true)
     */
    protected $editSentTimestamp;

     /**
     * @var array
     *
     * @ORM\Column(name="editable_fields", type="json", nullable=true)
     */
    protected $editableFields;

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
     * @var dateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;


    public $preview = false;

    protected static $hooked = false;

    function __construct() {
        $app = App::i();

        $this->__valuersExceptionsList = (object) ["include" => [], "exclude" => []];
        $this->__valuers = (object)[];
        
        $this->owner = $app->user->profile;

        if(!self::$hooked){
            self::$hooked = true;
            $app->hook('entity(' . $this->getHookClassPath() . ').randomId', function($id) use($app) {
                if(!$this->number){
                    $this->number = $app->config['registration.prefix'] . $id;
                }
            });
        }

        parent::__construct();
    }

    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return \MapasCulturais\i::__('Inscrições');
        else
            return \MapasCulturais\i::__('Inscrição');
    }

    function save($flush = false){
        $this->cleanRegisteredFields();
        parent::save($flush);
    }

    function getSingleUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
    }

    function getEditUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
    }

    function cleanRegisteredFields() {
        $app = App::i();
        $registered_metadata = $app->getRegisteredMetadata($this);

        $fields = [];
        foreach($this->opportunity->registrationFieldConfigurations as $field){
            $fields[] = $field->getFieldName();
        }

        foreach($registered_metadata as $key => $metadada){
            if(!in_array($key, $fields) && (strpos("field_", $key) === 0)){
                $app->unregisterEntityMetadata(Registration::class, $key);
            }
        }
    }
    
    function consolidateResult($flush = false, $caller = null){
        $app = App::i();
        
        $app->disableAccessControl();
        $em = $this->getEvaluationMethod();
        
        $result = $em->getConsolidatedResult($this);
        
        $this->consolidatedResult = $result;

        // para que dentro do hook as permissões funcionem
        $app->enableAccessControl();
        
        $app->applyHookBoundTo($this, 'entity(Registration).consolidateResult', [&$result, $caller]);
        
        $connection = $app->em->getConnection();
        $connection->executeQuery('UPDATE registration SET consolidated_result = :result WHERE id = :id', [
            'result' => $result,
            'id' => $this->id
        ]);

    }

    static function isPrivateEntity(){
        return true;
    }

    static function getValidations() {
        $app = App::i();
        $validations = [
            'owner' => [
                'required' => i::__("O agente responsável é obrigatório."),
                '$this->validateOwnerLimit()' => i::__('Foi excedido o limite de inscrições para este agente responsável.'),
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    /** 
     * @inheritdoc
     */
    public static function getPropertiesMetadata($include_column_name = false){
        $result = parent::getPropertiesMetadata($include_column_name);
        $result['valuersIncludeList'] = [
            'isEntityRelation' => false,
            'isMetadata' => false,
            'isPK' => false,
            'required' => false,
            'type' => 'array',
            'label' => i::__('Lista de de inclusão avaliadores')
        ];
        $result['valuersExcludeList'] = [
            'isEntityRelation' => false,
            'isMetadata' => false,
            'isPK' => false,
            'required' => false,
            'type' => 'array',
            'label' => i::__('Lista de exclusão de avaliadores')
        ];

        $result['valuers'] = [
            'isEntityRelation' => false,
            'isMetadata' => false,
            'isPK' => false,
            'required' => false,
            'type' => 'array',
            'label' => i::__('Lista de avaliadores da inscrição')
        ];

        return $result;
    }
    
    
    function jsonSerialize(): array {
        $this->registerFieldsMetadata();
        
        $json = [
            '@entityType' => $this->getControllerId(),
            'id' => $this->id,
            'opportunity' => $this->opportunity->simplify('id,name,singleUrl'),
            'createTimestamp' => $this->createTimestamp,
            'updateTimestamp' => $this->updateTimestamp,
            'sentTimestamp' => $this->sentTimestamp,
            'projectName' => $this->projectName,
            'number' => $this->number,
            'category' => $this->category,
            'range' => $this->range,
            'proponentType' => $this->proponentType,
            'owner' => $this->owner->simplify('id,name,lockedFields,singleUrl'),
            'agentRelations' => [],
            'files' => [],
            'singleUrl' => $this->singleUrl,
            'editUrl' => $this->editUrl,
            'appliedForQuota' => $this->appliedForQuota,
            'editableUntil' => $this->editableUntil,
            'editableFields' => $this->editableFields,
            'editSentTimestamp' => $this->editSentTimestamp,
            'status' => $this->status,
        ];

        if($this->opportunity->canUser('@control')) {
            $json['valuersIncludeList'] = $this->valuersIncludeList;
            $json['valuersExcludeList'] = $this->valuersExcludeList;
            $json['valuers'] = $this->valuers;
        }

        if($this->canUser('viewConsolidatedResult')){
            $json['evaluationResultValue'] = $this->getEvaluationResultValue();
            $json['evaluationResultString'] = $this->getEvaluationResultString();
        }

        foreach($this->getRegisteredMetadata(null, true) as $meta_key => $def){
            if(substr($meta_key, 0, 6) === 'field_'){
                $json[$meta_key] = $this->$meta_key;
            }
        }

        if($this->opportunity->publishedRegistrations || $this->opportunity->canUser('@control') || $this->status == self::STATUS_DRAFT) {
            $json['status'] = $this->status;
        }

        if($this->canUser('view') || $this->status === self::STATUS_APPROVED || $this->status === self::STATUS_WAITLIST){
            $related_agents = $this->getRelatedAgents(return_relations: true);

            $json['agentRelations'] = $related_agents;

            foreach($this->files as $group => $file){
                if($file instanceof File){
                    $json['files'][$group] = $file->simplify('id,url,name,deleteUrl');
                }
            }
        }else{
            $json = null;
        }
        
        if($this->canUser("viewUserEvaluation") && !$this->canUser("@control")){
            $checkList = 'projectName,category,files,field,owner';
            $result = ['files' => []];

            foreach($json as $k => $v){
                $_k = preg_replace('/field_\d+/', 'field', $k);
                
                if(strpos($checkList, $_k) >= 0){

                    if($k == "files"){
                        foreach(array_keys($v) as $f){
                            if($this->canSee($f)){
                                $result[$k][$f] = $v[$f];
                            }  
                        }
                       
                    }else if($k == "owner" || $k == "agentRelations"){
                        if($this->canSee("agentsSummary")){
                            $result[$k] = $v;
                        }
                    }else{
                        if($this->canSee($k) || $this->opportunity->canUser("@control")){
                            $result[$k] = $v;
                        }
                    }
                }else{
                    $result[$k] = $v;
                }
            }
        }else{
            $result = $json;
        }

        $result['spaceRelation'] = $this->getSpaceRelation();

        $app = App::i();
        $app->applyHookBoundTo($this, "{$this->hookPrefix}.jsonSerialize", [&$result]);

        return $result;
    }

    public function canSee($key)
    {
        if(in_array($key, ['id', 'number', 'category', 'range', 'proponentType'])){
            return true;
        }

        $avaliableEvaluationFields = ($this->opportunity->avaliableEvaluationFields != "null") ? $this->opportunity->avaliableEvaluationFields : [];
        if($avaliableEvaluationFields && in_array($key, array_keys($avaliableEvaluationFields))){
            return true;
        }

        return false;
    }

    function getSpaceRelation(){ 
        $relation = App::i()->repo('RegistrationSpaceRelation')->findBy(['owner' => $this]);
        
        if (is_array($relation) && isset($relation[0]) && $relation[0]->space)  {
            //var_dump($relation);
            return $relation[0];
        } else {
            return null;
        }
    }

    static function usesSpaceRelation() {
        return true;
    }

    static function getSpaceRelationEntityClassName() {
        return RegistrationSpaceRelation::class;
    }

    function setEditableUntil($datetime) {
        if ($this->opportunity->canUser('@control')) {
            $this->editableUntil = new DateTime($datetime);
        }
    }

    function setEditableFields($fields) {
        if ($this->opportunity->canUser('@control')) {
            $this->editableFields = $fields;
        }
    }

    protected function setEditSentTimestamp($datetime) {
        return false;
    }

    function reopenEditableFields() {
        $this->opportunity->checkPermission('@control');
        $this->editSentTimestamp = null;
        $this->save(true);
    }

    function sendEditableFields() {
        $app = App::i();
        $this->checkPermission('sendEditableFields');

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).sendEditableFields:before");

        $this->editSentTimestamp = new DateTime();
        $app->disableAccessControl();
        $this->save(true);
        $app->enableAccessControl();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).sendEditableFields:after");
    }

    function setOwnerId($id){
        $agent = App::i()->repo('Agent')->find($id);
        $this->setOwner($agent);
    }

    protected $_ownerChanged = false;

    function setOwner($agent){
        if (is_numeric($agent)) {
            $this->setOwnerId($agent);
        } else {
            $this->_ownerChanged = true;
            $this->owner = $agent;
        }
    }

    function validateOwnerLimit(){
        // updating and changing owner
        if($this->id && !$this->_ownerChanged){
            return true;
        }else{
            $registrationCount = $this->repo()->countByOpportunityAndOwner($this->opportunity, $this->owner);
            $limit = $this->opportunity->registrationLimitPerOwner;
            if($limit > 0 && $registrationCount >= $limit){
                return false;
            }
        }
        return true;
    }

    function setOpportunityId($id){
        if(!$this->isNew()) {
            return;
        }

        $opportunity = App::i()->repo('Opportunity')->find($id);
        $this->opportunity = $opportunity;
    }

    function setOpportunity($opportunity) {
        if(!$this->isNew()) {
            return;
        }

        if(is_numeric($opportunity)) {
            $this->setOpportunityId($opportunity);
        } else {
            $this->opportunity = $opportunity;   
        }
    }
    
    /**
     *
     * @return
     */
    protected function _getRegistrationOwnerRequest(){
        return  App::i()->repo('RequestChangeOwnership')->findOneBy(['originType' => $this->getClassName(), 'originId' => $this->id]);
    }

    function getRegistrationOwnerStatus(){
        if($request = $this->_getRegistrationOwnerRequest()){
            return RegistrationAgentRelation::STATUS_PENDING;
        }else{
            return RegistrationAgentRelation::STATUS_ENABLED;
        }
    }

    function getRegistrationOwner(){
        if($request = $this->_getRegistrationOwnerRequest()){
            return $request->agent;
        }else{
            return $this->owner;
        }
    }

    protected function _getAgentsWithDefinitions(){
        $definitions = App::i()->getRegistrationAgentsDefinitions();
        $owner = $this->owner;
        $owner->definition = $definitions['owner'];
        $agents = [$owner];
        $relAgents = $this->relatedAgents;

        foreach($relAgents as $groupName => $relatedAgents){
            $agent = clone $relatedAgents[0];
            $agent->groupName = $groupName;
            $agent->definition = $definitions[$groupName];
            $agents[] = $agent;
        }
        return $agents;
    }


    function _getDefinitionsWithAgents(){
        $definitions = App::i()->getRegistrationAgentsDefinitions();
        foreach($definitions as $groupName => $def){
            $metadata_name = $def->metadataName;
            $meta_val = $this->opportunity->$metadata_name;
            $definitions[$groupName]->use = $meta_val;

            if($meta_val === 'dontUse'){
                $definitions[$groupName]->agent = null;
                $definitions[$groupName]->relationStatus = null;

            }else{
                if($groupName === 'owner'){
                    $relation = $this->owner;
                    $meta_val = 'required';
                    $relation_status = 1;
                    $definitions[$groupName]->use = 'required';
                }else{
                    $related_agents = $this->getRelatedAgents($def->agentRelationGroupName, true, true);
                    if($related_agents){
                        $relation = $related_agents[0]->agent;
                        $relation_status = $related_agents[0]->status;
                    }else{
                        $relation = null;
                        $relation_status = null;
                    }
                }


                $definitions[$groupName]->agent = $relation ? $relation : null;
                $definitions[$groupName]->relationStatus = $relation_status;
            }
        }
        return $definitions;
    }

    /**
     * Retorna o valor do resultado consolidado da avaliação
     * 
     * @return mixed 
     * @throws PermissionDenied 
     */
    function getEvaluationResultValue(){
        if($method = $this->getEvaluationMethod()) {
            return $method->getConsolidatedResult($this);
        }

        return null;
    }

    /**
     * Retorna a string do valor consolidado da avaliação
     * 
     * @return string 
     * @throws PermissionDenied 
     */
    function getEvaluationResultString(){
        if($method = $this->getEvaluationMethod()) {
            $value = $this->getEvaluationResultValue();
            return $method->valueToString($value);
        }

        return null;
    }

    /**
     * Retorna uma lista com as avaliações enviadas
     * @return RegistrationEvaluation[] 
     */
    function getSentEvaluations() {
        $app = App::i();
        
        $evaluations = $app->repo('RegistrationEvaluation')->findBy([
            'registration' => $this, 
            'status' => RegistrationEvaluation::STATUS_SENT
        ]);

        return $evaluations;
    }

    /**
     * Retorna o snapshot dos dados do espaço relacionado
     * 
     * @return array 
     */
    function getSpaceData(){
        if($this->_spaceData['acessibilidade_fisica'] ?? false){
            $this->_spaceData['acessibilidade_fisica'] = str_replace(';', ', ', $this->_spaceData['acessibilidade_fisica']);
        } 
        
        return $this->_spaceData;
    }

    /**
     * Retorna o snapshot dos dados dos agentes relacionados e do agente owner
     * 
     * @return array
     */
    function getAgentsData(){
        if($this->canUser('view')){
            return $this->agentsData;
        }else{
            return [];
        }
    }

    /**
     * @deprecated
     * @return int 
     */
    function randomIdGeneratorInitialRange(){
        return 1000;
    }

    function getValuers(): array {
        return (array) $this->__valuers;
    }

    /**
     * Retorna a configuração de exceções da lista de avaliadores, aqueles que não entram na regra de distribuição padrão
     * 
     * @return mixed 
     */
    function getValuersExceptionsList(){
        if(is_string($this->__valuersExceptionsList) && json_validate($this->__valuersExceptionsList)) {
            $this->__valuersExceptionsList = json_decode($this->__valuersExceptionsList);
        }
        return (object) $this->__valuersExceptionsList;
    }

    protected function _setValuersExceptionsList($object){
        $this->checkPermission('modifyValuers');

        if(is_object($object) && isset($object->exclude) && is_array($object->exclude) && isset($object->include) && is_array($object->include)){
            $this->__valuersExceptionsList = $object;
        } else {
            throw new \Exception('Invalid __valuersExceptionsList format');
        }
    }

    function setValuersExcludeList(array $user_ids){
        $exceptions = $this->getValuersExceptionsList();
        $exceptions->exclude = $user_ids;
        $this->_setValuersExceptionsList($exceptions);
    }

    function setValuersIncludeList(array $user_ids){
        $exceptions = $this->getValuersExceptionsList();
        $exceptions->include = $user_ids;
        $this->_setValuersExceptionsList($exceptions);
    }

    /**
     * Retorna a lista de avaliadores incluídos
     * 
     * @return mixed 
     */
    function getValuersIncludeList(){
        $exceptions = $this->getValuersExceptionsList();
        return (array) $exceptions->include;
    }
    
    /**
     * Retorna a lista de avaliadores excluídos
     * @return mixed 
     */
    function getValuersExcludeList(){
        $exceptions = $this->getValuersExceptionsList();
        return (array) $exceptions->exclude;
    }

    /**
     * Retorna a lista dos campos verificados e os selos que verificam cada campo
     *
     *  @return object Um objeto contendo os selos bloqueados de cada campo.
     */
    function getLockedFieldSeals() {
        $owner_locked_field_seals = (array) $this->owner->lockedFieldSeals;

        $related_agents = $this->relatedAgents ?: [];
        $collective_locked_field_seals = [];
        
        if(isset($related_agents['coletivo'])) {
            $collective_locked_field_seals = (array) $related_agents['coletivo'][0]->lockedFieldSeals;
        }

        $locked_field_seals = [];

        $fields = $this->opportunity->registrationFieldConfigurations;

        foreach($fields as $field) {
            if($field->fieldType == 'agent-owner-field' && isset($owner_locked_field_seals[$field->config['entityField']])) {
                $locked_field_seals[$field->fieldName] = $owner_locked_field_seals[$field->config['entityField']];
                
            }

            if($field->fieldType == 'agent-collective-field' && isset($collective_locked_field_seals[$field->config['entityField']])) {
                $locked_field_seals[$field->fieldName] = $collective_locked_field_seals[$field->config['entityField']];
            }
        }
        
        return (object) $locked_field_seals;
    }

    /**
     * Retorna a lista dos campos bloqueados.
     *
     * @return array Um array contendo os nomes dos campos bloqueados.
     */
    function getLockedFields() {
        $locked_field_seals = (array) $this->lockedFieldSeals;

        $locked_fields = [];
        if (!empty($locked_field_seals)) {
            $locked_fields = array_keys($locked_field_seals);
        }

        return $locked_fields;
    }

    /**
     * Obtém as relações de selos do proprietário e dos agentes relacionados.
     *
     * @return array Retorna um array contendo todos os selos do proprietário e do coletivo.
     */
    function getAgentSealRelations() {
        $app = App::i();
        
        $seals = [];
        $owner_seals = $this->owner->sealRelations;
        $related_agents = $this->relatedAgents ?: [];
        $collective_seals = isset($related_agents['coletivo']) ? $related_agents['coletivo'][0]->sealRelations : [];

        $all_seals = array_merge($owner_seals, $collective_seals);

        if (empty($all_seals)) {
            return [];
        }
        
        foreach ($all_seals as $relation) {
            $seals[] = [
                '__objectType' => 'seal', // Added for compatibility with <mc-avatar>
                'sealRelationId' => $relation->id,
                'sealId' => $relation->seal->id,
                'name' => $relation->seal->name,
                'files' => $relation->seal->files ?? null,
                'singleUrl' => $app->createUrl('seal', 'sealRelation', [$relation->id]),
                'createTimestamp' => $relation->createTimestamp,
                'isVerificationSeal' => in_array($relation->seal->id, $app->config['app.verifiedSealsIds']),
            ];
        }
    
        return $seals;
    }

    /** 
     * Retorna os avaliadores da inscrição agrupados pelos comitês
     * 
     * @return User[][] 
     */
    function getCommittees($skip_tiebreaker = false): array {
        $evaluation_method = $this->evaluationMethod;
        
        $committee = $evaluation_method->getCommitteeGroups($this->evaluationMethodConfiguration);

        $valuer_users = [];
        $registration_committee = [];

        // agrupa os avaliadores por comitê
        foreach($committee as $group => $users) {
            if($skip_tiebreaker && $group == '@tiebreaker') {
                continue;
            }
            $registration_committee[$group] = [];

            foreach($users as $user) {
                if($evaluation_method->canUserEvaluateRegistration($this, $user) || $this->getUserEvaluation($user)) {
                    $valuer_users[] = $user;
                    $registration_committee[$group][] = $user;
                }
            }
        }

        return $registration_committee;
    } 

    /** 
     * Verifica se a inscrição precisa de um desempate
     * 
     * @return bool
     */ 
    function needsTiebreaker(): bool {
        $evaluation_method = $this->evaluationMethod;
        
        return $evaluation_method ? $evaluation_method->registrationNeedsTiebreaker($this) : false;
    }

    function _setStatusTo($status, $flush = true){
        if($this->status === self::STATUS_DRAFT && $status === self::STATUS_SENT){
            $this->checkPermission('send');
        }else{
            $this->checkPermission('changeStatus');
        }

        $app = App::i();
        $app->disableAccessControl();
        $this->status = $status;
        $this->save($flush);
        $app->enableAccessControl();
        
        $this->enqueueToPCacheRecreation();
    }

    public function unsetAgentSealRelation()
    {
        $app = App::i();

        $opportunityMetadataSeals = $this->opportunity->registrationSeals;


        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).unsetAgentsSealRelation:before", [&$opportunityMetadataSeals]);

        $app->disableAccessControl();

        if(isset($opportunityMetadataSeals->owner)) {
            $sealOwner = $app->repo('Seal')->find($opportunityMetadataSeals->owner);
            $this->owner->removeSealRelation($sealOwner);
        }

        $sealInstitutions = isset($opportunityMetadataSeals->institution) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->institution) : null;

    	$sealCollective = isset($opportunityMetadataSeals->collective) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->collective) : null;

        foreach($this->relatedAgents as $groupName => $relatedAgents){
            if (trim($groupName) == 'instituicao' && isset($opportunityMetadataSeals->institution) && is_object($sealInstitutions)) {
                $agent = $relatedAgents[0];
                $agent->removeSealRelation($sealInstitutions);
            } elseif (trim($groupName) == 'coletivo' && isset($opportunityMetadataSeals->collective) && is_object($sealCollective)) {
                $agent = $relatedAgents[0];
                $agent->removeSealRelation($sealCollective);
            }
        }

        $app->enableAccessControl();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).unsetAgentsSealRelation:after", [&$opportunityMetadataSeals]);
    }

    function setAgentsSealRelation() {
    	$app = App::i();

        /*
        * Related Seals added to registration to Agents (Owner/Institution/Collective) atributed on aproved registration
        */
        $opportunityMetadataSeals = $this->opportunity->registrationSeals;

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).setAgentsSealRelation:before", [&$opportunityMetadataSeals]);

    	$app->disableAccessControl();

        $seal_relations = (object)[];

    	if(isset($opportunityMetadataSeals->owner)) {
    		$relation_class = $this->owner->getSealRelationEntityClassName();
    		$relation = new $relation_class;

	    	$sealOwner          = $app->repo('Seal')->find($opportunityMetadataSeals->owner);
	        $relation->seal     = $sealOwner;
	        $relation->owner    = $this->owner;
	        $relation->agent    = $this->opportunity->owner; //  o agente que aplica o selo (o dono da oportunidade)

            $relation->save(true);

            $seal_relations->owner = $relation;
    	}

    	$sealInstitutions = isset($opportunityMetadataSeals->institution) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->institution) : null;

    	$sealCollective = isset($opportunityMetadataSeals->collective) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->collective) : null;

        foreach($this->relatedAgents as $groupName => $relatedAgents){
            $relation = null;
        	if (trim($groupName) == 'instituicao' && isset($opportunityMetadataSeals->institution) && is_object($sealInstitutions)) {
        		$agent = $relatedAgents[0];
        		$relation = new $relation_class;
        		$relation->seal = $sealInstitutions;
        		$relation->owner = $agent;
                $relation->agent = $this->opportunity->owner;
        		$relation->save(true);
        	} elseif (trim($groupName) == 'coletivo' && isset($opportunityMetadataSeals->collective) && is_object($sealCollective)) {
        		$agent = $relatedAgents[0];
        		$relation = new $relation_class;
        		$relation->seal = $sealCollective;
        		$relation->owner = $agent;
                $relation->agent = $this->opportunity->owner;
        		$relation->save(true);
        	}
            if ($relation) {
                $seal_relations->$groupName = $relation;
            }
        }
        $app->enableAccessControl();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).setAgentsSealRelation:after", [&$opportunityMetadataSeals, &$seal_relations]);
    }

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */
    
    protected static function _getStatusesNames() {
        $statuses = parent::_getStatusesNames();

        $statuses[self::STATUS_SENT] = i::__('Pendente');
        $statuses[self::STATUS_INVALID] = i::__('Inválida');
        $statuses[self::STATUS_NOTAPPROVED] = i::__('Não selecionada');
        $statuses[self::STATUS_WAITLIST] = i::__('Suplente');
        $statuses[self::STATUS_APPROVED] = i::__('Selecionada');

        return $statuses;
    }

    /**
     * Retorna array com os nomes dos status
     *
     * @return array
     */
    public static function getStatuses() {
        $statuses[self::STATUS_DRAFT] = 'Draft';
        $statuses[self::STATUS_SENT] = 'Pending';
        $statuses[self::STATUS_INVALID] = 'Invalid';
        $statuses[self::STATUS_NOTAPPROVED] = 'Notapproved';
        $statuses[self::STATUS_WAITLIST] = 'Waitlist';
        $statuses[self::STATUS_APPROVED] = 'Approved';

        return $statuses;
    }

    function setStatus(int $status) {
        $status_map = [
            self::STATUS_DRAFT => 'setStatusToDraft',
            self::STATUS_SENT => 'setStatusToSent',
            self::STATUS_INVALID => 'setStatusToInvalid',
            self::STATUS_NOTAPPROVED => 'setStatusToNotApproved',
            self::STATUS_WAITLIST => 'setStatusToWaitlist',
            self::STATUS_APPROVED => 'setStatusToApproved',
        ];

        if($method = $status_map[$status] ?? false) {
            $this->$method(false);
        } else {
            throw new Exception(i::__('Status inválido para inscrição'));
        }
    }

    function setStatusToDraft($flush = true){
        $this->_setStatusTo(self::STATUS_DRAFT, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(draft)');
    }

    function setStatusToApproved($flush = true){
        $this->_setStatusTo(self::STATUS_APPROVED, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(approved)');
    }

    function setStatusToNotApproved($flush = true){
        $this->_setStatusTo(self::STATUS_NOTAPPROVED, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(notapproved)');
    }

    function setStatusToWaitlist($flush = true){
        $this->_setStatusTo(self::STATUS_WAITLIST, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(waitlist)');
    }

    function setStatusToInvalid($flush = true){
        $this->_setStatusTo(self::STATUS_INVALID, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(invalid)');
    }

    function forceSetStatus(Registration $registration, $status = "pendent") {
        if ("pendent" === $status) {
            $_status = self::STATUS_SENT;
        } else if ("invalid" === $status) {
            $_status = self::STATUS_INVALID;
        } else {
            return;
        }

        $app = App::i();
        $app->disableAccessControl();
        $registration->status = $_status;
        $registration->save(true);
        $app->enableAccessControl();
    }

    function setStatusToSent($flush = true){
        $this->_setStatusTo(self::STATUS_SENT, $flush);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(sent)');
    }

    function send($update_sent_timestamp = true, $flush = true){
        $this->checkPermission('send');
        $app = App::i();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).send:before");

        $app->disableAccessControl();

        // copies agents data including configured private

        $this->status = self::STATUS_SENT;
        if($update_sent_timestamp){
            $this->sentTimestamp = new \DateTime;
        }
        $this->agentsData = $this->_getAgentsData();
        $this->_spaceData = $this->_getSpaceData();
        $this->save($flush);

        $app->enableAccessControl();
        
        $app->enqueueEntityToPCacheRecreation($this);

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).send:after");

    }

    function cleanMaskedRegistrationFields(){
        $app = App::i();
        $fieldsValues = $this->getMetadata();

        $fieldsConfigurations = $this->opportunity->registrationFieldConfigurations;

        $app->disableAccessControl();
        foreach ($fieldsValues as $fieldName => $value){

            foreach ($fieldsConfigurations as $fieldConf){

                if('field_'.$fieldConf->id  === $fieldName){
                    switch ($fieldConf->getFieldTypeDefinition()->slug){
                        case 'cpf':
                        case 'cnpj':
                            $value = preg_replace( '/[^0-9]/', '', (string) $value );
                            $this->setMetadata($fieldName, $value);
                            break;
                    }
                }
            }
        }
        $app->enableAccessControl();
    }

    /**
     * Verifica se uma etapa deve ser exibida com base nas categorias, 
     * faixas e tipos de proponente definidos na configuração do etapa.
     *
     * @param RegistrationStep $step A etapa a ser verificada.
     * @return bool Verdadeiro se a etapa deve ser exibida, falso caso contrário.
     */
    function isStepVisible(RegistrationStep $step): bool {
        $metadata = (array) $step->metadata;
        $conditional = $metadata['conditional'] ?? null;

        if (!$conditional) {
            return true;
        }

        if (!empty($conditional['categories'])) {
            if (!empty($this->category) && !in_array($this->category, $conditional['categories'])) {
                return false;
            }
        }

        if (!empty($conditional['proponentTypes'])) {
            if (!empty($this->proponentType) && !in_array($this->proponentType, $conditional['proponentTypes'])) {
                return false;
            }
        }
        
        if (!empty($conditional['ranges'])) {
            if (!empty($this->range) && !in_array($this->range, $conditional['ranges'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se um campo deve ser exibido com base nas categorias, 
     * faixas e tipos de proponente definidos na configuração do campo.
     *
     * @param RegistrationFieldConfiguration|RegistrationFileConfiguration $field O campo a ser verificado.
     * @return bool Verdadeiro se o campo deve ser exibido, falso caso contrário.
     */
    function isFieldVisisble(RegistrationFieldConfiguration|RegistrationFileConfiguration $field): bool
    {
        $opportunity = $this->opportunity;

        $use_category = (bool) $opportunity->registrationCategories;
        $use_range = (bool) $opportunity->registrationRanges;
        $use_proponent_types = (bool) $opportunity->registrationProponentTypes;

        $field_categories = $field->categories ?: [];
        $field_ranges = $field->registrationRanges ?: [];
        $field_proponent_types = $field->proponentTypes ?: [];

        if ($use_category && count($field_categories) > 0 && !in_array($this->category, $field_categories)) {
            return false;
        }

        if ($use_range && count($field_ranges) > 0 && !in_array($this->range, $field_ranges)) {
            return false;
        }

        if ($use_proponent_types && count($field_proponent_types) > 0 && !in_array($this->proponentType, $field_proponent_types)) {
            return false;
        }

        if (!$this->isStepVisible($field->step)) {
            return false;
        }

        if(!$field->conditional || !$field->conditionalField) {
            return true;
        }

        $_fied_name = $field->conditionalField;
        $_fied_value = $field->conditionalValue;
        

        // Busca o campo pai (pode estar na lista de campos ou de arquivos)
        $parentField = $this->findParentField($_fied_name);

        // Se o pai existe e não está visível, este também não está
        if ($parentField && !$this->isFieldVisisble($parentField)) {
            return false;
        }

        // Se o pai estiver visível, checa o valor esperado
        if ($_fied_name === 'appliedForQuota') {
            return $opportunity->enableQuotasQuestion && $this->appliedForQuota;
        }

        if (is_array($this->$_fied_name)) {
            return in_array($_fied_value, $this->$_fied_name);
        }

        return $this->$_fied_name == $_fied_value;
    }

    /**
     * Localiza e retorna a configuração de campo ou arquivo correspondente ao nome informado.
     * 
     * Pode ser utilizada para identificar o campo pai de um campo condicional,
     * permitindo a verificação recursiva de dependências em cascata.
     *
     * @param string $fieldName Nome do campo ou grupo de arquivos a ser localizado.
     *
     * @return RegistrationFieldConfiguration|RegistrationFileConfiguration|null
     *         Retorna a configuração correspondente ou null caso não seja encontrada.
     */
    private function findParentField(string $fieldName): RegistrationFieldConfiguration|RegistrationFileConfiguration|null
    {
        // Procura entre os campos normais
        foreach ($this->opportunity->registrationFieldConfigurations as $f) {
            if ($f->fieldName === $fieldName) {
                return $f;
            }
        }
        // Procura entre os arquivos
        foreach ($this->opportunity->registrationFileConfigurations as $f) {
            if ($f->fileGroupName === $fieldName || $f->fieldName === $fieldName) {
                return $f;
            }
        }
        return null;
    }

    function getValidationErrors() {
        if($previous_phase_opportunity = $this->opportunity->previousPhase){
            $previous_phase_opportunity->unregisterRegistrationMetadata(include_previous_phases:true);
        }

        if($this->isNew()) {
            $errors = parent::getValidationErrors();
        } else {
            $errors = [...parent::getValidationErrors(), ...$this->getSendValidationErrors()];
        }

        return $errors;
    }

    function getSendValidationErrors(string $field_prefix = 'field_', $file_prefix = 'file_', $agent_prefix = 'agent_'){
        $app = App::i();

        $errorsResult = [];

        $opportunity = $this->opportunity;

        $this->registerFieldsMetadata();

        $metadata_definitions = $app->getRegisteredMetadata('MapasCulturais\Entities\Registration');

        $use_category = (bool) $opportunity->registrationCategories;
        $use_range = (bool) $opportunity->registrationRanges;
        $use_proponent_types = (bool) $opportunity->registrationProponentTypes;

        if($use_range && !$this->range) {
            $errorsResult['range'] = [i::__('Faixa é um campo obrigatório.')];
        }

        if($use_proponent_types && !$this->proponentType) {
            $errorsResult['proponentType'] = [i::__('Tipo de proponente é um campo obrigatório.')];
        }

        if($use_category && !$this->category){
            $errorsResult['category'] = [i::__('Categoria é um campo obrigatório.')];
        }

        if($this->opportunity->requestAgentAvatar && !array_key_exists("avatar", $this->owner->files)){
            $errorsResult['avatar'] = [sprintf(\MapasCulturais\i::__('A imagem avatar do agente "%s" é obrigatório.'),$this->owner->name)];
        }

        $definitionsWithAgents = $this->_getDefinitionsWithAgents();
        
        // validate agents
        $proponent_type_to_group_map = $app->config['registration.proponentTypesToAgentsMap'];
        $group_to_proponent_type_map = [];
        foreach($proponent_type_to_group_map as $proponent_type => $group){
            $group_to_proponent_type_map[$group] = $group_to_proponent_type_map[$group] ?? []; 
            $group_to_proponent_type_map[$group][] = $proponent_type;
        }
        
        foreach($definitionsWithAgents as $def){
            $group_name = $def->agentRelationGroupName;
            $errors = [];

            // @TODO: validar o tipo do agente

            if($def->use === 'required'){
                if(!$def->agent){
                    if ($def->agentRelationGroupName == 'owner') {
                        $errors[] = i::__('O agente responsável é obrigatório.');
                    } else {
                        $group_proponent_types = $group_to_proponent_type_map[$group_name] ?? [];
                        if (in_array($this->proponentType, $group_proponent_types)) {
                            $proponent_type = $this->proponentType;
                            $proponent_agent_relation = $this->opportunity->proponentAgentRelation;
                            if ($proponent_agent_relation->$proponent_type ?? false) {
                                $errors[] = sprintf(i::__('O agente "%s" é obrigatório.'), $def->label);
                            }
                        }    
                    }
                }
            }

            if($def->agent){

                if($def->relationStatus < 0){
                    $errors[] = sprintf(i::__('O agente %s ainda não confirmou sua participação neste projeto.'), $def->agent->name);
                }else{
                    if($def->agent->type->id !== $def->type){
                        $typeDescription = $app->getRegisteredEntityTypeById($def->agent, $def->type)->name;
                        $errors[] = sprintf(i::__('Este agente deve ser do tipo "%s".'), $typeDescription);
                    }
                }
            }

            if($errors){
                $errorsResult[$agent_prefix . $def->agentRelationGroupName] = [implode(' ', $errors)];
            }
        }

        // validate space
        $opMeta = $app->repo('OpportunityMeta')->findOneBy(['owner' =>  $opportunity->id, 'key' => 'useSpaceRelationIntituicao']);
        if(!empty($opMeta) ){
            $isSpaceRelationRequired = $opMeta->value;
        }        
        $spaceDefined = $this->getSpaceRelation();
       
        if(isset($isSpaceRelationRequired)){
            if($isSpaceRelationRequired === 'required'){
                if($spaceDefined === null) {
                    $errorsResult['space'] = [i::__('O espaço é obrigatório')];
                }
            }
            if($isSpaceRelationRequired === 'required' || $isSpaceRelationRequired === 'optional'){
                //Espaço não autorizado
                if( $spaceDefined && $spaceDefined->status < 0){
                    $errorsResult['space'] = [i::__('O espaço vinculado a esta inscrição aguarda autorização do responsável')];
                }
            }
        }       

        // validate attachments
        foreach($opportunity->registrationFileConfigurations as $rfc){

            if(!$this->isFieldVisisble($rfc)){
                continue;
            }

            $field_required = $rfc->required;

            $errors = [];
            if($field_required){
                if(!isset($this->files[$rfc->fileGroupName])){
                    $errors[] = i::__('O arquivo é obrigatório.');
                }
            }
            if($errors){
                $errorsResult[$file_prefix . $rfc->id] = $errors;
            }
        }

        // validate fields
        foreach ($opportunity->registrationFieldConfigurations as $field) {

            if (!$this->isFieldVisisble($field)) {
                continue;
            }

            $metadata_definition = isset($metadata_definitions[$field->fieldName]) ? 
                $metadata_definitions[$field->fieldName] : null;


            $field_name = $field_prefix . $field->id;
            $field_required = $field->required;

            $errors = [];
            $prop_name = $field->getFieldName();
            $val = $this->$prop_name;

            $empty = false;

            if(is_array($val)){
                if(count($val) === 0) {
                    $empty = true;
                }
            } else if (is_object($val)){
                if($val == (object) []) {
                    $empty = true;
                }
            } else {
                $empty = trim((string) $val) === '';
            }

            if ($empty) {
                if($field_required) {
                    $errors[] = i::__('O campo é obrigatório.');
                }
            } else {
                
                $validations = isset($metadata_definition->config['validations']) ? 
                    $metadata_definition->config['validations']: [];

                foreach($validations as $validation => $error_message){
                    if(strpos($validation,'v::') === 0){

                        $validator = str_replace('v::', 'Respect\Validation\Validator::', $validation);
                        $validator .= "->validate(\$val)";
                        
                        eval("\$ok = $validator;");

                        if (!$ok) {
                            $errors[] = $error_message;
                        }
                    }
                }
            }

            if ($errors) {
                $errorsResult[$field_name] = $errors;
            }
        }
        // @TODO: validar o campo projectName

        if($opportunity->projectName == 2 && !$this->projectName){
            $errorsResult['projectName'] = [i::__('O nome do projeto é obrigatório.')];
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.sendValidationErrors", [&$errorsResult]);

        return $errorsResult;
    }

    function registerFieldsMetadata() {
        $this->opportunity->registerRegistrationMetadata();
    }

    function unregisterFieldsMetadata() {
        $this->opportunity->unregisterRegistrationMetadata();
    }

    protected function skipFieldsEntityRelations(){
        return [
            "@entityType",
            "opportunityTabName",
            "useOpportunityTab",
            "event_importer_processed_file",
            "event_importer_files_processed",
            "sentNotification",
            "controllerId",
            "deleteUrl",
            "editUrl",
            "singleUrl",
            "lockedFields",
            "currentUserPermissions"
        ];
    }

    function _getSpaceData(){
        
        $spaceRelation =  $this->getSpaceRelation(); 

        $exportData = [];
       
        if($spaceRelation && $spaceRelation->status == \MapasCulturais\Entities\SpaceRelation::STATUS_ENABLED){
            $space = $spaceRelation->space;
            $result =  $space->jsonSerialize();
            $skip_fields = $this->skipFieldsEntityRelations();

            foreach($skip_fields as $field ){
                if(in_array($field,array_keys($result))){
                    unset($result[$field]);
                }
            }

            foreach($result as $key => &$value) {
                if($value instanceof \MapasCulturais\Entity) {
                    $value = $value->id;
                }
            }
            
            $exportData = $result;
        }       
        
        return $exportData;
      
    }

    function _getAgentsData(){
        $exportData = [];

        $app = App::i();

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).getAgentsData:before");

        $skip_fields = $this->skipFieldsEntityRelations();
        foreach($this->_getAgentsWithDefinitions() as $agent){
            $result =  $agent->jsonSerialize();
            
            foreach($skip_fields as $field ){
                if(in_array($field,array_keys($result))){
                    unset($result[$field]);
                }
            }

            foreach($result as $key => &$value) {
                if($value instanceof \MapasCulturais\Entity) {
                    $value = $value->id;
                }
            }

            $exportData[$agent->definition->agentRelationGroupName] = $result;
        }

        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}).getAgentsData:after", [&$exportData]);
        
        return $exportData;
    }

    static function getPCachePermissionsList() {
        $permissions = parent::getPCachePermissionsList();

        $permissions[] = 'viewUserEvaluation';
        $permissions[] = 'evaluateOnTime';
        
        return $permissions;
    }

    protected function canUserCreate($user){
        if($user->is('guest')){
            return false;
        }

        if(!$this->opportunity instanceof Opportunity) {
             return false;
        }

        return $this->genericPermissionVerification($user);
    }

    protected function canUserView($user){
        if($user->is('guest')){
            return false;
        }

        if(!in_array($this->opportunity->status, [-1,1,-20]) && !$this->opportunity->canUser('@control', $user)){
            return false;
        }

        if($this->opportunity->isUserAdmin($user)){
            return true;
        }
     
        if($this->canUser('@control', $user)){                      
            return true;
        }

        if($this->opportunity->canUser('@control', $user)){
            return true;
        }

        if ($this->getEvaluationMethod()) {
            $can = $this->opportunity->evaluationMethodConfiguration && $this->getEvaluationMethod()->canUserEvaluateRegistration($this, $user);
        } else {
            $can = false;
        }

        $exclude_list = $this->getValuersExcludeList();
        $include_list = $this->getValuersIncludeList();

        if($can && in_array($user->id, $exclude_list)){
            $can = false;
        }

        if(!$can && in_array($user->id, $include_list)){
            $can = true;
        }

        if(!$can) {
            foreach($this->getRelatedAgents() as $agents){
                foreach($agents as $agent){
                    if($agent->canUser('@control', $user)){
                        $can = true;
                    }
                }
            }
        }

        if(!$can && $this->canUserViewUserEvaluation($user)){
            $can = true;
        }

        if(!$can) {
            $app = App::i();
            
            $evaluation = $app->repo('RegistrationEvaluation')->findOneBy([
                'registration' => $this,
                'user' => $user
            ]);
            $can = isset($evaluation);
        }
        
        return $can;
    }

    protected function canUserRemove($user){
        if ($user->is('guest')) {
            return false;
        }

        if ($user->is('admin')) {
            return true;
        }

        $now = new \DateTime();

        if ($this->opportunity->canUser('@control', $user) && $this->opportunity->registrationFrom < $now ) {
            return true;
        }

        if ($this->getOwnerUser()->equals($user) && $this->status < 1) {
            return true;
        }

        return false;
    }
    
    protected function canUserChangeStatus($user){
        if($user->is('guest')){
            return false;
        }

        return $this->status > 0 && $this->opportunity->canUser('@control', $user);
    }

    protected function canUserSend($user){

        if($user->is('guest')){
            return false;
        }

        if($this->status != self::STATUS_DRAFT) {
            return false;
        }

        if($this->opportunity->canUser('@control', $user)){
            return true;
        }

        if(!in_array($this->opportunity->status, [-1,1,-20]) && !$this->opportunity->canUser('@control', $user)){
            return false;
        }


        if($this->opportunity->isUserAdmin($user)){
            return true;
        }

        if(!$this->opportunity->isRegistrationOpen()){
            return false;
        }
       
        if($this->isUserAdmin($user)){
            return true;
        }

        return $this->canUser('@control');
    }

    protected function canUserSendEditableFields(User | GuestUser $user):bool {
        if($this->status == self::STATUS_DRAFT) {
            return false;
        }

        if (!$this->canUser('@control')) {
            return false;
        }

        if ($this->editableUntil < new DateTime() || $this->editSentTimestamp) {
            return false;
        }

        return true;
    }

    protected function canUserModify($user){
        if($this->status !== self::STATUS_DRAFT){
            return false;
        }else{

            if(!$this->opportunity->isRegistrationOpen() && !$this->opportunity->canUser('@control')) {
                return false;
            }

            if($this->genericPermissionVerification($user)){
                return true;
            }
            
            return false;
        }
    }

    protected function canUserCreateSpaceRelation($user){
        $result = $user->is('admin') || $this->userHasControl($user);
        return $result;
    }

    function canUserRemoveSpaceRelation($user){
        $result = $user->is('admin') || $this->userHasControl($user);
        return $result;
    }

    protected function canUserEvaluateOnTime($user){
        if($user->is('guest')){
            return false;
        }
        
        $evaluation_method_configuration = $this->getEvaluationMethodConfiguration();
        
        if (!$evaluation_method_configuration) {
            return false;
        }

        $is_valuer = isset($this->valuers[$user->id]);

        if(!$is_valuer){
            return false;
        }
        
        $result = $this->canUserViewUserEvaluation($user, true);

        return $result;
    }

    protected function canUserEvaluate($user){
        $emc = $this->opportunity->evaluationMethodConfiguration;
        
        if (!$emc) {
            return false;
        }
        
        if(new DateTime('now') < $emc->evaluationFrom || new DateTime('now') > $emc->evaluationTo){
            return false;
        }

        $can = $this->canUserEvaluateOnTime($user);

        $evaluation = $this->getUserEvaluation($user);
        
        $evaluation_sent = false;

        if($evaluation){
            $evaluation_sent = $evaluation->status === RegistrationEvaluation::STATUS_SENT;
        }
        return $can && !$evaluation_sent;
    }

    protected function canUserViewUserEvaluation($user, $skip_opportunity_control = false){
        if($this->status <= 0 || $user->is('guest') || !$this->evaluationMethod) {
            return false;
        }
        $app = App::i();

        $em = $this->evaluationMethod;

        if (!$skip_opportunity_control && $this->opportunity->canUser('@control', $user)) {
            return true;
        }

        $can = $em->canUserEvaluateRegistration($this, $user);

        $exclude_list = $this->getValuersExcludeList();
        $include_list = $this->getValuersIncludeList();
        
        if($can && in_array($user->id, $exclude_list)){
            $can = false;
        }

        if(!$can && in_array($user->id, $include_list)){
            $can = true;
        }

        if (!$can) {
            $evaluation = $app->repo('RegistrationEvaluation')->findOneBy([
                'registration' => $this,
                'user' => $user
            ]);
            $can = isset($evaluation);
        }

        return $can;
    }

    protected function canUserViewConsolidatedResult($user){
        if ($this->getEvaluationMethod()) {
            if($this->status <= 0 || !$this->opportunity->evaluationMethodConfiguration) {
                return false;
            }
        } else {
            return false;
        }

        return $this->getEvaluationMethod()->canUserViewConsolidatedResult($this, $user);
    }

    protected function canUserViewPrivateData($user){
        $can = $this->__canUserViewPrivateData($user) || $this->opportunity->canUser('@control', $user);
        
        // @todo fazer essa verificação por meio de hook no módulo de fases (#1659)
        $canUserEvaluateNextPhase = false;
        if($this->getMetadata('nextPhaseRegistrationId') !== null) {
            $next_phase_registration = App::i()->repo('Registration')->find($this->getMetadata('nextPhaseRegistrationId'));
            if ($next_phase_registration && $next_phase_registration->evaluationMethod) {
                $canUserEvaluateNextPhase = $next_phase_registration->evaluationMethod->canUserEvaluateRegistration($next_phase_registration, $user);    
            }            
        }

        $em = $this->evaluationMethod;
        $canUserEvaluate = $em && $em->canUserEvaluateRegistration($this, $user) || $canUserEvaluateNextPhase;

        return $can || $canUserEvaluate;
    }
    
    function getExtraEntitiesToRecreatePermissionCache(): array {
        if(!$this->id) {
            return [];
        }

        $result = [];

        if ($previous_phase = $this->previousPhase) {
            $result[]= $previous_phase;
        }

        if($this->opportunity->isAppealPhase) {
            $parent_registration = $this->repo()->findOneBy([
                'number' => $this->number,
                'opportunity' => $this->opportunity->parent
            ]);
            $result[] = $parent_registration;
        }

        return $result;
    }

    function getExtraPermissionCacheUsers(){
        $opportunity = $this->opportunity;
        $evaluationMethodConfiguration = $this->evaluationMethodConfiguration;

        if($this->status > 0 && $evaluationMethodConfiguration) {
            $valuers = $evaluationMethodConfiguration->getUsersWithControl();
        } else {
            $valuers = [];
        }

        $users = array_merge(
            $valuers, 
            $opportunity->getExtraPermissionCacheUsers(), 
            $opportunity->getUsersWithControl()
        );
        
        if($this->nextPhaseRegistrationId){
            $next_phase_registration = App::i()->repo('Registration')->find($this->nextPhaseRegistrationId);
            if($next_phase_registration){
                $_users = $next_phase_registration->getExtraPermissionCacheUsers();
                if($_users){
                    $users = array_merge($users, $_users);
                }
            }
        }
        
        return array_unique($users);
    }

    /**
     * Returns the Evaluation Method Definition Object
     * @return \MapasCulturais\Definitions\EvaluationMethod
     */
    public function getEvaluationMethodDefinition() {
        return $this->opportunity->getEvaluationMethodDefinition();
    }

    /**
     * Returns the Evaluation Method Configuration
     * @return EvaluationMethodConfiguration
     */
    public function getEvaluationMethodConfiguration() {
        return $this->opportunity ? $this->opportunity->evaluationMethodConfiguration : null;
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        if ($this->opportunity) {
            return $this->opportunity->getEvaluationMethod();
        } else {
            return null;
        }
    }

    /**
     *
     * @param \MapasCulturais\Entities\User $user
     * @return \MapasCulturais\Entities\RegistrationEvaluation
     */
    function getUserEvaluation(?\MapasCulturais\UserInterface $user = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
        }
        if ($user->is('guest')) {
            return null;
        }

        $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy([
            'registration' => $this,
            'user' => $user
        ]);

        return $evaluation;
    }

    function saveEvaluation(RegistrationEvaluation $evaluation, array $data, $evaluation_status = null){
        $evaluation->evaluationData = $data;

        if(!is_null($evaluation_status)){
            $evaluation->status = $evaluation_status;
        }

        $evaluation->save(true);
    }

    function saveUserEvaluation(array $data, ?User $user = null, $evaluation_status = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
        }

        $lockname = "save-user-evauation--{$this->id}--{$user->id}";

        $app->lock($lockname, 2);

        $evaluation = $this->getUserEvaluation($user);
        if(!$evaluation){
            $evaluation = new RegistrationEvaluation;
            $evaluation->user = $user;
            $evaluation->registration = $this;
        }

        $this->saveEvaluation($evaluation, $data, $evaluation_status);

        $app->unlock($lockname);

        return $evaluation;
    }

    public function evaluationUserChangeStatus($user, Registration $registration, $status) {
        if ($registration->canUser('evaluate', $user)) {
            $method_name = 'setStatusTo' . ucfirst($status);

            if (!method_exists($registration, $method_name)) {
                $this->errorJson('Invalid status name');
                return false;
            } else {
                $app = App::i();
                $app->disableAccessControl();
                $registration->$method_name();
                $app->enableAccessControl();

                return true;
            }
        }

        return false;
    }

    protected function canUserModifyValuers($user){
        return $this->opportunity->canUser('@control', $user);
    }

     //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ 
        parent::prePersist($args); 
    }
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
