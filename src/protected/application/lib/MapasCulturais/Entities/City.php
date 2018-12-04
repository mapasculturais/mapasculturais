<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Term
 *
 * @ORM\Table(name="city")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\City")
 * @ORM\HasLifecycleCallbacks
 */
class City extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="city_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;



    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;


    /**
     * @var \MapasCulturais\Entities\State
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\State", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     * })
     */
    protected $state;


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