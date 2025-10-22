<?php

namespace MapasCulturais\Entities;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;
use Opportunities\Jobs\UpdateSummaryCaches;

/**
 * EvaluationMethodConfiguration
 *
 * @property \MapasCulturais\Entities\Opportunity $opportunity Opportunity
 * @property \DateTime $evaluationFrom
 * @property \DateTime $evaluationTo
 * @property string $name
 * @property \MapasCulturais\Definitions\EntityType $type
 * 
 * @property-read \MapasCulturais\Definitions\EvaluationMethod $definition The evaluation method definition object
 * @property-read \MapasCulturais\EvaluationMethod $evaluationMethod The evaluation method module object
 * @property-read bool $useCommitteeGroups
 * @property-read bool $evaluateSelfApplication
 * @property-read string $summaryCacheKey Chave do cache do resumo das avaliações
 * @property int $opportunity ownerId
 * @property-read \MapasCulturais\Entities\Opportunity $owner
 * @property-read boolean $publishedRegistration
 * @property-read DateTime $publishTimestamp
 * @property-read array $summary
 * @property-read boolean $evaluationOpen
 * 
 * @ORM\Table(name="evaluation_method_configuration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class EvaluationMethodConfiguration extends \MapasCulturais\Entity {

    use Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityAgentRelation,
        Traits\EntityPermissionCache{
            Traits\EntityTypes::setType as traitSetType;
        }
        
    protected $__enableMagicGetterHook = true;
    protected $__enableMagicSetterHook = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="evaluation_method_configuration_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * The Evaluation Method Slug
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $_type;

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\OneToOne(targetEntity="MapasCulturais\Entities\Opportunity", inversedBy="evaluationMethodConfiguration", cascade={"persist"} )
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $opportunity;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

         /**
     * @var \DateTime
     *
     * @ORM\Column(name="evaluation_from", type="datetime", nullable=true)
     */
    protected $evaluationFrom;


     /**
     * @var \DateTime
     *
     * @ORM\Column(name="evaluation_to", type="datetime", nullable=true)
     */
    protected $evaluationTo;

    /**
     * @var \MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__agentRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $__metadata;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationPermissionCache", mappedBy="owner", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;
    
    static function getValidations() {
        $app = App::i();

        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome da fase de avaliação é obrigatório')
            ],
            'evaluationFrom' => [
                'required' => \MapasCulturais\i::__('A data inicial das avaliações é obrigatória'),
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
            ],
            'evaluationTo' => [
                'required' => \MapasCulturais\i::__('A data final das avaliações é obrigatória'),
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '$this->validateEvaluationDates()' => \MapasCulturais\i::__('A data final das avaliações deve ser maior ou igual a data inicial')
            ]
        ];

        $hook_class = self::getHookClassPath();

        $app->applyHook("entity($hook_class)::validations", [&$validations]);

        return $validations;
    }

    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateEvaluationDates() {
        if($this->registrationFrom && $this->registrationTo){
            return $this->registrationFrom <= $this->registrationTo;

        }elseif($this->registrationFrom || $this->registrationTo){
            return false;

        }else{
            return true;
        }
    }

    function setType($value) {
        $app = App::i();
        
        $this->traitSetType($value);

        $definition = $app->getRegisteredEntityTypeById($this, $this->_type);

        if(!$this->name && $definition) {
            $this->name = $definition->name;
        }
    }

    function setName($value) {
        $app = App::i();
        
        $definition = $app->getRegisteredEntityTypeById($this, $this->_type);
        
        if($value) {
            $this->name = $value;
        } else if((!$value && !$this->name) && $definition) {
            $this->name = $definition->name;    
        }
    }

    function setOpportunity($value) {
        if($value instanceof Opportunity) {
            $this->opportunity = $value;
        } else {
            $app = App::i();
            $this->opportunity = $app->repo('Opportunity')->find($value);
        }
    }

    function setEvaluationFrom($date){
        if($date instanceof \DateTime){
            $this->evaluationFrom = $date;
        }elseif($date){
            $this->evaluationFrom = new \DateTime($date);
        }else{
            $this->evaluationFrom = null;
        }
    }

    function setEvaluationTo($date){
        if($date instanceof \DateTime){
            $this->evaluationTo = $date;
        }elseif($date){
            $this->evaluationTo = new \DateTime($date);
        }else{
            $this->evaluationTo = null;
        }
    }

    public function jsonSerialize(): array {
        $result = parent::jsonSerialize();
        $result['type'] = $this->type;
        $result['opportunity'] = $this->opportunity->simplify('id,name,singleUrl,summary');
        $result['useCommitteeGroups'] = $this->useCommitteeGroups;
        $result['evaluateSelfApplication'] = $this->evaluateSelfApplication;
        $result['summary'] = $this->summary;

        /**
         * @todo Arranjar um modo de colocar isso no módulo de avaliação técnica
         */
        if ($this->_type == 'technical') {
            $result['opportunity']->affirmativePoliciesEligibleFields = $this->opportunity->getFields();
        }
        
        return $result;
    }

    /**
     * Returns the Evaluation Method Definition Object
     * @return \MapasCulturais\Definitions\EvaluationMethod
     */
    public function getDefinition() {
        $app = App::i();
        $definition = $app->getRegisteredEvaluationMethodBySlug($this->_type);
        return $definition;
    }

    /**
     * Returns the Evaluation Method Plugin Object
     * @return \MapasCulturais\EvaluationMethod
     */
    public function getEvaluationMethod() {
        $definition = $this->getDefinition();
        if ($definition) {
            return $definition->evaluationMethod;
        } else {
            return null;
        }
    }

    public function getUseCommitteeGroups() {
        return $this->evaluationMethod ? $this->evaluationMethod->useCommitteeGroups() : false;
    }
    
    public function getEvaluateSelfApplication() {
        return $this->evaluationMethod ? $this->evaluationMethod->evaluateSelfApplication() : false;
    }

    public function getUserRelation($user = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
        }

        $relation = $app->repo('EvaluationMethodConfigurationAgentRelation')->findOneBy(['agent' => $user->profile, 'owner' => $this]);

        return $relation;
    }

    /**
     * The Owner Opportunity
     * @return \MapasCulturais\Entities\Opportunity
     */
    function getOwner() {
        return $this->opportunity;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->opportunity->id;
    }
    /**
     * @return bool
     */
    public function getPublishedRegistration()
    {
        return $this->opportunity->publishedRegistration;
    }

    /**
     * @return DateTime
     */
    public function getPublishTimestamp()
    {
        return $this->opportunity->publishTimestamp;
    }

    
    /**
     * Retorna uma chave única para o cache do resumo das avaliações.
     * 
     * @return string A chave única para o cache do resumo da avaliações .
     */
    public function getSummaryCacheKey(): string
    {
        return "evaluation-summary-{$this->id}";
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

        if ($app->config['app.log.summary']) {
            $app->log->debug("SUMMARY: Atualizando o resumo de avaliações da fase {$this->name} ($this->id)");
        }

        $em = $this->evaluationMethod;
        $conn = $app->em->getConnection();
        $opportunity = $this->owner;
        $data = [
            'evaluations' => []
        ];

        if(!$em) {
            return $data;
        }
        
        // Conta as inscrições avaliadas por consolidatedResult
        $query = $app->em->createQuery("
            SELECT 
                r.consolidatedResult, 
                count(r) as qtd 
            FROM 
                MapasCulturais\\Entities\\Registration r  
            WHERE 
                r.opportunity = :opp AND r.status > 0
            GROUP BY r.consolidatedResult
        ");

        $query->setParameters([
            "opp" => $opportunity,
        ]);
        
        if($result = $query->getResult()){
            foreach($result as $values){
                $status = $em->valueToString($values['consolidatedResult']);
                if($status) {
                    $data['evaluations'][$status] = $values['qtd'];
                }
            }
        }

        // Conta as inscrições que não tenham sido totalmente avaliadas
        $query = $app->em->createQuery("
            SELECT 
                count(r) as qtd 
            FROM 
                MapasCulturais\\Entities\\Registration r  
            WHERE 
                r.opportunity = :opp AND r.status = 1 AND
                (r.consolidatedResult is null or r.consolidatedResult in ('', '0'))
        ");

        $query->setParameters([
            "opp" => $opportunity,
        ]);

        if($result = $query->getResult()){
            foreach($result as $values){
                $data['evaluations'][i::__('Não Avaliada')] = $values['qtd'];
            }
        }

        // Conta as inscrições pendentes de avaliação
        $pending_evaluation = $conn->fetchAssoc("
            SELECT COUNT(DISTINCT r.id) AS qtd
            FROM registration r,
                jsonb_object_keys(r.valuers) AS key
            WHERE 
                r.opportunity_id = {$opportunity->id}
                AND r.status = 1
                AND r.valuers IS NOT NULL
                AND jsonb_typeof(r.valuers) = 'object'
        ");
        $data['evaluations'][i::__('Pendente de avaliação')] = $pending_evaluation['qtd'];

        // Conta as inscrições com avaliações iniciadas
        $query = $app->em->createQuery("
            SELECT 
                COUNT(DISTINCT r.id) AS qtd 
            FROM 
                MapasCulturais\\Entities\\RegistrationEvaluation re
            JOIN 
                re.registration r
            WHERE 
                r.opportunity = :opp AND r.status < 2
        ");
        
        $query->setParameters([
            "opp" => $opportunity,
        ]);

        if($result = $query->getResult()){
            foreach($result as $values){
                $data['evaluations'][i::__('Com avaliações iniciadas')] = $values['qtd'];
            }
        }

        if($data['evaluations']) {
            $data['evaluations'] =  $em->filterEvaluationsSummary($data['evaluations']);
        }
        
        if($slug = $em->slug) {
            $app->applyHookBoundTo($this, "evaluations({$slug}).summary", [&$data]);
        }

        if($app->config['app.useOpportunitySummaryCache']) {
            $app->mscache->save($cache_key, $data, $app->config['app.opportunitySummaryCache.lifetime']);
        }

        return $data;
    }

    public function getValuerSummary(?User $user = null, ?string $committee_name = null): array {
        $app = App::i();
        
        /** @var \MapasCulturais\Connection $conn */
        $conn = $app->em->getConnection();
        $opportunity = $this->opportunity;
        $data = [];
        if($user) {
            $user_ids = [$user->id];
        } else {
            $agent_relations = $this->getAgentRelations();
            
            $user_ids = array_map(fn($agent_relation) => $agent_relation->agent->user->id, $agent_relations);
            if(!$user_ids) {
                $user_ids = [-1];
            }
        }

        $user_ids = implode(',', $user_ids);
        /**
         * Constrói a query para contar as avaliações com base no status.
         *
         * @param int|null $status Status da avaliação (0 = iniciada, 1 = concluída, 2 = enviada).
         * @return int Retorna a contagem de avaliações.
         */
        $buildQuery = function ($status = null) use ($user_ids, $opportunity, $conn, $committee_name): int {
            $statusCondition = is_null($status) ? "e.status IS NULL" : "e.status = {$status} AND e.registration_id IN (SELECT r.id FROM registration r WHERE r.opportunity_id = {$opportunity->id})";
            
            $params = [];
            $committee_where = '';
            if($committee_name) {
                $committee_where = "AND committee = :committee";
                $params['committee'] = $committee_name;
            }
            
            $query = "
                SELECT DISTINCT count(e.registration_id)
                FROM registration_evaluation e
                WHERE {$statusCondition} AND user_id IN($user_ids) $committee_where
            ";

            

            return $conn->fetchScalar($query, $params);
        };

        $params = [];
        $committee_where = '';
        if($committee_name) {
            $committee_where = "AND valuer_committee = :committee";
            $params['committee'] = $committee_name;
        }

        // Avaliações pendentes
        $query = "
            SELECT DISTINCT count(e.registration_id)
            FROM evaluations e
            WHERE opportunity_id = {$opportunity->id} AND e.evaluation_status IS NULL AND valuer_user_id IN ($user_ids) $committee_where
        ";

        $data['pending'] = $conn->fetchScalar($query, $params);
        
        // Avaliações iniciadas
        $data['started'] = $buildQuery(0);
        
        // Avaliações concluídas
        $data['completed'] = $buildQuery(1);
        
        // Avaliações enviadas
        $data['sent'] = $buildQuery(2);
        
        return $data;
    }

    public function getDefaultStatuses(): array {
        $evaluation_method = $this->getEvaluationMethod();
        
        return $evaluation_method->getDefaultStatuses($this);
    }

    public function enqueueUpdateSummary(string $start_string = 'now') {
        $app = App::i();
        $app->enqueueOrReplaceJob(UpdateSummaryCaches::SLUG, [
            'evaluationMethodConfiguration' => $this
        ], $start_string);
    }

    /**
     * @return bool
     */
    public function getEvaluationOpen(){

        $today = new DateTime('now');
        if($today >= $this->evaluationFrom && $today <= $this->evaluationTo ){
            return true;
        }

        return false;
    }

    public function getCommittee($return_relation = true) {
        $app = App::i();

        $committee = $this->getAgentRelations(null, true);
        
        if(!$return_relation) {
            $committee = array_map(function($r){ return $r->agent; }, $committee);
        }
        
        $app->applyHookBoundTo($this, "entity({$this->getHookClassPath()}.committee", [&$committee, $return_relation]);
        
        return $committee;
    }

    public function getValuerUserIds (bool $include_disabled = false): array {
        $user_ids = [];
        foreach ($this->getAgentRelations() as $agent_relation) {
            if (!$include_disabled && $agent_relation->status != EvaluationMethodConfigurationAgentRelation::STATUS_ENABLED) {
                continue;
            }

            $user_ids[] = $agent_relation->agent->user->id;
        }
        
        return $user_ids;
    }

    /** 
     * Redistribui as inscrições entre os avaliadores
     * 
     */
    public function redistributeCommitteeRegistrations() {
        $this->evaluationMethod->redistributeRegistrations($this->owner);
    }

    protected function canUserEvaluateOnTime($user){
        if($user->is('guest')){
            return false;
        }

        $valuers = $this->getAgentRelations();
        
        $is_valuer = false;
        
        foreach ($valuers as $agent_relation) {
            if ($agent_relation->status != EvaluationMethodConfigurationAgentRelation::STATUS_ENABLED) {
                continue;
            }

            $agent = $agent_relation->agent;
            if($agent->user->id == $user->id ){
                $is_valuer = true;
            }
        }
        
        return $is_valuer;
    }

    protected function canUserCreate($user){
        return $this->opportunity->canUser('modify', $user);
    }

    protected function canUserModify($user){
        return $this->opportunity->canUser('modify', $user);
    }

    protected function canUserRemove($user){
        if ($this->opportunity->publishedRegistrations) {
            return false;
        }

        if ($this->getCommittee()) {
            return false;
        }

        return parent::canUserRemove($user);
    }    
    
    protected function canUserManageEvaluationCommittee($user){
        return $this->opportunity->canUser('@control', $user);
    }
    
    protected function canUserCreateAgentRelationWithControl($user){
        return $this->opportunity->canUser('@control', $user);
    }

    function canUserRemoveAgentRelationWithControl($user){
        return $this->opportunity->canUser('@control', $user);
    }

    protected function canUser_control($user) {
        if ($this->opportunity && $this->opportunity->canUser('@control')) {
            return true;
        } else {
            return parent::canUser_control($user);
        }
    }
    
    function getExtraEntitiesToRecreatePermissionCache(){
        return [$this->opportunity->parent ?: $this->opportunity];
    }
    
    
    function save($flush = false){
        parent::save($flush);
        
        $this->enqueueToPCacheRecreation();
    }

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null) {
        parent::prePersist($args);
    }

    /** @ORM\PostPersist */
    public function postPersist($args = null) {
        parent::postPersist($args);
    }

    /** @ORM\PreRemove */
    public function preRemove($args = null) {
        parent::preRemove($args);
    }

    /** @ORM\PostRemove */
    public function postRemove($args = null) {
        parent::postRemove($args);
    }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null) {
        parent::preUpdate($args);
    }

    /** @ORM\PostUpdate */
    public function postUpdate($args = null) {
        parent::postUpdate($args);
    }

}
