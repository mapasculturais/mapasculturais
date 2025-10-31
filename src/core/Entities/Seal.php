<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Traits;

/**
 * Seal
 */
#[ORM\Table(name: "seal")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repositories\Seal")]
#[ORM\HasLifecycleCallbacks]
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
        Traits\EntityArchive;
        
    protected $__enableMagicGetterHook = true;

    const STATUS_RELATED = -1;

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "seal_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    protected $name;

    #[ORM\Column(name: "short_description", type: "text", nullable: true)]
    protected $shortDescription;

    #[ORM\Column(name: "long_description", type: "text", nullable: true)]
    protected $longDescription;

    #[ORM\Column(name: "certificate_text", type: "text", nullable: true)]
    protected $certificateText;

    #[ORM\Column(name: "valid_period", type: "smallint", nullable: false)]
    protected $validPeriod;

    #[ORM\Column(name: "create_timestamp", type: "datetime", nullable: false)]
    protected $createTimestamp;

    #[ORM\Column(name: "status", type: "smallint", nullable: false)]
    protected $status = self::STATUS_ENABLED;

    #[ORM\Column(name: "locked_fields", type: "json", nullable: true, options: ["default" => "[]"])]
    protected $lockedFields;

    #[ORM\Column(name: "update_timestamp", type: "datetime", nullable: true)]
    protected $updateTimestamp;
    
    #[ORM\Column(name: "subsite_id", type: "integer", nullable: true)]
    protected $_subsiteId;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Subsite")]
    #[ORM\JoinColumn(name: "subsite_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    protected $subsite;

    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do selo é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'validPeriod' => [
                'required' => \MapasCulturais\i::__('Validade do selo é obrigatória.'),
                'v::allOf(v::min(0),v::intVal())' => \MapasCulturais\i::__('Validade do selo deve ser um número inteiro.')
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
     * Define o valor de lockedFields parseando o valor
     * @return void 
     */
    protected function setLockedFields($_value) {
        $this->lockedFields = empty($_value) ? [] : (array) $_value;
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

    #[ORM\PrePersist]
    public function prePersist($args = null){ parent::prePersist($args); }
    #[ORM\PostPersist]
    public function postPersist($args = null){ parent::postPersist($args); }

    #[ORM\PreRemove]
    public function preRemove($args = null){ parent::preRemove($args); }
    #[ORM\PostRemove]
    public function postRemove($args = null){ parent::postRemove($args); }

    #[ORM\PreUpdate]
    public function preUpdate($args = null){ parent::preUpdate($args); }
    #[ORM\PostUpdate]
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
