<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * UserApp
 * 
 * @property int $id
 * @property User $user
 * @property string $name
 * @property int $status
 * @property Subsite $subsite
 * 
 * @property-read string $publicKey
 * @property-read string $privateKey
 *
 * @ORM\Table(name="user_app")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class UserApp extends \MapasCulturais\Entity {

    use Traits\EntitySoftDelete,
        Traits\EntityOriginSubsite;

    /**
     * @var string
     *
     * @ORM\Column(name="public_key", type="string", nullable=false)
     * @ORM\Id
     */
    protected $_publicKey;

    /**
     * @var string
     *
     * @ORM\Column(name="private_key", type="string", nullable=false)
     */
    protected $_privateKey;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */

    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;
    
    
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

    public function __construct() {
        $this->_publicKey = App::getToken(32);
        $this->_privateKey = App::getToken(64);
        $this->user = App::i()->user;
        parent::__construct();
    }

    function getPublicKey() {
        return $this->_publicKey;
    }

    function getPrivateKey() {
        return $this->_privateKey;
    }

    function getId() {
        return $this->_publicKey;
    }
    
    protected function canUserView($user){
        if($user->is('guest')){
            return false;
        }
        
        if($user->is('admin', $user->profile->getSubsiteId())){
            return true;
        }
        
        return $user->equals($this->user);
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