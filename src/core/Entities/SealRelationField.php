<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * SealRelationField
 *
 * Representa o estado por campo de um selo aplicado a uma entidade,
 * incluindo data de expiração, flag de invalidador e flags de notificação.
 *
 * @ORM\Table(name="seal_relation_field", indexes={
 *      @ORM\Index(name="idx_srf_relation", columns={"seal_relation_id"}),
 *      @ORM\Index(name="idx_srf_expiry", columns={"expiry_date"}),
 *      @ORM\Index(name="idx_srf_to_expire", columns={"seal_relation_id", "expiry_date"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class SealRelationField extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seal_relation_field_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * @var \MapasCulturais\Entities\SealRelation
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\SealRelation", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="seal_relation_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $sealRelation;

    /**
     * @var string
     *
     * @ORM\Column(name="field_name", type="string", length=255, nullable=false)
     */
    protected $fieldName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="date", nullable=true)
     */
    protected $expiryDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_invalidator", type="boolean", nullable=false, options={"default" : "false"})
     */
    protected $isInvalidator = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="notified_expire", type="boolean", nullable=false, options={"default" : "false"})
     */
    protected $notifiedExpire = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="notified_to_expire", type="boolean", nullable=false, options={"default" : "false"})
     */
    protected $notifiedToExpire = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * Retorna o status atual do campo em relação à data atual (UTC).
     *
     * @return string Um dos valores: 'valid', 'about_to_expire', 'expired', 'no_expiration'
     */
    public function getFieldStatus(): string
    {
        if ($this->expiryDate === null) {
            return 'no_expiration';
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $now = $now->setTime(0, 0, 0);
        
        $expiry = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->expiryDate->format('Y-m-d'), new \DateTimeZone('UTC'));

        // Verifica se já expirou
        if ($expiry < $now) {
            return 'expired';
        }

        // Verifica se está a 7 dias de expirar
        $warning_date = $expiry->modify('-7 days');
        if ($warning_date <= $now) {
            return 'about_to_expire';
        }

        return 'valid';
    }

    /**
     * Verifica se o campo está desbloqueado para edição.
     * Um campo está desbloqueado se estiver expirado ou prestes a expirar.
     *
     * @return bool
     */
    public function isUnlocked(): bool
    {
        $status = $this->getFieldStatus();
        return in_array($status, ['about_to_expire', 'expired'], true);
    }

    /**
     * Renova o campo, recalculando a data de expiração com base na configuração do selo.
     * Resetar as flags de notificação.
     *
     * @return void
     */
    public function renew(): void
    {
        $config = (array) $this->sealRelation->seal->lockedFieldsConfig;
        $field_config = (array) ($config[$this->fieldName] ?? []);

        $this->notifiedExpire = false;
        $this->notifiedToExpire = false;

        if (!empty($field_config['hasExpiry']) && !empty($field_config['periodValue']) && !empty($field_config['periodUnit'])) {
            $period_value = (int) $field_config['periodValue'];
            $period_unit = $field_config['periodUnit'];

            $interval_spec = 'P' . $period_value;
            switch ($period_unit) {
                case 'day':
                    $interval_spec .= 'D';
                    break;
                case 'month':
                    $interval_spec .= 'M';
                    break;
                case 'year':
                    $interval_spec .= 'Y';
                    break;
                default:
                    $interval_spec .= 'M';
                    break;
            }

            $this->expiryDate = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            $this->expiryDate = $this->expiryDate->add(new \DateInterval($interval_spec));
        }
    }

    /**
     * Marca o campo como notificado sobre a expiração.
     *
     * @param bool $value
     * @return void
     */
    public function setNotifiedExpire(bool $value): void
    {
        $this->notifiedExpire = $value;
    }

    /**
     * Marca o campo como notificado sobre a proximidade da expiração.
     *
     * @param bool $value
     * @return void
     */
    public function setNotifiedToExpire(bool $value): void
    {
        $this->notifiedToExpire = $value;
    }

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
