<?php
namespace OpportunityWorkplan\Entities;

use MapasCulturais\i;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits\EntityFiles;
use MapasCulturais\Traits\EntityMetadata;
use MapasCulturais\Traits\EntityOwnerAgent;

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
        EntityOwnerAgent,
        EntityFiles;

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
    * @ORM\OneToMany(targetEntity=\OpportunityWorkplan\Entities\DeliveryMeta::class, mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true, fetch="EAGER")
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
     * @var \OpportunityWorkplan\Entities\DeliveryFile[] Files
     *
     * @ORM\OneToMany(targetEntity="OpportunityWorkplan\Entities\DeliveryFile", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

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
            '@entityType' => 'delivery',
            'id' => $this->id,
            'status' => $this->status,
            'files' => $this->files,
            ...$metadatas
        ];
    }

    public function isMetadataRequired(string $metadata_key):bool {
        $metadata_map = [
            'accessibilityMeasures' => 'workplan_monitoringInformAccessibilityMeasures',
            'executedRevenue'       => 'workplan_monitoringReportExecutedRevenue',
            'priorityAudience'      => 'workplan_monitoringInformThePriorityAudience',
            'availabilityType'      => 'workplan_monitoringInformTheFormOfAvailability',
            'numberOfParticipants'  => 'workplan_registrationReportTheNumberOfParticipants',
            'participantProfile'    => 'workplan_monitoringProvideTheProfileOfParticipants',
            
            // Novos campos de planejamento
            'artChainLink'                    => 'workplan_deliveryInformArtChainLink',
            'totalBudget'                     => 'workplan_deliveryInformTotalBudget',
            'numberOfCities'                  => 'workplan_deliveryInformNumberOfCities',
            'numberOfNeighborhoods'           => 'workplan_deliveryInformNumberOfNeighborhoods',
            'mediationActions'                => 'workplan_deliveryInformMediationActions',
            'paidStaffByRole'                 => 'workplan_deliveryInformPaidStaffByRole',
            'teamCompositionGender'           => 'workplan_deliveryInformTeamComposition',
            'teamCompositionRace'             => 'workplan_deliveryInformTeamComposition',
            'revenueType'                     => 'workplan_deliveryInformRevenueType',
            'commercialUnits'                 => 'workplan_deliveryInformCommercialUnits',
            'unitPrice'                       => 'workplan_deliveryInformCommercialUnits',
            'hasCommunityCoauthors'           => 'workplan_deliveryInformCommunityCoauthors',
            'hasTransInclusionStrategy'       => 'workplan_deliveryInformTransInclusion',
            'transInclusionActions'           => 'workplan_deliveryInformTransInclusion',
            'hasAccessibilityPlan'            => 'workplan_deliveryInformAccessibilityPlan',
            'expectedAccessibilityMeasures'   => 'workplan_deliveryInformAccessibilityPlan',
            'hasEnvironmentalPractices'       => 'workplan_deliveryInformEnvironmentalPractices',
            'environmentalPracticesDescription' => 'workplan_deliveryInformEnvironmentalPractices',
            'hasPressStrategy'                => 'workplan_deliveryInformPressStrategy',
            'communicationChannels'           => 'workplan_deliveryInformCommunicationChannels',
            'hasInnovationAction'             => 'workplan_deliveryInformInnovation',
            'innovationTypes'                 => 'workplan_deliveryInformInnovation',
            'documentationTypes'              => 'workplan_deliveryInformDocumentationTypes',
        ];

        if ($this->$metadata_key) {
            return true;
        }
        
        $opportunity = $this->goal->workplan->registration->opportunity->firstPhase;
        $opportunity_metadata = $metadata_map[$metadata_key];

        return $opportunity->$opportunity_metadata ?? false;
    }
}