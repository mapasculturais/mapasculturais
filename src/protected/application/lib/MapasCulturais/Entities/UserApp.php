<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * UserApp
 *
 * @ORM\Table(name="user_app")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class UserApp extends \MapasCulturais\Entity {

    use Traits\EntitySoftDelete;

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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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

    public function __construct() {
        $this->_publicKey = hash('sha256', uniqid(rand(), true));
        $this->_privateKey = hash('sha512', uniqid(rand(), true));
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