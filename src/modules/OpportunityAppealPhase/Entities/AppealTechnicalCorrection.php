<?php

namespace OpportunityAppealPhase\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use MapasCulturais\Entity;
use MapasCulturais\Entities\User;

/**
 * @ORM\Table(
 *     name="appeal_technical_correction",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="appeal_technical_correction_sequence_uidx", columns={"appeal_registration_id", "sequence"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="MapasCulturais\Repository")
 */
class AppealTechnicalCorrection extends Entity
{
    public const STATUS_DRAFT = 0;
    public const STATUS_APPLIED = 1;
    public const STATUS_CONFIRMED_NO_CHANGE = 2;
    public const STATUS_DISCARDED = -1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="appeal_technical_correction_id_seq", allocationSize=1)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumn(name="appeal_registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $appealRegistration;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationEvaluation")
     * @ORM\JoinColumn(name="appeal_evaluation_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $appealEvaluation;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumn(name="source_registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $sourceRegistration;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumn(name="relator_user_id", referencedColumnName="id", nullable=false, onDelete="RESTRICT")
     */
    protected $relator;

    /** @ORM\Column(type="integer", nullable=false) */
    protected $sequence = 1;

    /** @ORM\Column(type="smallint", nullable=false) */
    protected $status = self::STATUS_DRAFT;

    /** @ORM\Column(type="text", nullable=false) */
    protected $reason = '';

    /** @ORM\Column(name="confirm_no_score_change", type="boolean", nullable=false, options={"default": false}) */
    protected $confirmNoScoreChange = false;

    /** @ORM\Column(name="before_consolidated_result", type="float", nullable=true) */
    protected $beforeConsolidatedResult;

    /** @ORM\Column(name="after_consolidated_result", type="float", nullable=true) */
    protected $afterConsolidatedResult;

    /** @ORM\Column(name="before_score", type="float", nullable=true) */
    protected $beforeScore;

    /** @ORM\Column(name="after_score", type="float", nullable=true) */
    protected $afterScore;

    /** @ORM\Column(name="before_eligible", type="boolean", nullable=true) */
    protected $beforeEligible;

    /** @ORM\Column(name="after_eligible", type="boolean", nullable=true) */
    protected $afterEligible;

    /** @ORM\Column(name="criteria_configuration_snapshot", type="json", nullable=false) */
    protected $criteriaConfigurationSnapshot = [];

    /**
     * @ORM\Version
     * @ORM\Column(type="integer", nullable=false, options={"default": 1})
     */
    protected $version = 1;

    /** @ORM\Column(name="create_timestamp", type="datetime", nullable=false) */
    protected $createTimestamp;

    /** @ORM\Column(name="update_timestamp", type="datetime", nullable=false) */
    protected $updateTimestamp;

    /**
     * @ORM\OneToMany(
     *     targetEntity="OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem",
     *     mappedBy="correction",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id"="ASC"})
     */
    protected $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        parent::__construct();
    }

    public function replaceDraft(string $reason, array $criteriaConfigurationSnapshot): void
    {
        $this->assertDraft();
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('O motivo da correção é obrigatório.');
        }

        $this->reason = $reason;
        $this->criteriaConfigurationSnapshot = $criteriaConfigurationSnapshot;
        $this->confirmNoScoreChange = false;
        $this->updateTimestamp = new \DateTime();
    }

    public function replaceRelator(User $relator): void
    {
        $this->assertDraft();
        $this->relator = $relator;
        $this->updateTimestamp = new \DateTime();
    }

    public function markApplied(array $before, array $after): void
    {
        $this->assertDraft();
        if (trim($this->reason) === '') {
            throw new DomainException('O motivo da correção é obrigatório.');
        }

        $this->captureTotals($before, $after);
        $this->status = self::STATUS_APPLIED;
        $this->confirmNoScoreChange = false;
        $this->updateTimestamp = new \DateTime();
    }

    public function markConfirmedNoChange(string $reason, array $current): void
    {
        $this->assertDraft();
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('A justificativa é obrigatória para deferir sem alterar a nota.');
        }

        $this->reason = $reason;
        $this->confirmNoScoreChange = true;
        $this->captureTotals($current, $current);
        $this->status = self::STATUS_CONFIRMED_NO_CHANGE;
        $this->updateTimestamp = new \DateTime();
    }

    public function discard(): void
    {
        $this->assertDraft();
        $this->status = self::STATUS_DISCARDED;
        $this->updateTimestamp = new \DateTime();
    }

    public function addItem(AppealTechnicalCorrectionItem $item): void
    {
        $this->assertDraft();
        foreach ($this->items as $existing) {
            if ($existing->targetEvaluation === $item->targetEvaluation) {
                throw new DomainException('Uma avaliação só pode aparecer uma vez na correção.');
            }
        }
        $item->correction = $this;
        $this->items->add($item);
    }

    public function clearItems(): void
    {
        $this->assertDraft();
        $this->items->clear();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    private function assertDraft(): void
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new DomainException('Uma correção finalizada é imutável.');
        }
    }

    private function captureTotals(array $before, array $after): void
    {
        $this->beforeConsolidatedResult = $before['consolidatedResult'] ?? null;
        $this->afterConsolidatedResult = $after['consolidatedResult'] ?? null;
        $this->beforeScore = $before['score'] ?? null;
        $this->afterScore = $after['score'] ?? null;
        $this->beforeEligible = $before['eligible'] ?? null;
        $this->afterEligible = $after['eligible'] ?? null;
    }

    protected function canUserCreate($user): bool
    {
        return false;
    }

    protected function canUserModify($user): bool
    {
        return false;
    }

    protected function canUserRemove($user): bool
    {
        return false;
    }
}
