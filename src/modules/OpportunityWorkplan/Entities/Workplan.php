<?php
namespace OpportunityWorkplan\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MapasCulturais\Traits\EntityMetadata;
use MapasCulturais\Traits\EntityOwnerAgent;

/**
 * 
 * @ORM\Table(name="registration_workplan")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Workplan extends \MapasCulturais\Entity {

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
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $registration;

    /**
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\Goal::class, mappedBy="workplan", cascade={"persist", "remove"}, orphanRemoval=true)
    */
    protected $goals;

    /**
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\WorkplanMeta::class, mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
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
    

    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function __construct() {
        $this->goals = new ArrayCollection();
        parent::__construct();
    }

    public function jsonSerialize(): array
    {
        $metadatas = $this->getMetadata();

        $sortedGoals = $this->goals->toArray();

        usort($sortedGoals, function ($a, $b) {
            return $a->id <=> $b->id;
        });

        return [
            'id' => $this->id,
            'registrationId' => $this->registration->id,
            'registration' => $this->registration,
            'goals' => $sortedGoals,
            ...$metadatas
        ];
    }


}