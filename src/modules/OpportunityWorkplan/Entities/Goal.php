<?php
namespace OpportunityWorkplan\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use MapasCulturais\Traits\EntityMetadata;
use MapasCulturais\Traits\EntityOwnerAgent;

/**
 * 
 * @ORM\Table(name="registration_workplan_goal")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Goal extends \MapasCulturais\Entity {


    use EntityMetadata,
        EntityOwnerAgent;

    /**
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;

    /**
     *
     * @ORM\ManyToOne(targetEntity=\OpportunityWorkplan\Entities\Workplan::class, inversedBy="goals"))
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workplan_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $workplan;

    /**
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\Delivery::class, mappedBy="goal", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $deliveries;

    /**
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\GoalMeta::class, mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

       /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

        /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function __construct() {
        $this->deliveries = new ArrayCollection();
        parent::__construct();
    }

    public function jsonSerialize(): array
    {
        $sortedDeliveries = $this->deliveries->toArray();

        usort($sortedDeliveries, function ($a, $b) {
            return $a->id <=> $b->id;
        });

        $metadatas = $this->getMetadata();

        return [
            'id' => $this->id,
            'deliveries' => $sortedDeliveries,
            ...$metadatas
        ];
    }
}