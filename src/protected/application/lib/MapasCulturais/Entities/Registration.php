<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Registration
 * @property-read \MapasCulturais\Entities\Agent $owner The owner of this registration
 * @property-read \MapasCulturais\Entities\Opportunity $opportunity
 * 
 * @property array valuersExcludeList
 * @property array valuersIncludeList
 * 
 * @property string $category
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
        Traits\EntitySealRelation {
            Traits\EntityMetadata::canUserViewPrivateData as __canUserViewPrivateData;
        }


    const STATUS_SENT = self::STATUS_ENABLED;
    const STATUS_APPROVED = 10;
    const STATUS_WAITLIST = 8;
    const STATUS_NOTAPPROVED = 3;
    const STATUS_INVALID = 2;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"default": "pseudo_random_id_generator()"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="MapasCulturais\DoctrineMappings\RandomIdGenerator")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=24, nullable=true)
     */
    protected $number;

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
     *   @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id")
     * })
     */
    protected $opportunity;


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
     * @ORM\Column(name="agents_data", type="json_array", nullable=true)
     */
    protected $_agentsData = [];
    
    /**
     * @var integer
     *
     * @ORM\Column(name="consolidated_result", type="string", length=255, nullable=true)
     */
    protected $consolidatedResult = self::STATUS_DRAFT;
    
    /*
     * @var array
     *
     * @ORM\Column(name="space_data", type="json_array", nullable=true)
     */
    protected $_spaceData = [];

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DRAFT;

    
    /**
     * @var integer
     *
     * @ORM\Column(name="valuers_exceptions_list", type="text", nullable=false)
     */
    protected $__valuersExceptionsList = '{"include": [], "exclude": []}';



    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationMeta", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__metadata = [];

    /**
     * @var \MapasCulturais\Entities\RegistrationFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationFile", fetch="EXTRA_LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationPermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**

     * @var \MapasCulturais\Entities\RegistrationAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\RegistrationSpaceRelation[] Space Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationSpaceRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__spaceRelation;

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
     *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $subsite;


    public $preview = false;

    protected static $hooked = false;

    function __construct() {
        $this->owner = App::i()->user->profile;
       
        if(!self::$hooked){
            self::$hooked = true;
            App::i()->hook('entity(' . $this->getHookClassPath() . ').randomId', function($id){
                if(!$this->number){
                    $this->number = 'on-' . $id;
                }
            });
        }
        parent::__construct();
    }

    function save($flush = false){
        parent::save($flush);
        $app = App::i();
        $opportunity = $this->opportunity;

        // cache utilizado pelo endpoint findEvaluations
        $app->mscache->delete("api:opportunity:{$opportunity->id}:registrations");
    }

    function getSingleUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
    }

    function getEditUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
    }

    
    function consolidateResult($flush = false){
        $app = App::i();
        
        $is_access_control_enabled = $app->isAccessControlEnabled();
        if($is_access_control_enabled){
            $app->disableAccessControl();
        }
        
        $em = $this->getEvaluationMethod();
        
        $this->consolidatedResult = $em->getConsolidatedResult($this);
        
        $this->save($flush);
        
        if($is_access_control_enabled){
            $app->enableAccessControl();
        }
    }

    static function isPrivateEntity(){
        return true;
    }

    static function getValidations() {
        return [
            'owner' => [
                'required' => \MapasCulturais\i::__("O agente responsável é obrigatório."),
                '$this->validateOwnerLimit()' => \MapasCulturais\i::__('Foi excedido o limite de inscrições para este agente responsável.'),
            ]
        ];
    }
    
    function jsonSerialize() {
        $json = [
            'id' => $this->id,
            'opportunity' => $this->opportunity->simplify('id,name,singleUrl'),
            'number' => $this->number,
            'category' => $this->category,
            'owner' => $this->owner->simplify('id,name,singleUrl'),
            'agentRelations' => [],
            'files' => [],
            'singleUrl' => $this->singleUrl,
            'editUrl' => $this->editUrl
        ];

        if($this->canUser('viewConsolidatedResult')){
            $json['evaluationResultValue'] = $this->getEvaluationResultValue();
            $json['evaluationResultString'] = $this->getEvaluationResultString();
        }

        foreach($this->__metadata as $meta){
            if(substr($meta->key, 0, 6) === 'field_'){
                $key = $meta->key;
                $json[$meta->key] = $this->$key;
            }
        }

        if($this->opportunity->publishedRegistrations || $this->opportunity->canUser('@control')) {
            $json['status'] = $this->status;
        }

        if($this->canUser('view') || $this->status === self::STATUS_APPROVED || $this->status === self::STATUS_WAITLIST){
            $related_agents = $this->getRelatedAgents();


            foreach(App::i()->getRegisteredRegistrationAgentRelations() as $def){
                $json['agentRelations'][] = [
                    'label' => $def->label,
                    'description' => $def->description,
                    'agent' => isset($related_agents[$def->agentRelationGroupName]) ? $related_agents[$def->agentRelationGroupName][0]->simplify('id,name,singleUrl') : null
                ];
            }

            foreach($this->files as $group => $file){
                if($file instanceof File){
                    $json['files'][$group] = $file->simplify('id,url,name,deleteUrl');
                }
            }
        }else{
            $json = null;
        }

        return $json;
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

    function setOwnerId($id){
        $agent = App::i()->repo('Agent')->find($id);
        $this->setOwner($agent);
    }

    protected $_ownerChanged = false;

    function setOwner(Agent $agent){
        $this->_ownerChanged = true;
        $this->owner = $agent;
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
        $agent = App::i()->repo('Opportunity')->find($id);
        $this->opportunity = $agent;
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

    function getEvaluationResultValue(){
        $method = $this->getEvaluationMethod();
        return $method->getConsolidatedResult($this);
    }

    function getEvaluationResultString(){
        $method = $this->getEvaluationMethod();
        $value = $this->getEvaluationResultValue();
        return $method->valueToString($value);
    }

    function getSpaceData(){
        if(array_key_exists('acessibilidade_fisica', $this->_spaceData)){
            $this->_spaceData['acessibilidade_fisica'] = str_replace(';', ', ', $this->_spaceData['acessibilidade_fisica']);
        } 
        
        return $this->_spaceData;
    }

    function getAgentsData(){
        if($this->canUser('view')){
            return $this->_agentsData;
        }else{
            return [];
        }
    }

    function randomIdGeneratorInitialRange(){
        return 1000;
    }

    function getValuersExceptionsList(){
        return json_decode($this->__valuersExceptionsList);
    }

    protected function _setValuersExceptionsList($object){
        $this->checkPermission('modifyValuers');

        if(is_object($object) && isset($object->exclude) && is_array($object->exclude) && isset($object->include) && is_array($object->include)){
            $this->__valuersExceptionsList = json_encode($object);
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

    function getValuersIncludeList(){
        $exceptions = $this->getValuersExceptionsList();
        return $exceptions->include;
    }
    
    function getValuersExcludeList(){
        $exceptions = $this->getValuersExceptionsList();
        return $exceptions->exclude;
    }
    

    function setStatus($status){
        // do nothing
    }

    function _setStatusTo($status){
        if($this->status === self::STATUS_DRAFT && $status === self::STATUS_SENT){
            $this->checkPermission('send');
        }else{
            $this->checkPermission('changeStatus');
        }

        $app = App::i();
        $app->disableAccessControl();
        $this->status = $status;
        $this->save(true);
        $app->enableAccessControl();
        $app->enqueueEntityToPCacheRecreation($this);
        $app->enqueueEntityToPCacheRecreation($this->opportunity);
    }

    function setAgentsSealRelation() {
    	$app = App::i();
    	$app->disableAccessControl();

    	/*
    	 * Related Seals added to registration to Agents (Owner/Institution/Collective) atributed on aproved registration
    	 */
    	$opportunityMetadataSeals = $this->opportunity->registrationSeals;

    	if(isset($opportunityMetadataSeals->owner)) {
    		$relation_class = $this->owner->getSealRelationEntityClassName();
    		$relation = new $relation_class;

	    	$sealOwner          = $app->repo('Seal')->find($opportunityMetadataSeals->owner);
	        $relation->seal     = $sealOwner;
	        $relation->owner    = $this->owner;
	        $relation->agent    = $this->opportunity->owner; //  o agente que aplica o selo (o dono da oportunidade)

            $relation->save(true);
    	}

    	$sealInstitutions = isset($opportunityMetadataSeals->institution) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->institution) : null;

    	$sealCollective = isset($opportunityMetadataSeals->collective) ?
                $app->repo('Seal')->find($opportunityMetadataSeals->collective) : null;

        foreach($this->relatedAgents as $groupName => $relatedAgents){
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
        }
        $app->enableAccessControl();
    }

    function setStatusToDraft(){
        $this->_setStatusTo(self::STATUS_DRAFT);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(draft)');
    }

    function setStatusToApproved(){
        $this->_setStatusTo(self::STATUS_APPROVED);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(approved)');
    }

    function setStatusToNotApproved(){
        $this->_setStatusTo(self::STATUS_NOTAPPROVED);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(notapproved)');
    }

    function setStatusToWaitlist(){
        $this->_setStatusTo(self::STATUS_WAITLIST);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(waitlist)');
    }

    function setStatusToInvalid(){
        $this->_setStatusTo(self::STATUS_INVALID);
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

    function setStatusToSent(){
        $this->_setStatusTo(self::STATUS_SENT);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(sent)');
    }

    function send(){
        $this->checkPermission('send');
        $app = App::i();

        $_access_control_enabled = $app->isAccessControlEnabled();

        if($_access_control_enabled){
            $app->disableAccessControl();
        }

        // copies agents data including configured private

        // creates zip archive of all files
        if($this->files){
            $app->storage->createZipOfEntityFiles($this, $fileName = $this->number . ' - ' . uniqid() . '.zip');
        }

        $this->status = self::STATUS_SENT;
        $this->sentTimestamp = new \DateTime;
        $this->_agentsData = $this->_getAgentsData();
        $this->_spaceData = $this->_getSpaceData();
        // var_dump($this->_agentsData);
        // dump($this->_spaceData);
        // die();
        $this->save(true);

        if($_access_control_enabled){
            $app->enableAccessControl();
        }

        $app->enqueueEntityToPCacheRecreation($this->opportunity);
        $app->enqueueEntityToPCacheRecreation($this);
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
                            $value = preg_replace( '/[^0-9]/', '', $value );
                            $this->setMetadata($fieldName, $value);
                            break;
                    }
                }
            }
        }
        $app->enableAccessControl();
    }

    function getSendValidationErrors(){
        $app = App::i();

        $errorsResult = [];

        $opportunity = $this->opportunity;

        $use_category = (bool) $opportunity->registrationCategories;

        if($use_category && !$this->category){
            $errorsResult['category'] = [sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), $opportunity->registrationCategTitle)];
        }

        $definitionsWithAgents = $this->_getDefinitionsWithAgents();

        // validate agents
        foreach($definitionsWithAgents as $def){
            $errors = [];

            // @TODO: validar o tipo do agente

            if($def->use === 'required'){
                if(!$def->agent){
                    $errors[] = sprintf(\MapasCulturais\i::__('O agente "%s" é obrigatório.'), $def->label);
                }
            }

            if($def->agent){
                if($def->relationStatus < 0){
                    $errors[] = sprintf(\MapasCulturais\i::__('O agente %s ainda não confirmou sua participação neste projeto.'), $def->agent->name);
                }else{
                    if($def->agent->type->id !== $def->type){
                        $typeDescription = $app->getRegisteredEntityTypeById($def->agent, $def->type)->name;
                        $errors[] = sprintf(\MapasCulturais\i::__('Este agente deve ser do tipo "%s".'), $typeDescription);
                    }

                    $erroredProperties  = [];
                    foreach($def->requiredProperties as $requiredProperty){
                        $app->disableAccessControl();
                        $value = $def->agent->$requiredProperty;
                        $app->enableAccessControl();
                        if(!$value){
                            $erroredProperties[] = '{{' . $requiredProperty . '}}';
                        }
                    }
                    if(is_array($erroredProperties) && count($erroredProperties) === 1){
                        $errors[] = sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), $erroredProperties[0]);
                    }elseif(is_array($erroredProperties) && count($erroredProperties) > 1){
                        $errors[] = sprintf(\MapasCulturais\i::__('Os campos "%s" são obrigatórios.'), implode(', ', $erroredProperties));
                    }
                }
            }

            if($errors){
                $errorsResult['registration-agent-' . $def->agentRelationGroupName] = implode(' ', $errors);
            }

        }

        // validate space
        $conn = $app->em->getConnection();
        $selValue = $conn->fetchAll("SELECT * FROM opportunity_meta WHERE object_id = $opportunity->id and key = 'useSpaceRelationIntituicao'");
        if(!empty($selValue) ){
            $isSpaceRelationRequired = $selValue[0]['value'];
        }        
        $spaceDefined = $this->getSpaceRelation();
       
        if($isSpaceRelationRequired === 'required'){
            if($spaceDefined === null) {
                $errorsResult['space'] = \MapasCulturais\i::__('É obrigatório vincular um espaço com a inscrição');
            }
        }
        //dump($spaceDefined->status);
        //dump($isSpaceRelationRequired);
        //die();
        //adicionar 
        if($isSpaceRelationRequired === 'required' || $isSpaceRelationRequired === 'optional'){
            //Espaço não autorizado
            if( $spaceDefined && $spaceDefined->status < 0){
                $errorsResult['space'] = \MapasCulturais\i::__('O espaço vinculado a esta inscrição aguarda autorização do responsável');
            }
        }
        
        //|| $isSpaceRelationRequired === 'optional'
        if(!is_null($spaceDefined)){
            $app = App::i();
            $requiredSpaceProperties = $app->config['registration.spaceRelations'][0]['requiredProperties'];

            foreach($requiredSpaceProperties as $r){
                $app->disableAccessControl();
                $isEmpty = $spaceDefined;
                $app->enableAccessControl();

                if(empty($isEmpty)){
                    $errorsResult['invalidSpaceFields'] = sprintf(\MapasCulturais\i::__('Os campos "%s" são obrigatórios.'), implode(', ', $requiredSpaceProperties));
                }
            }
        }            

        // validate attachments
        foreach($opportunity->registrationFileConfigurations as $rfc){

            if($use_category && count($rfc->categories) > 0 && !in_array($this->category, $rfc->categories)){
                continue;
            }

            $errors = [];
            if($rfc->required){
                if(!isset($this->files[$rfc->fileGroupName])){
                    $errors[] = sprintf(\MapasCulturais\i::__('O arquivo "%s" é obrigatório.'), $rfc->title);
                }
            }
            if($errors){
                $errorsResult['registration-file-' . $rfc->id] = $errors;
            }
        }

        // validate fields
        foreach ($opportunity->registrationFieldConfigurations as $field) {

            if ($use_category && count($field->categories) > 0 && !in_array($this->category, $field->categories)) {
                continue;
            }

            $errors = [];

            $prop_name = $field->getFieldName();
            $val = $this->$prop_name;

            $empty = (is_string($val) && !trim($val)) || !$val;

            if ($field->required) {
                if ($empty) {
                    $errors[] = sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), $field->title);
                }
            }
            if (!$empty){
                foreach($field->getFieldTypeDefinition()->validations as $validation => $error_message){
                    if(strpos($validation,'v::') === 0){

                        $validator = str_replace('v::', '\MapasCulturais\Validator::', $validation);
                        $validator = str_replace('()', "()->validate(\"$val\")", $validator);

                        eval("\$ok = $validator;");

                        if (!$ok) {
                            $errors[] = $error_message;
                        }
                    }
                }
            }

            if ($errors) {
                $errorsResult['registration-field-' . $field->id] = $errors;
            }
        }
        // @TODO: validar o campo projectName

        if($opportunity->projectName == 2 && !$this->projectName){
            $errorsResult['projectName'] = sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), \MapasCulturais\i::__('Nome do Projeto'));
        }

        return $errorsResult;
    }    

    protected function _getSpaceData(){
        $app = App::i();

        $propertiesToExport = $app->config['registration.spaceProperties'];
        $spaceRelation =  $this->getSpaceRelation(); 

        $exportData = [];       
        if($spaceRelation && $spaceRelation->status == \MapasCulturais\Entities\SpaceRelation::STATUS_ENABLED){
            $space = $spaceRelation->space;
            foreach($propertiesToExport as $p){
                $exportData[$p] = $space->$p;
            }
        }       

        return $exportData;

       /* $app = App::i();

        $spacePropertiesToExport = $app->config['registration.spaceProperties'];
        $spaceRelation =  $this->getSpaceRelation(); 
        //$app->repo('RegistrationSpaceRelation')->findBy(['owner'=>$this, 'status'=>]);
        //dump($spacePropertiesToExport);
        //var_dump($spaceRelation->status);
       
        if($spaceRelation && $spaceRelation->status == \MapasCulturais\Entities\SpaceRelation::STATUS_ENABLED){
            $space = $spaceRelation->space;
            $exportData = [];

            foreach($spacePropertiesToExport as $p){
                $exportData[$p] = $space->$p;
            }

            return $exportData;
        }
        return null; */
    }

    protected function _getAgentsData(){
        $app = App::i();

        $propertiesToExport = $app->config['registration.propertiesToExport'];

        $exportData = [];

        foreach($this->_getAgentsWithDefinitions() as $agent){
            $exportData[$agent->definition->agentRelationGroupName] = [];

            foreach($propertiesToExport as $p){
                $exportData[$agent->definition->agentRelationGroupName][$p] = $agent->$p;
            }
        }

        return $exportData;
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

        if($this->opportunity->isUserAdmin($user)){
            return true;
        }

        if($this->canUser('@control', $user)){
            return true;
        }

        if($this->opportunity->canUser('@control', $user)){
            return true;
        }

        $can = $this->getEvaluationMethod()->canUserEvaluateRegistration($this, $user);

        $exclude_list = $this->getValuersExcludeList();
        $include_list = $this->getValuersIncludeList();

        if($can && in_array($user->id, $exclude_list)){
            return false;
        }

        if(!$can && in_array($user->id, $include_list)){
            return true;
        }

        foreach($this->getRelatedAgents() as $agents){
            foreach($agents as $agent){
                if($agent->canUser('@control', $user)){
                    return true;
                }
            }
        }

        if($this->canUserViewUserEvaluation($user)){
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

        if($this->opportunity->isUserAdmin($user)){
            return true;
        }

        if(!$this->opportunity->isRegistrationOpen()){
            return $this->canUser('@control') && 
                    $this->sentTimestamp && 
                    $this->opportunity->publishedRegistrations;
        }

        if($this->getSendValidationErrors()){
            return false;
        }

        if($this->isUserAdmin($user)){
            return true;
        }

        return $this->canUser('@control');
    }

    protected function canUserModify($user){
        if($this->status !== self::STATUS_DRAFT){
            return false;
        }else{
            return $this->genericPermissionVerification($user);
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
    protected function canUserEvaluate($user){
        if($this->opportunity->canUser('@control')){
            $evaluation_method_configuration = $this->getEvaluationMethodConfiguration();
            $valuers = $evaluation_method_configuration->getRelatedAgents();
            $is_valuer = false;
            
            if(isset($valuers['group-admin']) && is_array($valuers['group-admin'])){
                foreach($valuers['group-admin'] as $agent){
                    if($agent->user->id == $user->id){
                        $is_valuer = true;
                    }
                }
            }

            if(!$is_valuer){
                return false;
            }
        }

        $can = $this->canUserViewUserEvaluation($user);

        $evaluation_sent = false;

        if($this->opportunity->publishedRegistrations){
            return false;
        }

        if($user->is('guest')){
            return false;
        }

        if($evaluation = $this->getUserEvaluation($user)){
            $evaluation_sent = $evaluation->status === RegistrationEvaluation::STATUS_SENT;
        }

        return $can && !$evaluation_sent;
    }

    protected function canUserViewUserEvaluation($user){
        if($this->status <= 0) {
            return false;
        }

        $can = $this->getEvaluationMethod()->canUserEvaluateRegistration($this, $user);

        $exclude_list = $this->getValuersExcludeList();
        $include_list = $this->getValuersIncludeList();

        if($can && in_array($user->id, $exclude_list)){
            $can = false;
        }

        if(!$can && in_array($user->id, $include_list)){
            $can = true;
        }

        return $can;
    }

    protected function canUserViewConsolidatedResult($user){
        if($this->status <= 0) {
            return false;
        }

        return $this->getEvaluationMethod()->canUserViewConsolidatedResult($this, $user);
    }

    protected function canUserViewPrivateData($user){
        $can = $this->__canUserViewPrivateData($user);

        $canUserEvaluateNextPhase = false;
        if($this->getMetadata('nextPhaseRegistrationId') !== null) {
            $next_phase_registration = App::i()->repo('Registration')->find($this->getMetadata('nextPhaseRegistrationId'));
            $canUserEvaluateNextPhase = $this->getEvaluationMethod()->canUserEvaluateRegistration($next_phase_registration, $user);
        }

        $canUserEvaluate = $this->getEvaluationMethod()->canUserEvaluateRegistration($this, $user) || $canUserEvaluateNextPhase;

        return $can || $canUserEvaluate;
    }

    function getExtraPermissionCacheUsers(){
        $users = $this->getEvaluationMethodConfiguration()->getUsersWithControl();

        $users = array_merge($users, $this->opportunity->getUsersWithControl());
        
        if($this->nextPhaseRegistrationId){
            $next_phase_registration = App::i()->repo('Registration')->find($this->nextPhaseRegistrationId);
            if($next_phase_registration){
                $_users = $next_phase_registration->getExtraPermissionCacheUsers();
                if($_users){
                    $users = array_merge($users, $_users);
                }
            }
        }

        return $users;
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
     * @return \MapasCulturais\Definitions\EvaluationMethodConfiguration
     */
    public function getEvaluationMethodConfiguration() {
        return $this->opportunity->evaluationMethodConfiguration;
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        if($this->opportunity == null){
            $app = App::i();
            $app->redirect('/painel');
        }

        return $this->opportunity->getEvaluationMethod();
    }

    /**
     *
     * @param \MapasCulturais\Entities\User $user
     * @return \MapasCulturais\Entities\RegistrationEvaluation
     */
    function getUserEvaluation(User $user = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
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

    function saveUserEvaluation(array $data, User $user = null, $evaluation_status = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
        }

        $evaluation = $this->getUserEvaluation($user);
        if(!$evaluation){
            $evaluation = new RegistrationEvaluation;
            $evaluation->user = $user;
            $evaluation->registration = $this;
        }

        $this->saveEvaluation($evaluation, $data, $evaluation_status);

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
