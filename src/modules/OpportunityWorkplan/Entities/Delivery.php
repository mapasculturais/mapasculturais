<?php
namespace OpportunityWorkplan\Entities;

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\Traits\EntityMetadata;
use MapasCulturais\Traits\EntityOwnerAgent;
use MapasCulturais\i;

/**
 * 
 * @ORM\Table(name="registration_workplan_goal_delivery")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Delivery extends \MapasCulturais\Entity {

    const STATUS_SCHEDULED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_OVERDUE = 2;
    const STATUS_CANCELED = 3;
    const STATUS_COMPLETED = 10;

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
     * @ORM\ManyToOne(targetEntity=\OpportunityWorkplan\Entities\Goal::class, inversedBy="deliveries"))
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="goal_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $goal;

    /**
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\DeliveryMeta::class, mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
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

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_SCHEDULED;

    /**
     * Retorna array com os nomes dos status
     * 
     * @return array
     */
    static function getStatusesNames() {
        return [
            self::STATUS_SCHEDULED   => i::__('Programada'),
            self::STATUS_IN_PROGRESS => i::__('Em andamento'),
            self::STATUS_OVERDUE     => i::__('Atrasada'),
            self::STATUS_CANCELED    => i::__('Cancelada'),
            self::STATUS_COMPLETED   => i::__('Concluída')
        ];
    }

    public function jsonSerialize(): array
    {
        $metadatas = $this->getMetadata();

        return [
            'id' => $this->id,
            ...$metadatas
        ];
    }
}