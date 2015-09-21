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
        $this->_publicKey = self::getToken(32);
        $this->_privateKey = self::getToken(64);
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
    
    /**
     * http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string/13733588#13733588
     */
    protected static function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1)
            return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    protected static function getToken($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[self::crypto_rand_secure(0, $max)];
        }
        return $token;
    }
    
    protected function canUserView($user){
        if($user->is('guest')){
            return false;
        }
        
        if($user->is('admin')){
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