<?php

namespace OpportunityAppealPhase\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;

/**
 * RegistrationAppealReview
 *
 * Designação de slot de revisão de recurso, vinculada a uma avaliação original.
 *
 * @property int $id
 * @property RegistrationEvaluation $originalEvaluation
 * @property Registration $registration
 * @property Opportunity $appealPhase
 * @property User $slotOwnerUser
 * @property User $correctorUser
 * @property int $status
 * @property string $correctionType
 * @property object $releasedScope
 * @property DateTime $startsAt
 * @property DateTime $endsAt
 * @property object $originalValue
 * @property object $correctedValue
 * @property float $originalScore
 * @property float $correctedScore
 * @property DateTime $createTimestamp
 * @property DateTime $sentTimestamp
 * @property DateTime $updateTimestamp
 *
 * @ORM\Table(name="registration_appeal_review")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class RegistrationAppealReview extends Entity
{
    const STATUS_DESIGNATED = 0;
    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;
    const STATUS_REOPENED = 3;

    const CORRECTION_TYPE_OFFICIAL = 'official';
    const CORRECTION_TYPE_RECORD = 'record';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_appeal_review_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var RegistrationEvaluation
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationEvaluation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="original_evaluation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $originalEvaluation;

    /**
     * @var Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $registration;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="appeal_phase_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $appealPhase;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="slot_owner_user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $slotOwnerUser;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="corrector_user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $correctorUser;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DESIGNATED;

    /**
     * @var string
     *
     * @ORM\Column(name="correction_type", type="string", length=20, nullable=false)
     */
    protected $correctionType;

    /**
     * @var object
     *
     * @ORM\Column(name="released_scope", type="json", nullable=true)
     */
    protected $releasedScope;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="starts_at", type="datetime", nullable=true)
     */
    protected $startsAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="ends_at", type="datetime", nullable=true)
     */
    protected $endsAt;

    /**
     * @var object
     *
     * @ORM\Column(name="original_value", type="json", nullable=true)
     */
    protected $originalValue;

    /**
     * @var object
     *
     * @ORM\Column(name="corrected_value", type="json", nullable=true)
     */
    protected $correctedValue;

    /**
     * @var float
     *
     * @ORM\Column(name="original_score", type="float", nullable=true)
     */
    protected $originalScore;

    /**
     * @var float
     *
     * @ORM\Column(name="corrected_score", type="float", nullable=true)
     */
    protected $correctedScore;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="sent_timestamp", type="datetime", nullable=true)
     */
    protected $sentTimestamp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    /**
     * @return string[]
     */
    public static function getCorrectionTypes(): array
    {
        return [
            self::CORRECTION_TYPE_OFFICIAL,
            self::CORRECTION_TYPE_RECORD,
        ];
    }

    /**
     * @return string[]
     */
    public static function getActiveStatuses(): array
    {
        return [
            self::STATUS_DESIGNATED,
            self::STATUS_DRAFT,
            self::STATUS_REOPENED,
        ];
    }

    public static function isActiveStatus(int $status): bool
    {
        return in_array($status, self::getActiveStatuses(), true);
    }

    public function isActive(): bool
    {
        return self::isActiveStatus((int) $this->status);
    }

    /**
     * Retorna os usuários elegíveis para corrigir o slot vinculado a esta designação.
     *
     * Elegibilidade aberta a todos os métodos de avaliação (F6 / #49,
     * Variante 3 — override registrado na #7 em 2026-09-09): a escolha de
     * critérios existe para `technical` e `documentary`; nos demais métodos
     * a correção liberada é integral (releasedScope nulo).
     *
     * @return User[]
     */
    public function eligibleCorrectors(?RegistrationEvaluation $slot = null): array
    {
        $slot ??= $this->originalEvaluation;

        if (!$slot || !$this->appealPhase || !$this->appealPhase->parent) {
            return [];
        }

        $main_phase = $slot->registration->opportunity;
        $main_emc = $main_phase->evaluationMethodConfiguration;

        if (!$main_emc) {
            return [];
        }

        if ($this->appealPhase->status !== Opportunity::STATUS_APPEAL_PHASE || !$this->appealPhase->evaluationMethodConfiguration) {
            return [];
        }

        $users = [];
        $users_by_id = [];

        $push_user = static function (?User $user) use (&$users, &$users_by_id): void {
            if (!$user || isset($users_by_id[$user->id])) {
                return;
            }

            $users[] = $user;
            $users_by_id[$user->id] = true;
        };

        $push_user($slot->user);

        foreach ($this->appealPhase->evaluationMethodConfiguration->getCommittee(false) as $agent) {
            $push_user($agent->user);
        }

        return $users;
    }

    public static function findActiveForEvaluationAndUser(RegistrationEvaluation $slot, User $user): ?self
    {
        $app = App::i();
        /** @var self[] $reviews */
        $reviews = $app->repo(self::class)->findBy([
            'originalEvaluation' => $slot,
            'correctorUser' => $user,
            'status' => self::getActiveStatuses(),
        ]);

        foreach ($reviews as $review) {
            if (
                $review->registration->equals($slot->registration) &&
                $review->appealPhase &&
                $review->appealPhase->status === Opportunity::STATUS_APPEAL_PHASE &&
                $review->slotOwnerUser &&
                $review->slotOwnerUser->equals($slot->user)
            ) {
                return $review;
            }
        }

        return null;
    }

    public function setReleasedScope($value): void
    {
        $this->releasedScope = $value;
    }

    public function setOriginalValue($value): void
    {
        $this->originalValue = $value;
    }

    public function setCorrectedValue($value): void
    {
        $this->correctedValue = $value;
    }

    public function getReleasedScope()
    {
        return $this->releasedScope;
    }

    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    public function getCorrectedValue()
    {
        return $this->correctedValue;
    }

    //============================================================= //
    // The following lines are used by MapasCulturais hook system.
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
