<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * RegistrationFieldConfiguration
 *
 * @ORM\Table(name="registration_field_configuration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationFieldConfiguration extends \MapasCulturais\Entity {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_field_configuration_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="max_size", type="string", nullable=true)
     */
    protected $maxSize;

    /**
     * @var boolean
     *
     * @ORM\Column(name="categories", type="array", nullable=true)
     */
    protected $categories = [];

    /**
     * @var boolean
     *
     * @ORM\Column(name="required", type="boolean", nullable=false)
     */
    protected $required = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="field_type", type="string", length=255, nullable=false)
     */
    protected $fieldType = null;
    
    /**
     * @var string
     *
     * @ORM\Column(name="field_options", type="array", length=255, nullable=false)
     */
    protected $fieldOptions = [];

    static function getValidations() {
        return [
            'owner' => [ 
                'required' => \MapasCulturais\i::__("O projeto é obrigatório.")
            ],
            'title' => [ 
                'required' => \MapasCulturais\i::__("O título do anexo é obrigatório.")
            ],
            'fieldType' => [ 
                'required' => \MapasCulturais\i::__("O tipo de campo é obrigatório")
            ]
        ];
    }

    public function setOwnerId($id){
//        $this->owner = $this->repo()->find('project', $id);
        $this->owner = App::i()->repo('Project')->find($id);
    }
    
    public function setFieldOptions($value){
        if(!is_array($value)){
            $value = explode("\n", $value);
        }
        
        $this->fieldOptions = $value;
    }
    
    public function setCategories($value) {
        if(!$value){
            $value = [];
        } else if (!is_array($value)){
            $value = explode("\n", $value);
        }
        $this->categories = $value;
    }
    
    public function getFieldName(){
        return 'field_' . $this->id;
    }
    
    /**
     * 
     * @return \MapasCulturais\Definitions\RegistrationFieldType
     */
    public function getFieldTypeDefinition(){
        return App::i()->getRegisteredRegistrationFieldTypeBySlug($this->fieldType);
    }

    public function jsonSerialize() {

        return [
        'id' => $this->id,
        'ownerId' => $this->owner->id,
        'title' => $this->title,
        'description' => $this->description,
        'maxSize' => $this->maxSize,
        'required' => $this->required,
        'fieldType' => $this->fieldType,
        'fieldOptions' => $this->fieldOptions,
        'categories' => $this->categories,
        'fieldName' => $this->getFieldName()
        ];
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

    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').insert:before', $args);
        
        if(!$this->getFieldTypeDefinition()->requireValuesConfiguration){
            $this->fieldOptions = [];
        }
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').insert:after', $args);
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').remove:before', $args);
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').remove:after', $args);
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').update:before', $args);
        
        if(!$this->getFieldTypeDefinition()->requireValuesConfiguration){
            $this->fieldOptions = [];
        }
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).fieldConfiguration(' . $this->fieldType . ').update:after', $args);
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
