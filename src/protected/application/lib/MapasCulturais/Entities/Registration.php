<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Registration
 * @property-read \MapasCulturais\Entities\Agent $owner The owner of this registration
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
    	Traits\EntitySealRelation;


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
     * @ORM\Column(name="category", type="string", length=255)
     */
    protected $category;


    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    protected $project;


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
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DRAFT;


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


    function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
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
            'project' => $this->project->simplify('id,name,singleUrl'),
            'number' => $this->number,
            'category' => $this->category,
            'owner' => $this->owner->simplify('id,name,singleUrl'),
            'agentRelations' => [],
            'files' => [],
            'singleUrl' => $this->singleUrl,
            'editUrl' => $this->editUrl
        ];

        foreach($this->__metadata as $meta){
            if(substr($meta->key, 0, 6) === 'field_'){
                $key = $meta->key;
                $json[$meta->key] = $this->$key;
            }
        }

        if($this->project->publishedRegistrations || $this->project->canUser('@control')) {
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
            $json['owner'] = null;
        }

        return $json;
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
            $registrationCount = $this->repo()->countByProjectAndOwner($this->project, $this->owner);
            $limit = $this->project->registrationLimitPerOwner;
            if($limit > 0 && $registrationCount >= $limit){
                return false;
            }
        }
        return true;
    }

    function setProjectId($id){
        $agent = App::i()->repo('Project')->find($id);
        $this->project = $agent;
    }

    function getSingleUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
    }

    function getEditUrl(){
        return App::i()->createUrl('registration', 'view', [$this->id]);
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
        foreach($this->relatedAgents as $groupName => $relatedAgents){
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
            $meta_val = $this->project->$metadata_name;
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

    function getNumber(){
        return 'on-' . $this->id;
    }

    function setStatus(){
        // do nothing
    }

    protected function _setStatusTo($status){
        if($this->status === self::STATUS_DRAFT && $status === self::STATUS_SENT){
            $this->checkPermission('send');
        }else{
            $this->checkPermission('changeStatus');
        }

		if($status === self::STATUS_APPROVED) {
			$this->setAgentsSealRelation();
		}

        $app = App::i();
        $app->disableAccessControl();
        $this->status = $status;
        $this->save(true);
        $app->enableAccessControl();
    }

    function setAgentsSealRelation() {
    	$app = App::i();
    	$app->disableAccessControl();

    	/*
    	 * Related Seals added to registration to Agents (Owner/Institution/Collective) atributed on aproved registration
    	 */
    	$projectMetadataSeals = $this->project->registrationSeals;
    	//eval(\Psy\sh());
    	//die;

    	if(isset($projectMetadataSeals->owner)) {
    		$relation_class = $this->owner->getSealRelationEntityClassName();
    		$relation = new $relation_class;

	    	$sealOwner			= App::i()->repo('Seal')->find($projectMetadataSeals->owner);
	        $relation->seal		= $sealOwner;
	        $relation->owner	= $this->owner;
	    	$relation->save(true);
    	}

    	$sealInstitutions	= isset($projectMetadataSeals->institution)? App::i()->repo('Seal')->find($projectMetadataSeals->institution):null;
    	$sealCollective		= isset($projectMetadataSeals->collective)? App::i()->repo('Seal')->find($projectMetadataSeals->collective):null;

        foreach($this->relatedAgents as $groupName => $relatedAgents){
        	if (trim($groupName) == 'instituicao' && isset($projectMetadataSeals->institution) && is_object($sealInstitutions)) {
        		$agent = $relatedAgents[0];
        		$relation = new $relation_class;
        		$relation->seal = $sealInstitutions;
        		$relation->owner = $agent;
        		$relation->save(true);
        	} elseif (trim($groupName) == 'coletivo' && isset($projectMetadataSeals->collective) && is_object($sealCollective)) {
        		$agent = $relatedAgents[0];
        		$relation = new $relation_class;
        		$relation->seal = $sealCollective;
        		$relation->owner = $agent;
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

    function setStatusToSent(){
        $this->_setStatusTo(self::STATUS_SENT);
        App::i()->applyHookBoundTo($this, 'entity(Registration).status(sent)');
    }

    function send(){
        $this->checkPermission('send');
        $app = App::i();

        $app->disableAccessControl();

        // copies agents data including configured private

        // creates zip archive of all files
        if($this->files){
            $app->storage->createZipOfEntityFiles($this, $fileName = $this->number . ' - ' . uniqid() . '.zip');
        }

        $this->status = self::STATUS_SENT;
        $this->sentTimestamp = new \DateTime;
        $this->_agentsData = $this->_getAgentsData();
        $this->save(true);
        $this->project->save(true);
        $app->enableAccessControl();
    }

    function getSendValidationErrors(){
        $app = App::i();

        $errorsResult = [];

        $project = $this->project;

        $use_category = (bool) $project->registrationCategories;

        if($use_category && !$this->category){
            $errorsResult['category'] = [sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), $project->registrationCategTitle)];
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
                    if(count($erroredProperties) === 1){
                        $errors[] = sprintf(\MapasCulturais\i::__('O campo "%s" é obrigatório.'), $erroredProperties[0]);
                    }elseif(count($erroredProperties) > 1){
                        $errors[] = sprintf(\MapasCulturais\i::__('Os campos "%s" são obrigatórios.'), implode(', ', $erroredProperties));
                    }
                }
            }

            if($errors){
                $errorsResult['registration-agent-' . $def->agentRelationGroupName] = implode(' ', $errors);
            }

        }

        // validate attachments
        foreach($project->registrationFileConfigurations as $rfc){

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
        foreach ($project->registrationFieldConfigurations as $field) {

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
                        $validation = str_replace('v::', 'MapasCulturais\Validator::', $validation);

                        eval("\$ok = {$validation}->validate(\$this->{$prop_name});");

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

        return $errorsResult;
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

        if($this->project && !$this->project->useRegistrations){
            return false;
        }

        return $this->genericPermissionVerification($user);
    }

    protected function canUserView($user){
        if($user->is('guest')){
            return false;
        }

        if($user->is('admin')){
            return true;
        }

        if($this->canUser('@control', $user)){
            return true;
        }

        if($this->project->canUser('@control', $user)){
            return true;
        }

        foreach($this->getRelatedAgents() as $agents){
            foreach($agents as $agent){
                if($agent->canUser('@control', $user)){
                    return true;
                }
            }
        }

        return false;
    }

    protected function canUserChangeStatus($user){
        if($user->is('guest')){
            return false;
        }

        return $this->status > 0 && $this->project->canUser('@control', $user);
    }

    protected function canUserSend($user){
        if($user->is('guest')){
            return false;
        }

        if($user->is('superAdmin')){
            return true;
        }

        if(!$this->project->isRegistrationOpen()){
            return false;
        }

        if($this->getSendValidationErrors()){
            return false;
        }

        if($user->is('admin')){
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
