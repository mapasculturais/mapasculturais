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
        Traits\EntityAgentRelation;

    const STATUS_SENT = self::STATUS_ENABLED;
    const STATUS_APPROVED = 10;
    const STATUS_WAITLIST = 8;
    const STATUS_NOTAPPROVED = 3;
    const STATUS_INVALID = 2;

    protected static $validations = array(
        'owner' => array(
            'required' => "O agente responsável é obrigatório."
        )
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"default": "random_id_generator('registration', 10000)"})
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
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DRAFT;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationMeta", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__metadata = array();

    function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    function jsonSerialize() {
        $json = array(
            'id' => $this->id,
            'project' => $this->project->simplify('id,name,singleUrl'),
            'number' => $this->number,
            'category' => $this->category,
            'owner' => $this->owner->simplify('id,name,singleUrl'),
            'agentRelations' => array(),
            'files' => array(),
            'singleUrl' => $this->singleUrl,
            'editUrl' => $this->editUrl
        );

        if($this->project->publishedRegistrations || $this->project->canUser('@control'))
            $json['status'] = $this->status;

        $related_agents = $this->getRelatedAgents();

        foreach(App::i()->getRegisteredRegistrationAgentRelations() as $def){
            $json['agentRelations'][] = array(
                'label' => $def->label,
                'description' => $def->description,
                'agent' => isset($related_agents[$def->agentRelationGroupName]) ? $related_agents[$def->agentRelationGroupName][0]->simplify('id,name,singleUrl') : null
            );
        }

        foreach($this->files as $group => $file){
            $json['files'][$group] = $file->simplify('id,url,name,deleteUrl');
        }

        return $json;
    }

    function setOwnerId($id){
        $agent = App::i()->repo('Agent')->find($id);
        $this->owner = $agent;
    }

    function setProjectId($id){
        $agent = App::i()->repo('Project')->find($id);
        $this->project = $agent;
    }

    /**
     *
     * @return
     */
    protected function _getRegistrationOwnerRequest(){
        return  App::i()->repo('RequestChangeOwnership')->findOneBy(array('originType' => $this->getClassName(), 'originId' => $this->id));
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
        foreach($this->relatedAgents as $groupName=>$relatedAgents){
            $agent = $relatedAgents[0];
            $agent->groupName = $groupName;
            $agent->definition = $definitions[$groupName];
            $agents[] = $agent;
        }
        return $agents;
    }


    protected function _getDefinitionsWithAgents(){
        $definitions = App::i()->getRegistrationAgentsDefinitions();
        foreach($definitions as $groupName=>$def){
            $metadata_name = $def->metadataName;
            $meta_val = $this->project->$metadata_name;

            if($meta_val === 'dontUse')
                continue;

            if($groupName === 'owner'){
                $relation = $this->owner;
                $meta_val = 'required';
            }else{
                $relation = $this->getRelatedAgents($def->agentRelationGroupName, true, true);
            }

            $definitions[$groupName]->use = $meta_val;
            $definitions[$groupName]->agent = $relation ? $relation : null;
        }
        return $definitions;
    }


    function randomIdGeneratorFormat($id){
        return intval($this->project->id . str_pad($id,5,'0',STR_PAD_LEFT));
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
        $app = App::i();
        $app->disableAccessControl();
        $this->status = $status;
        $this->save(true);
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
            $app->storage->createZipOfEntityFiles($this, $fileName = $this->number . '.zip');
        }

        $this->status = self::STATUS_SENT;
        $this->save(true);
        $app->enableAccessControl();
    }

    function getSendValidationErrors(){
        $app = App::i();

        $errorsResult = [];

        $project = $this->project;

        if($project->registrationCategories && !$this->category){
            $errorsResult['category'] = [sprintf($app->txt('The field "%s" is required.'), $project->registrationCategTitle)];
        }
        
        $definitionsWithAgents = $this->_getDefinitionsWithAgents();

        foreach($definitionsWithAgents as $def){
            $errors = [];
            if($def->use === 'required'){
                if(!$def->agent){
                    $errors[] = sprintf($app->txt('The agent "%s" is required.'), $def->label);
                }
            }
            if($def->agent){
                foreach($def->requiredProperties as $requiredProperty){
                    $value = null;
                    try{
                        $value = $def->agent->$requiredProperty;
                    }catch(\Exception $e){};

                    if(!$value){
                        $errors[] = sprintf($app->txt('The field "%s" of the agent "%s" is required.'), $requiredProperty, $def->label);
                    }
                }
            }

            if($errors){
                $errorsResult['registration-agent-' . $def->agentRelationGroupName] = $errors;
            }

        }

        foreach($project->registrationFileConfigurations as $rfc){
            $errors = [];
            if($rfc->required){
                if(!isset($this->files[$rfc->fileGroupName])){
                    $errors[] = sprintf($app->txt('The file "%s" is required.'), $rfc->title);
                }
            }
            if($errors){
                $errorsResult['registration-file-' . $rfc->id] = $errors;
            }
        }

        return $errorsResult;
    }

    function exportAgentsData(){
        //for backup, not being executed yet
        foreach($app->getRegisteredRegistrationAgentRelations() as $def){

            $properties = Agent::getPropertiesMetadata();

            $errorsResult['properties'] = $properties;

            foreach($this->_getAgentsWithDefinitions() as $agent){
                $exportData = [];
                $exportData2 = [];
                $errorsResult[$agent->name] = [];
                foreach($properties as $p=>$details){

                    $val = $agent->$p;

                    if(empty($val) || $details['isEntityRelation'] || $p === 'createTimestamp'){
                        continue;
                    }

                    if( !$details['isMetadata'] || !$details['private'] || in_array($p, $def->requiredProperties) ){
                        $exportData[$p] = $val;
                    }

                    $exportData2[$p] = $details;
                }
                $errorsResult[$agent->name][] = $exportData;
            }
        }
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

        foreach($this->relatedAgents as $agent){
            if($agent->canUser('@control', $user)){
                return true;
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
