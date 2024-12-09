<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Role
 *
 * @ORM\Table(name="permission_cache_pending")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class PermissionCachePending extends \MapasCulturais\Entity {

    const STATUS_WAITING = 0;
    const STATUS_PROCESSING = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="permission_cache_pending_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    public $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=255, nullable=false)
     */
    public $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    public $status = self::STATUS_WAITING;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", cascade={"persist"}, )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usr_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $user;

    protected function canUserCreate($user) {
        return true;
    }

    public function save($flush = false)
    {
        parent::save($flush);

        $app = App::i();

        if($app->config['app.log.pcache']) {
            $app->log->debug("CACHE PENDING: $this->objectType :: $this->objectId");
        }
    }
}
