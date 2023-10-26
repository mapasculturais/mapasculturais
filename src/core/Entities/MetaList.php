<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * File
 *
 * @property-read int $id MetaList Id
 * @property-read string $name MetaList name
 * @property-read string $group MetaList Group (link, video etc)
 * @property-read string $objectType MetaList Owner Class Name
 * @property-read id $objectId MetaList Owner Id
 * @property-read \DateTime $createTimestamp MetaList Create Timestamp
 * @property-read \MapasCulturais\Entity $owner The Owner of this MetaList
 *
 * @ORM\Table(name="MetaList")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\MetaList")
 * @ORM\HasLifecycleCallbacks
 */
class MetaList extends \MapasCulturais\Entity
{
    use \MapasCulturais\Traits\EntityMetaLists;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="file_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;


    /**
     * @var string
     *
     * @ORM\Column(name="grp", type="string", length=32, nullable=false)
     */
    public $group;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    public $title;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    public $value;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    public $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="string", nullable=false)
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * The owner entity of this file
     * @var \MapasCulturais\Entity
     */
    protected $_owner;

    static function getValidations() {
        $app = App::i();
        $validations = [
            'title' => [
                'required' => \MapasCulturais\i::__('O título é obrigatório'),
                'v::stringType()->length(0,255)' => \MapasCulturais\i::__('O título não pode ter mais que 255 caracteres')
            ],
            'value' => [
                'required' => \MapasCulturais\i::__('A o valor é obrigatória')
            ]
        ];

        $prefix = self::getHookPrefix();
        // entity(Agent).validations
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    public function getGroup(){
        return trim($this->group);
    }

    public function setGroup($val){
        if($this->id)
            $this->group = trim($val);
    }

    /**
     * Returns the owner of this metadata
     * @return \MapasCulturais\Entity
     */
    public function getOwner(){
        if(!$this->_owner && ($this->objectType && $this->objectId))
            $this->_owner = App::i()->repo($this->objectType)->find($this->objectId);

        return $this->_owner;
    }

    /**
     * Set the owner of this metadata
     * @param \MapasCulturais\Entity $owner
     */
    public function setOwner(\MapasCulturais\Entity $owner){
        $this->_owner = $owner;
        $this->objectType = $owner->className;
        $this->objectId = $owner->id;
    }
    
    public function canUser($action, $userOrAgent = null) {
        $owner = $this->getOwner();

        if(strtolower($action) === 'remove'){
            $action = 'modify';
        }
        
        return $owner ? $owner->canUser($action, $userOrAgent) : false;
    }

    /**
     * Returns the url to this file
     * @return string the url to this file
     */
    public function getUrl(){
        return App::i()->storage->getUrl($this);
    }

    public function getPath(){
        return App::i()->storage->getPath($this);
    }

    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').insert:before');
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').insert:after');
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').remove:before');
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').remove:after');
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').update:before');
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').metalist(' . $this->group . ').update:after');
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

    public function __toString() {
        return $this->title .'-'. $this->value;
    }
}
