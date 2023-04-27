<?php

namespace MapasCulturais\Entities;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;
use Doctrine\ORM\Mapping as ORM;

/**
 * EvaluationMethodConfiguration
 *
 * @property \MapasCulturais\Entities\Opportunity $opportunity Opportunity
 * @property \DateTime $evaluationFrom
 * @property \DateTime $evaluationTo
 * 
 * @property-read \MapasCulturais\Definitions\EvaluationMethod $definition The evaluation method definition object
 * @property-read \MapasCulturais\EvaluationMethod $evaluationMethod The evaluation method plugin object
 * @property int $opportunity ownerId
 * @property-read \MapasCulturais\Entities\Opportunity owner
 * @property-read boolean publishedRegistration
 * @property-read DateTime publishTimestamp
 * @property-read array summary
 * @property-read boolean evaluationOpen
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
        Traits\EntityPermissionCache;
        
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
    protected $id;

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
     * @ORM\OneToOne(targetEntity="MapasCulturais\Entities\Opportunity", inversedBy="evaluationMethodConfiguration", cascade="persist" )
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
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__agentRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EvaluationMethodConfigurationMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventPermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
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

    public function jsonSerialize() {
        $result = parent::jsonSerialize();

        $result['opportunity'] = $this->opportunity->simplify('id,name,singleUrl');

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
        return $definition->evaluationMethod;
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

        $conn = $app->em->getConnection();
        $opportunity = $this->owner;
        $data = [
            'evaluations' => []
        ];
        
        $buildQuery = function($colluns = "*", $params = "", $type = "fetchAll") use ($conn, $opportunity){
            return $conn->$type("SELECT {$colluns} FROM evaluations e WHERE opportunity_id = {$opportunity->id} {$params}");
        };

        $registrations_ids = array_map(function($evaluation){
            return $evaluation['registration_id'];
        }, $buildQuery());
        $reg_ids = implode(',', $registrations_ids);
        
        // Conta as inscrições enviadas
        if($reg_ids){
            if($count_reg = $conn->fetchAssoc("SELECT count(r.status) as qtd FROM registration r WHERE r.id IN ({$reg_ids}) AND r.status > 0"));
            $data['registrations'] = $count_reg['qtd'];
        }

        // Conta as inscrições avaliadas
        $evaluated = $buildQuery("DISTINCT count(e.registration_id) as qtd", "AND e.evaluation_status > 0", "fetchAssoc");
        $data['evaluated'] = $evaluated['qtd'];

        // Conta as inscrições avaliadas por status
        $query = $app->em->createQuery("
            SELECT 
                r.status, 
                count(r) as qtd 
            FROM 
                MapasCulturais\\Entities\\Registration r  
            WHERE 
                r.opportunity = :opp AND r.status > 0 AND 
                r.id IN (:reg_ids) GROUP BY r.status
        ");

        $query->setParameters([
            "opp" => $opportunity,
            "reg_ids" => $registrations_ids
        ]);
        
        if($result = $query->getResult()){
            foreach($result as $values){
                $data[$values['status']] = $values['qtd'];
            }
        }

        // status das avaliações

        // Conta as inscrições avaliadas por status
        $query = $app->em->createQuery("
            SELECT 
                r.consolidatedResult, 
                count(r) as qtd 
            FROM 
                MapasCulturais\\Entities\\Registration r  
            WHERE 
                r.opportunity = :opp AND r.status > 0 AND 
                r.id IN (:reg_ids) GROUP BY r.consolidatedResult
        ");

        $query->setParameters([
            "opp" => $opportunity,
            "reg_ids" => $registrations_ids
        ]);
        
        $em = $this->evaluationMethod;
        if($result = $query->getResult()){
            foreach($result as $values){
                $status = $em->valueToString($values['consolidatedResult']);
                if($status) {
                    $data['evaluations'][$status] = $values['qtd'];
                } else {
                    $data['evaluations'][i::__('Não Avaliada')] = $values['qtd'];
                }
            }
        }

        $data['evaluations'] = $em->filterEvaluationsSummary($data['evaluations']);
        $slug = $em->slug;
        $app->applyHookBoundTo($this, "evaluations({$slug}).summary", [&$data]);

        if($app->config['app.useOpportunitySummaryCache']) {
            $app->cache->save($cache_key, $data, $app->config['app.opportunitySummaryCache.lifetime']);
        }

        return $data;
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

    protected function canUserCreate($user){
        return $this->opportunity->canUser('modify', $user);
    }

    protected function canUserModify($user){
        return $this->opportunity->canUser('modify', $user);
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
