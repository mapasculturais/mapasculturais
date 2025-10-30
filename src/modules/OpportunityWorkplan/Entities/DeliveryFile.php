<?php
namespace OpportunityWorkplan\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class DeliveryFile extends \MapasCulturais\Entities\File{

    /**
     * @var \OpportunityWorkplan\Entities\Delivery
     */
    #[ORM\ManyToOne(targetEntity: "OpportunityWorkplan\Entities\Delivery")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;

    /**
     * @var \OpportunityWorkplan\Entities\DeliveryFile
     */
    #[ORM\ManyToOne(targetEntity: "OpportunityWorkplan\Entities\DeliveryFile", fetch: "EAGER")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;
}