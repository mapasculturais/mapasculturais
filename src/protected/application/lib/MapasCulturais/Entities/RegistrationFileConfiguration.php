<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * RegistrationMeta
 *
 * @ORM\Table(name="registration_file_configuration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationFileConfiguration extends \MapasCulturais\Entity {

    use \MapasCulturais\Traits\EntityFiles;

    protected static $validations = [
        'owner' => [ 'required' => "O projeto é obrigatório."],
        'title' => [ 'required' => "O título do anexo é obrigatório."]
    ];
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_file_configuration_id_seq", allocationSize=1, initialValue=1)
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
     * @var boolean
     *
     * @ORM\Column(name="required", type="boolean", nullable=false)
     */
    protected $required = false;
    
    /**
     * @var \MapasCulturais\Entities\AgentFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    public function getFileGroupName(){
        return 'rfc_' . $this->id;
    }

    public function setOwnerId($id){
//        $this->owner = $this->repo()->find('project', $id);
        $this->owner = App::i()->repo('Project')->find($id);
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'ownerId' => $this->owner->id,
            'title' => $this->title,
            'description' => $this->description,
            'required' => $this->required,
            'template' => $this->getFile('registrationFileTemplate'),
            'groupName' => $this->fileGroupName
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
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').insert:before', $args);
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').insert:after', $args);
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').remove:before', $args);
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').remove:after', $args);
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').update:before', $args);
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(registration).meta(' . $this->key . ').update:after', $args);
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
