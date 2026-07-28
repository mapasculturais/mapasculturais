<?php

namespace OpportunityAppealPhase\Entities;

use Doctrine\ORM\Mapping as ORM;
use DomainException;
use MapasCulturais\Entity;

/**
 * @ORM\Table(
 *     name="appeal_technical_correction_item",
 *     indexes={
 *         @ORM\Index(name="appeal_technical_correction_item_evaluation_idx", columns={"target_evaluation_id"}),
 *         @ORM\Index(name="appeal_technical_correction_item_original_valuer_idx", columns={"original_valuer_user_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="appeal_technical_correction_item_target_uidx", columns={"correction_id", "target_evaluation_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="MapasCulturais\Repository")
 */
class AppealTechnicalCorrectionItem extends Entity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="appeal_technical_correction_item_id_seq", allocationSize=1)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="OpportunityAppealPhase\Entities\AppealTechnicalCorrection", inversedBy="items")
     * @ORM\JoinColumn(name="correction_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $correction;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationEvaluation")
     * @ORM\JoinColumn(name="target_evaluation_id", referencedColumnName="id", nullable=false, onDelete="RESTRICT")
     */
    protected $targetEvaluation;

    /**
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumn(name="original_valuer_user_id", referencedColumnName="id", nullable=false, onDelete="RESTRICT")
     */
    protected $originalValuer;

    /** @ORM\Column(name="is_tiebreaker", type="boolean", nullable=false, options={"default": false}) */
    protected $isTiebreaker = false;

    /** @ORM\Column(name="before_evaluation_data", type="json", nullable=false, options={"jsonb": true}) */
    protected $beforeEvaluationData = [];

    /** @ORM\Column(name="after_evaluation_data", type="json", nullable=false, options={"jsonb": true}) */
    protected $afterEvaluationData = [];

    /** @ORM\Column(name="before_result", type="float", nullable=true) */
    protected $beforeResult;

    /** @ORM\Column(name="after_result", type="float", nullable=true) */
    protected $afterResult;

    /** @ORM\Column(name="changed_criteria", type="json", nullable=false, options={"jsonb": true}) */
    protected $changedCriteria = [];

    /** @ORM\Column(name="create_timestamp", type="datetime", nullable=false) */
    protected $createTimestamp;

    public function captureChange(
        array $beforeEvaluationData,
        array $afterEvaluationData,
        ?float $beforeResult,
        ?float $afterResult,
        array $changedCriteria
    ): void {
        foreach ($changedCriteria as $criterionId => $change) {
            if (!is_array($change)
                || !array_key_exists('before', $change)
                || !array_key_exists('after', $change)
                || !is_numeric($change['before'])
                || !is_numeric($change['after'])) {
                throw new DomainException("Alteração inválida para o critério {$criterionId}.");
            }
        }

        $this->beforeEvaluationData = $beforeEvaluationData;
        $this->afterEvaluationData = $afterEvaluationData;
        $this->beforeResult = $beforeResult;
        $this->afterResult = $afterResult;
        $this->changedCriteria = $changedCriteria;
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
