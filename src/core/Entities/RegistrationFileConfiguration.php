<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * RegistrationMeta
 * 
 *  $owner
 *  @property RegistrationStep $step
 *  @property string $title
 *  @property string $description
 *  @property bool $required
 *  @property string[] $categories
 *  @property int $displayOrder
 *  @property bool $conditional
 *  @property string $conditionalField
 *  @property string $conditionalValue
 *  @property array $registrationRanges
 *  @property string[] $proponentTypes
 * 
 * @property-read string $fileGroupName 
 */
#[ORM\Table(name: "registration_file_configuration")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
#[ORM\HasLifecycleCallbacks]
class RegistrationFileConfiguration extends \MapasCulturais\Entity {

    use \MapasCulturais\Traits\EntityFiles;

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "registration_file_configuration_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Opportunity")]
    #[ORM\JoinColumn(name: "opportunity_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\RegistrationStep")]
    #[ORM\JoinColumn(name: "step_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $step;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: false)]
    protected $title;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    protected $description;

    #[ORM\Column(name: "required", type: "boolean", nullable: false)]
    protected $required = false;

    #[ORM\Column(name: "categories", type: "json", nullable: true)]
    protected $categories = [];

    #[ORM\Column(name: "display_order", type: "smallint", nullable: false)]
    protected $displayOrder = 255;

    #[ORM\Column(name: "conditional", type: "boolean", nullable: false)]
    protected $conditional;

    #[ORM\Column(name: "conditional_field", type: "string", length: 255, nullable: false)]
    protected $conditionalField;

    #[ORM\Column(name: "conditional_value", type: "string", length: 255, nullable: false)]
    protected $conditionalValue;

    #[ORM\Column(name: "registration_ranges", type: "json", nullable: true)]
    protected $registrationRanges = [];

    #[ORM\Column(name: "proponent_types", type: "json", nullable: true)]
    protected $proponentTypes = [];

    #[ORM\OneToMany(targetEntity: "MapasCulturais\Entities\RegistrationFileConfigurationFile", mappedBy: "owner", cascade: ["remove"], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "object_id", onDelete: "CASCADE")]
    protected $__files;

    static function getValidations() {
        $app = App::i();
        $validations = [
            'owner' => [
                'required' => \MapasCulturais\i::__("A oportunidade é obrigatória.")
            ],
            'title' => [
                'required' => \MapasCulturais\i::__("O título do anexo é obrigatório.")
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    public function getFileGroupName(){
        return 'rfc_' . $this->id;
    }

    public function setOwnerId($id){
        $this->owner = App::i()->repo('Opportunity')->find($id);
    }

    public function setCategories($value) {
        if(!$value){
            $value = [];
        } else if (!is_array($value)){
            $value = explode("\n", $value);
        }
        $this->categories = $value;
    }

    public function setRegistrationRanges($value) {
        if(!$value){
            $value = [];
        } else if (!is_array($value)){
            $value = explode("\n", $value);
        }
        $this->registrationRanges = $value;
    }

    public function setStep(int|RegistrationStep $step) {
        if (is_int($step)) {
            $app = \MapasCulturais\App::i();
            $step = $app->repo('RegistrationStep')->find($step);
        }
        $this->step = $step;
    }

    public function setProponentTypes($value) {
        if(!$value){
            $value = [];
        } else if (!is_array($value)){
            $value = explode("\n", $value);
        }
        $this->proponentTypes = $value;
    }

    public function jsonSerialize(): array {
        $result = [
            'id' => $this->id,
            'ownerId' => $this->owner->id,
            'title' => $this->title,
            'description' => $this->description,
            'required' => $this->required,
            'template' => $this->getFile('registrationFileTemplate'),
            'groupName' => $this->fileGroupName,
            'categories' => $this->categories ?: [],
            'displayOrder' => $this->displayOrder,
            'conditional' => filter_var($this->conditional, FILTER_VALIDATE_BOOLEAN),
            'conditionalField' => $this->conditionalField,
            'conditionalValue' => $this->conditionalValue,
            'registrationRanges' => $this->registrationRanges ?: [],
            'proponentTypes' => $this->proponentTypes ?: [],
            'step' => $this->step ?? null,
        ];

        $app = App::i();

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.jsonSerialize", [&$result]);

        return $result;
    }

    protected function _canUser($user){
        return $this->owner->canUser('modifyRegistrationFields', $user);
    }

    protected function canUserModify($user){
        return $this->_canUser($user);
    }

    protected function canUserRemove($user){
        return $this->_canUser($user);
    }

    protected function canUserCreate($user){
        return $this->_canUser($user);
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