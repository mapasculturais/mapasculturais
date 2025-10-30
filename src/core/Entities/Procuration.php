<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Procuration
 * 
 * @property-read string $token
 * @property string $action
 * @property string $user
 * @property string $attorney
 * @property string $validUntilTimestamp
 */
#[ORM\Table(name: "procuration", indexes: [
    new ORM\Index(name: "procuration_usr_idx", columns: ["usr_id"]),
    new ORM\Index(name: "procuration_attorney_idx", columns: ["attorney_user_id"]),
])]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class Procuration extends \MapasCulturais\Entity{

    #[ORM\Column(name: "token", type: "string", length: 32, nullable: false)]
    #[ORM\Id]
    public $id;

    #[ORM\Column(name: "action", type: "string", length: 255, nullable: false)]
    protected $action;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\User", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "usr_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected $user;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\User", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "attorney_user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected $attorney;

    #[ORM\Column(name: "create_timestamp", type: "datetime", nullable: false)]
    protected $createTimestamp;

    #[ORM\Column(name: "valid_until_timestamp", type: "datetime", nullable: true)]
    protected $validUntilTimestamp;

    public function __construct() {
        $this->_token = App::getToken(32);
        $this->user = App::i()->user;
        
        parent::__construct();
    }
    
    function getToken() {
        return $this->id;
    }

    function isValid(){
        $current_date = new \DateTime();
        return is_null($this->validUntilTimestamp) || $current_date < $this->validUntilTimestamp;
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