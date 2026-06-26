<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\i;

/**
 * SealRelation
 *
 *
 * @property-read int $id The Id of the relation.
 *
 * @todo http://thoughtsofthree.com/2011/04/defining-discriminator-maps-at-child-level-in-doctrine-2-0/
 *
 * @ORM\Table(name="seal_relation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Opportunity"   = "\MapasCulturais\Entities\OpportunitySealRelation",
        "MapasCulturais\Entities\Project"       = "\MapasCulturais\Entities\ProjectSealRelation",
        "MapasCulturais\Entities\Event"         = "\MapasCulturais\Entities\EventSealRelation",
        "MapasCulturais\Entities\Agent"         = "\MapasCulturais\Entities\AgentSealRelation",
        "MapasCulturais\Entities\Space"         = "\MapasCulturais\Entities\SpaceSealRelation"
   })
 * @ORM\HasLifecycleCallbacks
 */
abstract class SealRelation extends \MapasCulturais\Entity
{
    const STATUS_PENDING = -5;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seal_relation_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * A entidade que recebe o selo
     * 
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=true)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Seal
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Seal", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seal_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $seal;

    /**
     * O agente que está aplicando o selo (que não necessariamente é o dono do selo, pode ser um agente com permissão
     * ou o dono de um projeto que aplica o selo quando a inscrição é selecionada)
     * 
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $agent;

    /**
     * Gerada automaticamente no metodo save() com o profile do usuario logado.
     * 
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $ownerRelation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validate_date", type="date", nullable=true)
     */
    protected $validateDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="renovation_request", type="boolean", nullable=false)
     */
    protected $renovationRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="computed_status", type="string", length=20, nullable=true)
     */
    protected $computedStatus;

    /**
     * @var \MapasCulturais\Entities\SealRelationField[]
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealRelationField", mappedBy="sealRelation", cascade={"remove","persist"}, fetch="LAZY")
     */
    protected $__sealRelationFields;
    
    function setSeal(Seal $seal){
        if($this->isNew()){
            $this->seal = $seal;

            $period = new \DateInterval("P" . ((string)$seal->validPeriod) . "M");
            $this->validateDate = clone $this->createTimestamp;
            $this->validateDate->add($period);
            
        } else {
            throw new \Exception();
        }
    }

    function jsonSerialize(): array {
        $app = App::i();
        $user = $app->user;
        $can_view_sensitive = $this->canUserViewSensitiveData($user);

        $result = parent::jsonSerialize();
        $result['@entityType'] = 'sealRelation';
        $result['owner'] = $this->owner->simplify('className,id,name,avatar,singleUrl');

        if ($this->seal->sensitive && !$can_view_sensitive) {
            $result['seal'] = [
                'id' => $this->seal->id,
                'name' => i::__('[Selo oculto]'),
                'singleUrl' => null,
                'sensitive' => true
            ];
            $result['computedStatus'] = 'hidden';
            $result['fields'] = [];
        } else {
            $result['seal'] = $this->seal->simplify('id,name,files,singleUrl,validateDate');
            $result['computedStatus'] = $this->computedStatus;

            $fields = [];
            foreach ($this->getSealRelationFields() as $field) {
                $fields[] = [
                    'fieldName' => $field->fieldName,
                    'fieldStatus' => $field->getFieldStatus(),
                    'expiryDate' => $field->expiryDate ? $field->expiryDate->format(i::__('d/m/Y')) : null,
                    'isInvalidator' => $field->isInvalidator,
                    'isUnlocked' => $field->isUnlocked(),
                ];
            }
            $result['fields'] = $fields;
        }

        $result['certificateText'] = $this->getCertificateText(true);
        $result['requestSealRelationUrl'] = $this->requestSealRelationUrl;
        $result['renewSealRelationUrl'] = $this->renewSealRelationUrl;
        $result['ownerSealUserId'] = $this->ownerSealUserId;
        $result['toExpire'] = $this->toExpire;
        // $result['renovationRequest'] = $this->renovationRequest; // acho que já vai no parent::jsonSerialize
        $result['validateDate'] = $this->validateDate->format(i::__('d/m/Y'));

        return $result;
    }

    function getRequestSealRelationUrl() {
        return $this->owner->getRequestSealrelationUrl($this->id);
    }
    function getRenewSealRelationUrl() {
        return $this->owner->getRenewSealRelationUrl($this->id);
    }
    function getOwnerSealUserId() {
        return $this->seal->ownerUser->id;
    }

    /**
     * Retorna os campos SealRelationField associados, garantindo carregamento.
     *
     * @return SealRelationField[]
     */
    public function getSealRelationFields(): array
    {
        $fields = $this->__sealRelationFields;
        
        if ($fields instanceof \Doctrine\Common\Collections\Collection) {
            $fields = $fields->toArray();
        }
        
        if (empty($fields) && $this->id) {
            $app = App::i();
            $fields = $app->repo('SealRelationField')->findBy(['sealRelation' => $this]);
        }
        
        return $fields ?: [];
    }

    /**
     * Calcula e retorna o status computado do selo com base nos campos.
     *
     * @return string 'fully_valid', 'partially_valid', ou 'invalid'
     */
    public function getComputedStatus(): string
    {
        $config = (array) $this->seal->lockedFieldsConfig;

        // Se não há configuração granular, usa comportamento legado
        if (empty($config)) {
            if ($this->seal->validPeriod > 0 && $this->validateDate) {
                $today = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
                $today = $today->setTime(0, 0, 0);
                $expirationDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->validateDate->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
                
                if ($expirationDate && $expirationDate < $today) {
                    return 'invalid';
                }
            }
            return 'fully_valid';
        }

        // Nova lógica granular
        $has_invalidator_expired = false;
        $has_non_invalidator_expired = false;
        $expired_count = 0;
        $total_count = 0;

        $fields = $this->getSealRelationFields();
        
        if (empty($fields)) {
            return 'fully_valid';
        }

        foreach ($fields as $field) {
            $total_count++;
            $field_status = $field->getFieldStatus();

            if ($field_status === 'expired') {
                $expired_count++;
                if ($field->isInvalidator) {
                    $has_invalidator_expired = true;
                } else {
                    $has_non_invalidator_expired = true;
                }
            }
        }

        if ($total_count === 0) {
            return 'fully_valid';
        }

        if ($has_invalidator_expired || $expired_count === $total_count) {
            return 'invalid';
        }

        if ($has_non_invalidator_expired) {
            return 'partially_valid';
        }

        return 'fully_valid';
    }

    /**
     * Atualiza e persiste o computed_status se mudou.
     *
     * @return void
     */
    public function updateComputedStatus(): void
    {
        $new_status = $this->getComputedStatus();
        if ($this->computedStatus !== $new_status) {
            $this->computedStatus = $new_status;
        }
    }

    /**
     * Renova o selo, recalculando as datas de expiração de todos os campos
     * com base na configuração atual do selo.
     *
     * @return void
     */
    public function renew(): void
    {
        $app = App::i();
        $fields = $this->getSealRelationFields();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $field->renew();
                $app->em->persist($field);
            }
        }

        // Atualiza validateDate legado
        if ($this->seal->validPeriod > 0) {
            $period = new \DateInterval("P" . ((string)$this->seal->validPeriod) . "M");
            $this->validateDate = new \DateTime();
            $this->validateDate->add($period);
        }

        $this->updateComputedStatus();
        $app->em->persist($this);
    }

    /**
     * Sincroniza os campos granulares desta relação com a configuração atual
     * do selo. Usado quando lockedFieldsConfig do selo é alterado.
     *
     * @return void
     */
    public function reconcileSealRelationFields(): void
    {
        $app = App::i();
        $config = (array) $this->seal->lockedFieldsConfig;
        $fields_by_name = [];

        foreach ($this->getSealRelationFields() as $field) {
            $fields_by_name[$field->fieldName] = $field;
        }

        foreach ($fields_by_name as $field_name => $field) {
            if (!isset($config[$field_name])) {
                $app->em->remove($field);
                unset($fields_by_name[$field_name]);
            }
        }

        foreach ($config as $field_name => $field_config) {
            $field_config = (array) $field_config;
            $field = $fields_by_name[$field_name] ?? null;

            if (!$field instanceof SealRelationField) {
                $field = new SealRelationField();
                $field->sealRelation = $this;
                $field->fieldName = $field_name;
            }

            $field->expiryDate = $this->calculateSealRelationFieldExpiryDate($field_config);
            $field->isInvalidator = !empty($field_config['isInvalidator']);
            $field->setNotifiedExpire(false);
            $field->setNotifiedToExpire(false);

            $app->em->persist($field);
            $fields_by_name[$field_name] = $field;
        }

        $this->__sealRelationFields = array_values($fields_by_name);
        $this->updateComputedStatus();
        $app->em->persist($this);
    }

    /**
     * @param array $field_config
     * @return \DateTimeInterface|null
     */
    protected function calculateSealRelationFieldExpiryDate(array $field_config): ?\DateTimeInterface
    {
        $has_expiry = !empty($field_config['hasExpiry']);
        if (!$has_expiry || empty($field_config['periodValue']) || empty($field_config['periodUnit'])) {
            return null;
        }

        $period_value = (int) $field_config['periodValue'];
        $period_unit = $field_config['periodUnit'];

        $interval_spec = 'P' . $period_value;
        switch ($period_unit) {
            case 'day':
                $interval_spec .= 'D';
                break;
            case 'month':
                $interval_spec .= 'M';
                break;
            case 'year':
                $interval_spec .= 'Y';
                break;
            default:
                $interval_spec .= 'M';
                break;
        }

        $create_timestamp = $this->createTimestamp ?: new \DateTime();
        $expiry_date = clone $create_timestamp;
        $expiry_date->add(new \DateInterval($interval_spec));

        return $expiry_date;
    }
    
    /**
     * Retorna 0 se o certificado está expirado, 
     * Retorna 1 se o certificado não está expirado
     * Retorna 2 se o certificado nunca expira
     * 
     * @return int 
     */
    function getToExpire() {
        if($this->seal->validPeriod > 0){
            $expirationDate = $this->validateDate;
            $now = new \DateTime();

            // Expired
            if($expirationDate < $now) { 
                return 0;
            // To Expire
            } else {
                return 1;
            }
        
        // Don't Expire
        } else {
            return 2;
        }
    }

    /**
     * Verifica se o usuário pode visualizar os dados desta relação de selo,
     * considerando selos sensíveis/ocultos (LGPD).
     *
     * @param User $user
     * @param Opportunity|null $opportunity Oportunidade de contexto (para filtros de inscrição)
     * @return bool
     */
    public function canUserViewSensitiveData(User $user, ?Opportunity $opportunity = null): bool {
        if ($user->is('admin')) {
            return true;
        }

        // Dono da entidade
        $owner = $this->owner;
        if ($owner->userHasControl($user)) {
            return true;
        }

        // Gestor da oportunidade específica (sem role admin)
        if ($opportunity && $opportunity->canUser('@control', $user)) {
            return true;
        }

        // Gestor de alguma oportunidade vinculada à entidade
        if ($this->_userControlsLinkedOpportunity($user)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se o usuário tem @control em alguma oportunidade vinculada ao dono desta relação.
     *
     * @param User $user
     * @return bool
     */
    protected function _userControlsLinkedOpportunity(User $user): bool {
        $app = App::i();
        $owner = $this->owner;

        // Obtém o agente proprietário da entidade que recebeu o selo
        $owner_agent = ($owner instanceof Agent) ? $owner : $owner->owner;

        if (!($owner_agent instanceof Agent)) {
            return false;
        }

        try {
            $dql = "SELECT pc.id FROM MapasCulturais\Entities\OpportunityPermissionCache pc
                    JOIN pc.owner o
                    WHERE pc.userId = :userId AND pc.action = '@control'
                    AND (
                        o.owner = :owner
                        OR EXISTS (
                            SELECT 1 FROM MapasCulturais\Entities\AgentOpportunity ao
                            WHERE ao.id = o.id AND ao.ownerEntity = :owner
                        )
                        OR EXISTS (
                            SELECT 1 FROM MapasCulturais\Entities\Registration r
                            WHERE r.opportunity = o AND r.owner = :owner
                        )
                    )";

            $query = $app->em->createQuery($dql);
            $query->setParameters([
                'owner' => $owner_agent,
                'userId' => $user->id
            ]);
            $query->setMaxResults(1);

            return (bool) $query->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function canUserView($user) {
        $base = parent::canUserView($user);
        if (!$base) {
            return false;
        }

        if ($this->seal->sensitive) {
            return $this->canUserViewSensitiveData($user);
        }

        return true;
    }

    protected function canUserCreate($user){
        $app = App::i();

        $can = !$app->isWorkflowEnabled() || $this->seal->canUser('@control', $user);

        return $this->owner->canUser('createSealRelation', $user) && $can;
    }

    protected function canUserModify($user){
        return $this->canUserCreate($user);
    }

    protected function canUserRemove($user){
        $app = App::i();

        $can = !$app->isWorkflowEnabled() || $this->seal->canUser('@control', $user);

        return $this->owner->canUser('removeSealRelation', $user) || $can;
    }
    
    protected function canUserPrint($user) {
        return $this->owner->canUser('@control', $user) || $this->seal->canUser('@control', $user);
    }
    
    public function isExpired() {
        if($this->seal->validPeriod > 0) {
            
            $today = new \DateTime();
            $expirationDate = $this->validateDate;
            return $expirationDate < $today;
            
        } else {
            return false;
        }
    }

    public function save($flush = false) {
        $app = App::i();
        try {
            if($this->owner instanceof Agent){
                $this->ownerRelation = $this->owner;
            } else {
                $this->ownerRelation = $this->owner->owner;
            }

            $is_new = $this->isNew();
            parent::save($flush);

            // Ao criar uma nova relação, gera registros em seal_relation_field
            if ($is_new && $this->seal) {
                $this->_createSealRelationFields();
                if ($flush) {
                    $app->em->flush();
                }
            }
            
        } catch (\MapasCulturais\Exceptions\PermissionDenied $e) {
            if (!App::i()->isWorkflowEnabled())
                throw $e;

            $app = App::i();
            $app->disableAccessControl();
            $this->status = self::STATUS_PENDING;      
            
            parent::save($flush);
            $app->enableAccessControl();

            $request = new RequestSealRelation;
            $request->setSealRelation($this);
            $request->save(true);

            throw new \MapasCulturais\Exceptions\WorkflowRequest([$request]);
        }
    }

    /**
     * Cria registros em SealRelationField com base na configuração do selo.
     * Campos sem expiry têm expiry_date = NULL.
     * 
     * @return void
     */
    protected function _createSealRelationFields() {
        $app = App::i();
        $config = (array) $this->seal->lockedFieldsConfig;

        if (empty($config)) {
            return;
        }

        foreach ($config as $field_name => $field_config) {
            $field_config = (array) $field_config;

            $seal_relation_field = new SealRelationField();
            $seal_relation_field->sealRelation = $this;
            $seal_relation_field->fieldName = $field_name;
            $seal_relation_field->expiryDate = $this->calculateSealRelationFieldExpiryDate($field_config);
            $seal_relation_field->isInvalidator = !empty($field_config['isInvalidator']);
            
            $app->em->persist($seal_relation_field);
        }

        // Inicializa computed_status como fully_valid para novas relações
        $this->computedStatus = 'fully_valid';
        $app->em->persist($this);
    }

    function delete($flush = false) {
        $this->checkPermission('remove');
        // ($originType, $originId, $destinationType, $destinationId, $metadata)
        $ruid = RequestSealRelation::generateRequestUid($this->owner->getClassName(), $this->owner->id, $this->seal->getClassName(), $this->seal->id, ['class' => $this->getClassName(), 'relationId' => $this->id]);
        $requests = App::i()->repo('RequestSealRelation')->findBy(['requestUid' => $ruid]);
        foreach($requests as $r)
            $r->delete($flush);

        parent::delete($flush);
    }

    function generateLink($url, $texto){
      return '<a href=' . $url . ' rel="noopener noreferrer"><i>' . $texto .'</i></a>';
    }
    
    /**
     * Retorna a mensagem de impressão do certificado. Se uma mensagem não foi definida pelo usuário, retorna uma mensagem padrão com todos os campos
     *
     * @param addLinks
     * @return mensagem de impressão
     */
    public function getCertificateText($addLinks = false){
        
        $app = App::i();
        $mensagem = $this->seal->certificateText;
        $entity = $this->seal;
        $nomeSelo = $addLinks ? $this->generateLink($app->createUrl('seal', 'single', ['id'=>$this->seal->id],
                    $this->seal->name), $this->seal->name) : $this->seal->name;

        $donoSelo = $addLinks ? $this->generateLink($this->seal->owner->getSingleUrl(),
                    $this->seal->owner->name) : $this->owner->name;

        $nomeEntidade = $addLinks ? $this->generateLink($this->owner->getSingleUrl(),
        $this->owner->name) : $this->owner->name;

        $dateInicio = $this->createTimestamp->format("d/m/Y");
        $seloExpira = isset($expirationDate);
        
        
        if($entity->validPeriod > 0){
            $dateFim = $this->validateDate->format('d/m/Y');
        }

        if(!empty($mensagem)){
            $mensagem = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$mensagem);
            $mensagem = str_replace("[sealName]",$nomeSelo,$mensagem);
            $mensagem = str_replace("[sealOwner]",$donoSelo,$mensagem);
            $mensagem = str_replace("[sealShortDescription]",$this->seal->shortDescription,$mensagem);
            $mensagem = str_replace("[sealRelationLink]",$app->createUrl('seal','printsealrelation',[$this->id]),$mensagem);
            $mensagem = str_replace("[entityDefinition]",$this->owner->entityTypeLabel,$mensagem);
            $mensagem = str_replace("[entityName]",$nomeEntidade,$mensagem);
            $mensagem = str_replace("[dateIni]",$dateInicio,$mensagem);

            if($entity->validPeriod > 0){
                $mensagem = str_replace("[dateFin]",$dateFim,$mensagem);
            } else {
                $mensagem = str_replace("[dateFin]",'',$mensagem);
            }
            
            $mensagem = preg_replace('/\v+|\\\r\\\n/','<br/>',$mensagem);
            
        }
        else{
            $mensagem = '<p>' . \MapasCulturais\i::__('<b>Nome do Selo</b>') . ': ' . $nomeSelo .'</p>';
            $mensagem = $mensagem . '<p>' . \MapasCulturais\i::__('<b>Dono do Selo</b>') . ': ' . $donoSelo . '</p>';
            $mensagem = $mensagem . '<p>' . \MapasCulturais\i::__('<b>Descrição Curta</b>') . ': ' . $this->seal->shortDescription .'</p>';
            $mensagem = $mensagem . '<p>' . \MapasCulturais\i::__('<b>Tipo de Entidade</b>') . ': ' . $this->owner->entityTypeLabel .'</p>';
            $mensagem = $mensagem . '<p>' . \MapasCulturais\i::__('<b>Nome da Entidade</b>') . ': ' . $nomeEntidade .'</p>';
            $mensagem = $mensagem . '<p>' . \MapasCulturais\i::__('<b>Data de Criação</b>') . ': ' . $dateInicio .'</p>';
            
            // Tirando daqui porque já está na view/seal/sealrelation.php
            //if($entity->validPeriod > 0){
            //    $mensagem = $mensagem . \MapasCulturais\i::__('Data de Expiração') . ': ' . $dateFim;
            //}
        }

        //hook para que plugins/temas possam adicionar metadados para substituição na mensagem
        $app->applyHook('sealRelation.certificateText', [&$mensagem, $this]);

        return $mensagem;
    }

    /**
     * Retorna a URL da single do selo
     * @return string 
     */
    public function getSingleUrl(): string
    {
        return App::i()->createUrl('seal', 'sealRelation', [$this->id]);
    }
    
    
}
