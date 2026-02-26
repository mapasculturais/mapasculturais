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

    /**
     * Verifica se um campo de metadata é obrigatório
     *
     * Lógica:
     * 1. Se campo não está habilitado (inform) → não é obrigatório
     * 2. Se campo está habilitado mas não tem require → é opcional
     * 3. Se campo está habilitado E require = true → é obrigatório
     * 4. Se campo já tem valor preenchido → considera satisfeito (retorna true)
     *
     * @param string $metadata_key Nome do campo de metadata
     * @return bool Se o campo é obrigatório
     */
    public function isMetadataRequired(string $metadata_key):bool {
        // Mapeamento completo: campo → [inform, require]
        $metadata_map = [
            // MONITORAMENTO - Campos originais
            'accessibilityMeasures' => [
                'inform' => 'workplan_monitoringInformAccessibilityMeasures',
                'require' => 'workplan_monitoringRequireAccessibilityMeasures'
            ],
            'executedRevenue' => [
                'inform' => 'workplan_monitoringReportExecutedRevenue',
                'require' => 'workplan_monitoringRequireExecutedRevenue'
            ],
            'priorityAudience' => [
                'inform' => 'workplan_monitoringInformThePriorityAudience',
                'require' => 'workplan_monitoringRequirePriorityAudience'
            ],
            'availabilityType' => [
                'inform' => 'workplan_monitoringInformTheFormOfAvailability',
                'require' => 'workplan_monitoringRequireAvailabilityType'
            ],
            'numberOfParticipants' => [
                'inform' => 'workplan_registrationReportTheNumberOfParticipants',
                'require' => 'workplan_deliveryRequireExpectedNumberPeople'
            ],
            'participantProfile' => [
                'inform' => 'workplan_monitoringProvideTheProfileOfParticipants',
                'require' => 'workplan_monitoringRequireParticipantProfile'
            ],

            // PLANEJAMENTO - Campos originais
            'segmentDelivery' => [
                'inform' => 'workplan_registrationInformCulturalArtisticSegment',
                'require' => 'workplan_deliveryRequireSegment'
            ],
            'expectedNumberPeople' => [
                'inform' => 'workplan_registrationReportTheNumberOfParticipants',
                'require' => 'workplan_deliveryRequireExpectedNumberPeople'
            ],

            // PLANEJAMENTO - Novos campos
            'artChainLink' => [
                'inform' => 'workplan_deliveryInformArtChainLink',
                'require' => 'workplan_deliveryRequireArtChainLink'
            ],
            'totalBudget' => [
                'inform' => 'workplan_deliveryInformTotalBudget',
                'require' => 'workplan_deliveryRequireTotalBudget'
            ],
            'numberOfCities' => [
                'inform' => 'workplan_deliveryInformNumberOfCities',
                'require' => 'workplan_deliveryRequireNumberOfCities'
            ],
            'numberOfNeighborhoods' => [
                'inform' => 'workplan_deliveryInformNumberOfNeighborhoods',
                'require' => 'workplan_deliveryRequireNumberOfNeighborhoods'
            ],
            'mediationActions' => [
                'inform' => 'workplan_deliveryInformMediationActions',
                'require' => 'workplan_deliveryRequireMediationActions'
            ],
            'paidStaffByRole' => [
                'inform' => 'workplan_deliveryInformPaidStaffByRole',
                'require' => 'workplan_deliveryRequirePaidStaffByRole'
            ],
            'teamCompositionGender' => [
                'inform' => 'workplan_deliveryInformTeamComposition',
                'require' => 'workplan_deliveryRequireTeamCompositionGender'
            ],
            'teamCompositionRace' => [
                'inform' => 'workplan_deliveryInformTeamComposition',
                'require' => 'workplan_deliveryRequireTeamCompositionRace'
            ],
            'revenueType' => [
                'inform' => 'workplan_deliveryInformRevenueType',
                'require' => 'workplan_deliveryRequireRevenueType'
            ],
            'commercialUnits' => [
                'inform' => 'workplan_deliveryInformCommercialUnits',
                'require' => 'workplan_deliveryRequireCommercialUnits'
            ],
            'unitPrice' => [
                'inform' => 'workplan_deliveryInformCommercialUnits',
                'require' => 'workplan_deliveryRequireUnitPrice'
            ],

            // Campos gate (sempre obrigatórios quando habilitados - sem 'require')
            'hasCommunityCoauthors' => [
                'inform' => 'workplan_deliveryInformCommunityCoauthors',
                'require' => null // Gate sempre obrigatório
            ],
            'hasTransInclusionStrategy' => [
                'inform' => 'workplan_deliveryInformTransInclusion',
                'require' => null // Gate sempre obrigatório
            ],
            'hasAccessibilityPlan' => [
                'inform' => 'workplan_deliveryInformAccessibilityPlan',
                'require' => null // Gate sempre obrigatório
            ],
            'hasEnvironmentalPractices' => [
                'inform' => 'workplan_deliveryInformEnvironmentalPractices',
                'require' => null // Gate sempre obrigatório
            ],
            'hasPressStrategy' => [
                'inform' => 'workplan_deliveryInformPressStrategy',
                'require' => null // Gate sempre obrigatório
            ],
            'hasInnovationAction' => [
                'inform' => 'workplan_deliveryInformInnovation',
                'require' => null // Gate sempre obrigatório
            ],

            // Campos detail (condicionais - só obrigatórios se gate = true)
            'communityCoauthorsDetail' => [
                'inform' => 'workplan_deliveryInformCommunityCoauthors',
                'require' => 'workplan_deliveryRequireCommunityCoauthorsDetail',
                'gate' => 'hasCommunityCoauthors'
            ],
            'transInclusionActions' => [
                'inform' => 'workplan_deliveryInformTransInclusion',
                'require' => 'workplan_deliveryRequireTransInclusionActions',
                'gate' => 'hasTransInclusionStrategy'
            ],
            'expectedAccessibilityMeasures' => [
                'inform' => 'workplan_deliveryInformAccessibilityPlan',
                'require' => 'workplan_deliveryRequireExpectedAccessibilityMeasures',
                'gate' => 'hasAccessibilityPlan'
            ],
            'environmentalPracticesDescription' => [
                'inform' => 'workplan_deliveryInformEnvironmentalPractices',
                'require' => 'workplan_deliveryRequireEnvironmentalPracticesDescription',
                'gate' => 'hasEnvironmentalPractices'
            ],
            'innovationTypes' => [
                'inform' => 'workplan_deliveryInformInnovation',
                'require' => 'workplan_deliveryRequireInnovationTypes',
                'gate' => 'hasInnovationAction'
            ],

            // Campos independentes
            'communicationChannels' => [
                'inform' => 'workplan_deliveryInformCommunicationChannels',
                'require' => 'workplan_deliveryRequireCommunicationChannels'
            ],
            'documentationTypes' => [
                'inform' => 'workplan_deliveryInformDocumentationTypes',
                'require' => 'workplan_deliveryRequireDocumentationTypes'
            ],

            // MONITORAMENTO - Novos campos executados
            'executedNumberOfCities' => [
                'inform' => 'workplan_monitoringInformNumberOfCities',
                'require' => 'workplan_monitoringRequireNumberOfCities'
            ],
            'executedNumberOfNeighborhoods' => [
                'inform' => 'workplan_monitoringInformNumberOfNeighborhoods',
                'require' => 'workplan_monitoringRequireNumberOfNeighborhoods'
            ],
            'executedMediationActions' => [
                'inform' => 'workplan_monitoringInformMediationActions',
                'require' => 'workplan_monitoringRequireMediationActions'
            ],
            'executedCommercialUnits' => [
                'inform' => 'workplan_monitoringInformCommercialUnits',
                'require' => 'workplan_monitoringRequireCommercialUnits'
            ],
            'executedUnitPrice' => [
                'inform' => 'workplan_monitoringInformCommercialUnits',
                'require' => 'workplan_monitoringRequireUnitPrice'
            ],
            'executedPaidStaffByRole' => [
                'inform' => 'workplan_monitoringInformPaidStaffByRole',
                'require' => 'workplan_monitoringRequirePaidStaffByRole'
            ],
            'executedTeamCompositionGender' => [
                'inform' => 'workplan_monitoringInformTeamComposition',
                'require' => 'workplan_monitoringRequireTeamCompositionGender'
            ],
            'executedTeamCompositionRace' => [
                'inform' => 'workplan_monitoringInformTeamComposition',
                'require' => 'workplan_monitoringRequireTeamCompositionRace'
            ],
        ];

        // Campo não está no mapa → não é obrigatório
        if (!isset($metadata_map[$metadata_key])) {
            return false;
        }

        $config = $metadata_map[$metadata_key];
        $opportunity = $this->goal->workplan->registration->opportunity->firstPhase;

        // Se campo não está habilitado → não é obrigatório
        if (!($opportunity->{$config['inform']} ?? false)) {
            return false;
        }

        // Se campo tem gate, verifica se gate está ativo
        if (isset($config['gate'])) {
            $gate_value = $this->{$config['gate']} ?? null;
            // Se gate não for 'true' (string), o detail não é obrigatório
            if ($gate_value !== 'true') {
                return false;
            }
        }

        // Se é um campo gate (require = null), sempre é obrigatório quando habilitado
        if ($config['require'] === null) {
            return true;
        }

        // Se campo já tem valor preenchido, considera satisfeito
        if ($this->$metadata_key) {
            return true;
        }

        // Verifica configuração de obrigatoriedade
        return $opportunity->{$config['require']} ?? false;
    }

    /**
     * Valida campo JSON do tipo array de objetos
     * Ex: paidStaffByRole = [{role, quantity, totalValue}, ...]
     *
     * @param string $field Nome do campo
     * @return bool Se o campo tem conteúdo válido
     */
    protected function validateJsonArrayField(string $field): bool {
        $value = $this->$field;
        if (!$value) return false;

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) && count($decoded) > 0;
    }

    /**
     * Valida campo JSON do tipo objeto
     * Ex: teamCompositionGender = {cisMale: 5, cisFemale: 8, ...}
     *
     * @param string $field Nome do campo
     * @return bool Se o campo tem conteúdo válido
     */
    protected function validateJsonObjectField(string $field): bool {
        $value = $this->$field;
        if (!$value) return false;

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) && count($decoded) > 0;
    }

    /**
     * Valida campo multiselect (array)
     *
     * @param string $field Nome do campo
     * @return bool Se o campo tem ao menos uma opção selecionada
     */
    protected function validateMultiselectField(string $field): bool {
        $value = $this->$field;
        if (!$value) return false;

        $array = is_string($value) ? json_decode($value, true) : $value;
        return is_array($array) && count($array) > 0;
    }

    /**
     * Valida campo select (não pode ser null ou vazio)
     *
     * @param string $field Nome do campo
     * @return bool Se o campo tem valor selecionado
     */
    protected function validateSelectField(string $field): bool {
        $value = $this->$field;
        return !is_null($value) && $value !== '';
    }
}