<?php

namespace MapasCulturais\Entities;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\GuestUser;
use MapasCulturais\Entities\User;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Exceptions\PermissionDenied;
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
        Traits\EntityRevision,
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
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation", mappedBy="owner", cascade={"remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__agentRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationMeta", mappedBy="owner", cascade={"remove","persist"}, fetch="EAGER")
     */
    protected $__metadata;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationPermissionCache", mappedBy="owner", cascade={"remove"}, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;
    
    static function getValidations() {
        $app = App::i();

        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome da fase de avaliação é obrigatório'),
                '$this->validateCriteriaSectionsIntegrity()' => \MapasCulturais\i::__('A configuração de critérios e seções contém erros de integridade')
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

    /**
     * Valida a integridade entre seções e critérios.
     * 
     * Verifica:
     * - Se há critérios com 'sid' apontando para seção inexistente (critérios órfãos)
     * - Se há critérios sem campos obrigatórios preenchidos conforme o tipo de avaliação
     * 
     * @return bool true se válido, false se há erros
     */
    function validateCriteriaSectionsIntegrity() {
        // Só valida para métodos que usam seções e critérios
        $types_with_sections = ['technical', 'qualification'];
        if (!in_array($this->_type, $types_with_sections)) {
            return true;
        }

        $sections = $this->sections ?? [];
        $criteria = $this->criteria ?? [];

        // Se os dados vierem como string JSON (antes do unserialize do metadata), converte
        if (is_string($sections)) {
            $sections = json_decode($sections);
        }
        if (is_string($criteria)) {
            $criteria = json_decode($criteria);
        }

        // Normaliza para arrays
        $sections = (array) $sections;
        $criteria = (array) $criteria;

        // Coleta IDs das seções existentes
        $section_ids = [];
        foreach ($sections as $section) {
            $section = (object) $section;
            if (isset($section->id)) {
                $section_ids[$section->id] = true;
            }
        }

        // Se há critérios mas não há seções, é inválido
        if (!empty($criteria) && empty($sections)) {
            return false;
        }

        // Se há seções, cada uma deve ter pelo menos um critério.
        // Exceção: avaliação técnica possui hook que remove seções sem critérios
        // automaticamente antes de salvar, portanto não rejeitamos aqui.
        if (!empty($sections) && $this->_type !== 'technical') {
            $section_ids_with_criteria = [];
            foreach ($criteria as $criterion) {
                $criterion = (object) $criterion;
                if (isset($criterion->sid) && isset($section_ids[$criterion->sid])) {
                    $section_ids_with_criteria[$criterion->sid] = true;
                }
            }

            foreach ($sections as $section) {
                $section = (object) $section;
                if (!isset($section->id) || !isset($section_ids_with_criteria[$section->id])) {
                    return false;
                }
            }
        }

        // Se não há critérios, não há mais o que validar
        if (empty($criteria)) {
            return true;
        }

        // Valida cada critério
        foreach ($criteria as $criterion) {
            $criterion = (object) $criterion;
            
            // Verifica se o critério tem sid
            if (!isset($criterion->sid)) {
                return false;
            }

            // Verifica se a seção referenciada existe
            if (!isset($section_ids[$criterion->sid])) {
                return false;
            }

            if ($this->_type === 'technical') {
                if (empty($criterion->title) || trim($criterion->title) === '') {
                    return false;
                }
                if (!isset($criterion->max) || !is_numeric($criterion->max)) {
                    return false;
                }
                if (!isset($criterion->weight) || !is_numeric($criterion->weight)) {
                    return false;
                }
            }

            if ($this->_type === 'qualification') {
                if (empty($criterion->name) || trim($criterion->name) === '') {
                    return false;
                }

                $options = $criterion->options ?? [];
                if (is_object($options)) {
                    $options = get_object_vars($options);
                }
                if (!is_array($options) || empty(array_filter($options, fn($option) => trim((string) $option) !== ''))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove draft/empty criteria configuration entries before persistence.
     *
     * The UI autosaves while managers are still typing, so intermediate values
     * such as a section without criteria must not become effective config.
     */
    function sanitizeCriteriaSectionsDraft(): void {
        $types_with_sections = ['technical', 'qualification'];
        if (!in_array($this->_type, $types_with_sections)) {
            return;
        }

        $sections = $this->normalizeCriteriaSectionsValue($this->sections ?? []);
        $criteria = $this->normalizeCriteriaSectionsValue($this->criteria ?? []);

        $sections_by_id = [];
        foreach ($sections as $section) {
            $section = (object) $section;
            $section_id = $section->id ?? null;
            $section_name = trim((string) ($section->name ?? ''));

            if (!$section_id || $section_name === '') {
                continue;
            }

            $section->name = $section_name;
            $sections_by_id[$section_id] = $section;
        }

        $clean_criteria = [];
        $section_ids_with_criteria = [];
        foreach ($criteria as $criterion) {
            $criterion = (object) $criterion;
            $section_id = $criterion->sid ?? null;

            if (!$section_id || !isset($sections_by_id[$section_id])) {
                continue;
            }

            if (!$this->isPersistableCriterion($criterion)) {
                continue;
            }

            $clean_criteria[] = $criterion;
            $section_ids_with_criteria[$section_id] = true;
        }

        $clean_sections = [];
        foreach ($sections_by_id as $section_id => $section) {
            if (isset($section_ids_with_criteria[$section_id])) {
                $clean_sections[] = $section;
            }
        }

        $this->sections = $clean_sections;
        $this->criteria = $clean_criteria;
    }

    private function normalizeCriteriaSectionsValue($value): array {
        if (is_string($value)) {
            $decoded = json_decode($value);
            $value = $decoded ?: [];
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        return is_array($value) ? $value : [];
    }

    private function isPersistableCriterion(object $criterion): bool {
        if ($this->_type === 'technical') {
            $criterion->title = trim((string) ($criterion->title ?? ''));

            if ($criterion->title === '') {
                return false;
            }

            if (!isset($criterion->max) || !is_numeric($criterion->max)) {
                return false;
            }

            if (!isset($criterion->weight) || !is_numeric($criterion->weight)) {
                return false;
            }

            return true;
        }

        if ($this->_type === 'qualification') {
            $criterion->name = trim((string) ($criterion->name ?? ''));

            if ($criterion->name === '') {
                return false;
            }

            $options = $criterion->options ?? [];
            if (is_object($options)) {
                $options = get_object_vars($options);
            }

            if (!is_array($options)) {
                return false;
            }

            $options = array_values(array_filter($options, fn($option) => trim((string) $option) !== ''));
            if (empty($options)) {
                return false;
            }

            $criterion->options = $options;
            return true;
        }

        return false;
    }

    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateEvaluationDates() {
        if ($this->evaluationFrom && $this->evaluationTo) {
            return $this->evaluationFrom <= $this->evaluationTo;
        }
        if ($this->evaluationFrom || $this->evaluationTo) {
            return false;
        }

        return true;
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
        if($this->opportunity->isContinuousFlow) {
            if($this->opportunity->canUser('@control') && !$this->getCommittee()) {
                return true;
            }
        }
        
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

    /**
     * Verifica se o usuário pode substituir um avaliador
     * 
     * @param User $user
     * @return bool
     */
    protected function canUserReplaceEvaluator(GuestUser|User $user): bool
    {
        return $this->opportunity->canUser('@control', $user);
    }
    
    function getExtraEntitiesToRecreatePermissionCache(){
        return [$this->opportunity->parent ?: $this->opportunity];
    }
    
    
    /**
     * Verifica se existem avaliações iniciadas, concluídas ou enviadas nesta fase.
     */
    public function hasStartedEvaluations(): bool {
        if ($this->isNew()) {
            return false;
        }

        $app = App::i();
        $count = $app->em->getConnection()->fetchOne("
            SELECT COUNT(re.id)
            FROM registration_evaluation re
            INNER JOIN registration r ON r.id = re.registration_id
            WHERE r.opportunity_id = :opportunity_id
        ", ['opportunity_id' => $this->opportunity->id]);

        return (int) $count > 0;
    }

    /**
     * Retorna os IDs de critérios removidos entre duas configurações.
     */
    public function getRemovedCriterionIds(array $old_criteria, array $new_criteria): array {
        $old_criteria = $this->normalizeCriteriaSectionsValue($old_criteria);
        $new_criteria = $this->normalizeCriteriaSectionsValue($new_criteria);

        $new_criterion_ids = [];
        foreach ($new_criteria as $criterion) {
            $criterion = (object) $criterion;
            if (!empty($criterion->id)) {
                $new_criterion_ids[$criterion->id] = true;
            }
        }

        $removed = [];
        foreach ($old_criteria as $criterion) {
            $criterion = (object) $criterion;
            if (!empty($criterion->id) && !isset($new_criterion_ids[$criterion->id])) {
                $removed[] = $criterion->id;
            }
        }

        return array_values(array_unique($removed));
    }

    /**
     * Impede exclusão de critérios/seções por não-admin quando há avaliações iniciadas.
     *
     * @return string[] IDs dos critérios removidos autorizados
     */
    protected function validateCriteriaDeletion(): array {
        $types_with_sections = ['technical', 'qualification'];
        if (!in_array($this->_type, $types_with_sections, true) || $this->isNew()) {
            return [];
        }

        $app = App::i();
        $conn = $app->em->getConnection();

        $old_criteria = json_decode((string) $conn->fetchOne("
            SELECT value FROM evaluationmethodconfiguration_meta
            WHERE object_id = :id AND key = 'criteria'
        ", ['id' => $this->id]) ?: '[]', true) ?: [];

        $removed_criterion_ids = $this->getRemovedCriterionIds(
            $old_criteria,
            (array) ($this->criteria ?? [])
        );

        if (empty($removed_criterion_ids) || !$this->hasStartedEvaluations()) {
            return $removed_criterion_ids;
        }

        if (!$app->user->is('admin')) {
            throw new PermissionDenied(
                $app->user,
                message: i::__('Já existem avaliações iniciadas, concluídas ou enviadas. Por isso, não é possível excluir critérios ou seções. Solicite a um administrador do sistema.')
            );
        }

        return $removed_criterion_ids;
    }

    /**
     * Remove critérios excluídos do evaluation_data das avaliações existentes.
     */
    public function removeDeletedCriteriaFromEvaluations(array $removed_criterion_ids): void {
        if (empty($removed_criterion_ids)) {
            return;
        }

        $app = App::i();
        $app->disableAccessControl();

        $evaluations = $app->em->createQuery("
            SELECT re
            FROM MapasCulturais\\Entities\\RegistrationEvaluation re
            JOIN re.registration r
            WHERE r.opportunity = :opportunity
        ")->setParameter('opportunity', $this->opportunity)->getResult();

        $registrations_to_consolidate = [];

        foreach ($evaluations as $evaluation) {
            $data = (array) $evaluation->evaluationData;
            $changed = false;

            foreach ($removed_criterion_ids as $criterion_id) {
                if (array_key_exists($criterion_id, $data)) {
                    unset($data[$criterion_id]);
                    $changed = true;
                }

                $reason_key = "{$criterion_id}_reason";
                if (array_key_exists($reason_key, $data)) {
                    unset($data[$reason_key]);
                    $changed = true;
                }
            }

            if (!$changed) {
                continue;
            }

            $evaluation->setEvaluationData($data);
            $evaluation->__skipQueuingPCacheRecreation = true;
            $evaluation->save(true);

            $registrations_to_consolidate[$evaluation->registration->id] = $evaluation->registration;
        }

        foreach ($registrations_to_consolidate as $registration) {
            $registration->consolidateResult(true);
        }

        $app->enableAccessControl();
    }

    function save($flush = false){
        $removed_criterion_ids = $this->validateCriteriaDeletion();

        $this->sanitizeCriteriaSectionsDraft();

        parent::save($flush);

        if ($removed_criterion_ids) {
            $this->removeDeletedCriteriaFromEvaluations($removed_criterion_ids);
        }
        
        $this->enqueueToPCacheRecreation();
    }

    /**
     * Retorna as inscrições enviadas do edital cujo dono é o usuário informado.
     * Usado para avisar que o avaliador não poderá avaliar a própria inscrição.
     *
     * @return array<int, array{id:int|string, number:string}>
     */
    public function findSentRegistrationsOwnedByUser(User $user): array
    {
        $app = App::i();
        $first_phase = $this->opportunity->firstPhase ?? $this->opportunity;

        $phase_ids = [(int) $first_phase->id];
        foreach ($app->repo('Opportunity')->findBy(['parent' => $first_phase]) as $phase) {
            $phase_ids[] = (int) $phase->id;
        }

        if (!in_array((int) $this->opportunity->id, $phase_ids, true)) {
            $phase_ids[] = (int) $this->opportunity->id;
        }

        $placeholders = implode(',', array_fill(0, count($phase_ids), '?'));
        $params = array_merge([(int) $user->id], $phase_ids);

        return $app->em->getConnection()->fetchAllAssociative(
            "SELECT r.id, r.number
             FROM registration r
             INNER JOIN agent a ON a.id = r.agent_id
             WHERE a.user_id = ?
               AND r.status > 0
               AND r.opportunity_id IN ($placeholders)
             ORDER BY r.id",
            $params
        );
    }

    /**
     * Monta payload de aviso quando o usuário/agente tem inscrição própria no edital.
     *
     * @return array{count:int, numbers:string[], message:string}|null
     */
    public function buildOwnRegistrationsWarning(User $user, ?string $agent_name = null): ?array
    {
        $registrations = $this->findSentRegistrationsOwnedByUser($user);
        if (!$registrations) {
            return null;
        }

        $numbers = array_values(array_filter(array_map(
            fn ($row) => (string) ($row['number'] ?? ''),
            $registrations
        )));
        $count = count($numbers);
        $name = $agent_name ?: ($user->profile->name ?? ("#{$user->id}"));

        if ($count === 1) {
            $message = sprintf(
                i::__('%s também é proponente neste edital e, por isso, não avaliará a própria inscrição. As quantidades para ele podem ser diferentes do esperado.'),
                $name
            );
        } else {
            $message = sprintf(
                i::__('%s também é proponente neste edital (%d inscrições) e, por isso, não avaliará as próprias. As quantidades para ele podem ser diferentes do esperado.'),
                $name,
                $count
            );
        }

        return [
            'count' => $count,
            'numbers' => $numbers,
            'message' => $message,
        ];
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
