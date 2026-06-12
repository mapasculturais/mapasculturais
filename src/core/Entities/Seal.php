<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Traits;

/**
 * Seal
 *
 * @ORM\Table(name="seal")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Seal")
 * @ORM\HasLifecycleCallbacks
 */
class Seal extends \MapasCulturais\Entity
{
    use Traits\EntityMetadata,
    	Traits\EntityOwnerAgent,
        Traits\EntityMetaLists,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityAgentRelation,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityArchive,
        Traits\EntityRevision;
        
    protected $__enableMagicGetterHook = true;

    const STATUS_RELATED = -1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seal_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    protected $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="certificate_text", type="text", nullable=true)
     */
    protected $certificateText;

    /**
     * @var integer
     *
     * @ORM\Column(name="valid_period", type="smallint", nullable=false)
     */
    protected $validPeriod;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;


    /**
     * @var object
     *
     * @ORM\Column(name="locked_fields", type="json", nullable=true, options={"default" : "[]"})
     */
    protected $lockedFields;

    /**
     * @var object
     *
     * @ORM\Column(name="locked_fields_config", type="json", nullable=false, options={"default" : "{}"})
     */
    protected $lockedFieldsConfig = [];

    /**
     * @var boolean
     *
     * @ORM\Column(name="sensitive", type="boolean", nullable=false, options={"default" : "false"})
     */
    protected $sensitive = false;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealMeta", mappedBy="owner", cascade={"remove","persist"}, fetch="EAGER")
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SealFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealFile", mappedBy="owner", cascade={"remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\SealAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealAgentRelation", mappedBy="owner", cascade={"remove"})
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__agentRelations;
    
    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealPermissionCache", mappedBy="owner", cascade={"remove"}, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;
    
    
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
     *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $subsite;

    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do selo é obrigatório'),
                'v::stringType()->length(1,255)' => \MapasCulturais\i::__('O nome do selo deve ter entre 1 e 255 caracteres')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(1,400)' => \MapasCulturais\i::__('A descrição curta deve ter entre 1 e 400 caracteres')
            ],
            'longDescription' => [
                'v::stringType()' => \MapasCulturais\i::__('A descrição longa deve ser um texto')
            ],
            'certificateText' => [
                'v::stringType()' => \MapasCulturais\i::__('O conteúdo do certificado deve ser um texto')
            ],
            'validPeriod' => [
                'required' => \MapasCulturais\i::__('Validade do selo é obrigatória.'),
                'v::allOf(v::min(0),v::intVal())' => \MapasCulturais\i::__('Validade do selo deve ser um número inteiro maior ou igual a zero.')
            ],
            'sensitive' => [
                'v::boolVal()' => \MapasCulturais\i::__('O campo sensível deve ser um booleano')
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    static function getControllerClassName()
    {
        return 'Seals\\Controller';
    }

    
    /**
     * Flag interna para garantir que, quando ambos lockedFields e lockedFieldsConfig
     * forem enviados no mesmo request, lockedFieldsConfig prevaleça.
     * @var bool
     */
    protected $__lockedFieldsConfigSetInRequest = false;

    /**
     * Define o valor de lockedFields parseando o valor
     * @return void 
     */
    protected function setLockedFields($_value) {
        // Proteção contra dual-write: se lockedFieldsConfig também foi enviado no request,
        // ignora o valor de lockedFields e sincroniza a partir de lockedFieldsConfig.
        if ($this->__lockedFieldsConfigSetInRequest) {
            $this->lockedFields = array_keys((array) $this->lockedFieldsConfig);
            return;
        }

        $this->lockedFields = empty($_value) ? [] : (array) $_value;
        
        // Dual-write: sincroniza locked_fields_config a partir de locked_fields
        $config = [];
        foreach ($this->lockedFields as $field) {
            $config[$field] = [
                'hasExpiry' => false,
                'periodValue' => null,
                'periodUnit' => null,
                'isInvalidator' => false,
            ];
        }
        $this->lockedFieldsConfig = $config;
    }

    /**
     * Define o valor de lockedFieldsConfig com validação de schema e whitelist
     * @param array|object $_value 
     * @return void 
     */
    protected function setLockedFieldsConfig($_value) {
        $config = empty($_value) ? [] : (array) $_value;
        
        // Whitelist de chaves permitidas por campo
        $allowed_field_keys = ['hasExpiry', 'periodValue', 'periodUnit', 'isInvalidator'];
        $allowed_period_units = ['day', 'month', 'year'];
        
        $validated = [];
        foreach ($config as $field_name => $field_config) {
            // Validação da chave do campo
            if (!is_string($field_name) || trim($field_name) === '') {
                throw new \MapasCulturais\Exceptions\BadRequest(
                    \MapasCulturais\i::__('A chave do campo em lockedFieldsConfig deve ser uma string não vazia')
                );
            }

            if (!preg_match('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_:\.-]+$/', $field_name)) {
                throw new \MapasCulturais\Exceptions\BadRequest(
                    \MapasCulturais\i::__('A chave do campo em lockedFieldsConfig deve seguir o padrão controllerId.field')
                );
            }

            $field_config = (array) $field_config;

            // Rejeita chaves não permitidas
            $invalid_keys = array_diff(array_keys($field_config), $allowed_field_keys);
            if ($invalid_keys) {
                throw new \MapasCulturais\Exceptions\BadRequest(
                    \MapasCulturais\i::__('Chaves inválidas em lockedFieldsConfig: ') . implode(', ', $invalid_keys)
                );
            }

            $has_expiry = filter_var(
                $field_config['hasExpiry'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
            $is_invalidator = filter_var(
                $field_config['isInvalidator'] ?? false,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;

            $field_config['hasExpiry'] = $has_expiry;
            $field_config['isInvalidator'] = $is_invalidator;

            // Se não tem expiração, limpa campos relacionados à expiração
            if (!$has_expiry) {
                unset($field_config['periodValue'], $field_config['periodUnit'], $field_config['isInvalidator']);
            }

            // Validação: campo sem expiração não pode ser invalidador
            if ($is_invalidator && !$has_expiry) {
                throw new \MapasCulturais\Exceptions\BadRequest(
                    \MapasCulturais\i::__('Campo sem expiração não pode ser marcado como invalidador')
                );
            }

            // Validação de expiração
            if ($has_expiry) {
                if (!isset($field_config['periodValue']) || $field_config['periodValue'] === '' || $field_config['periodValue'] === null) {
                    throw new \MapasCulturais\Exceptions\BadRequest(
                        \MapasCulturais\i::__('O valor do período é obrigatório quando há expiração')
                    );
                }

                if (!is_numeric($field_config['periodValue']) || (int) $field_config['periodValue'] != $field_config['periodValue']) {
                    throw new \MapasCulturais\Exceptions\BadRequest(
                        \MapasCulturais\i::__('O valor do período deve ser um número inteiro')
                    );
                }

                $period_value = (int) $field_config['periodValue'];
                if ($period_value < 1) {
                    throw new \MapasCulturais\Exceptions\BadRequest(
                        \MapasCulturais\i::__('O valor do período deve ser um inteiro maior ou igual a 1')
                    );
                }

                if (!isset($field_config['periodUnit']) || !in_array($field_config['periodUnit'], $allowed_period_units, true)) {
                    throw new \MapasCulturais\Exceptions\BadRequest(
                        \MapasCulturais\i::__('A unidade do período deve ser um dos valores: day, month, year')
                    );
                }

                $field_config['periodValue'] = $period_value;
            }

            $validated[$field_name] = array_intersect_key($field_config, array_flip($allowed_field_keys));
        }
        
        $this->__lockedFieldsConfigSetInRequest = true;
        $this->lockedFieldsConfig = $validated;
        
        // Dual-write: sincroniza locked_fields legado a partir de locked_fields_config
        $this->lockedFields = array_keys($validated);
    }

    /**
     * Define o valor de sensitive
     * @param bool $value
     * @return void
     */
    protected function setSensitive($value) {
        $this->sensitive = (bool) $value;
    }

    function validatePeriod($value) {
    	if (!is_numeric($value)) {
    		return false;
    	} elseif ($value < 0) {
    		return false;
    	}
    	return true;
    }

    static function getPCachePermissionsList()
    {
        $permissions = parent::getPCachePermissionsList();
        $permissions[] = 'applySeal';

        return $permissions;
    }

    protected function canUserRemove($user) {
        $app = App::i();
        
        if(in_array($this->id, $app->config['app.verifiedSealsIds'])) {
            return false;
        } else {
            return parent::canUserRemove($user);
        }
    }

    protected function canUserArchive($user) {
        $app = App::i();
        
        if(in_array($this->id, $app->config['app.verifiedSealsIds'])) {
            return false;
        } else {
            return parent::canUserRemove($user);
        }
    }

    protected function canUserApplySeal($user) {
        if ($user->is('guest')) {
            return false;
        }

        if ($this->isUserAdmin($user)) {
            return true;
        }

        if ($this->canUser('@control', $user)) {
            return true;
        }

        return false;
    }
    
    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return \MapasCulturais\i::__('Selos');
        else
            return \MapasCulturais\i::__('Selo');
    }


    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){
        $this->__lockedFieldsConfigSetInRequest = false;
        parent::prePersist($args);
    }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){
        $this->__lockedFieldsConfigSetInRequest = false;
        parent::preUpdate($args);
    }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
