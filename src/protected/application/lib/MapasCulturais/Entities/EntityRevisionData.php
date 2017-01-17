<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Entity Revision Data
 *
 * @ORM\Table(name="entity_revision_data")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EntityRevisionData extends \MapasCulturais\Entity{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="revision_data_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    protected $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", nullable=false)
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    protected $value;

    public function __construct() {
        $this->timestamp = new \DateTime;
    }

    public function setValue($value) {
        $this->value = json_encode($value);
    }

    public function getValue() {
        return json_decode($this->value);
    }

    public function canUser($action, $userOrAgent = null){
        return true;
    }
}
