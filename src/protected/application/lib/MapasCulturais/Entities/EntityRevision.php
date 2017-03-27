<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Entity Revision
 *
 * @ORM\Table(name="entity_revision",indexes={@ORM\Index(name="entity_revision_idx", columns={"object_id", "object_type", "timestamp"})}))
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\EntityRevision")
 */
class EntityRevision extends \MapasCulturais\Entity{
    const ACTION_CREATED        = 'created';
    const ACTION_MODIFIED       = 'modified';
    const ACTION_PUBLISHED      = 'publish';
    const ACTION_UNPUBLISHED    = 'unpublished';
    const ACTION_ARCHIVED       = 'archive';
    const ACTION_UNARCHIVED     = 'unarchive';
    const ACTION_TRASHED        = 'delete';
    const ACTION_UNTRASHED      = 'undelete';
    const ACTION_DELETED        = 'delete';



    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="entity_revision_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="string", length=255, nullable=false)
     */
    protected $objectType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=false)
     */
    protected $action = "";

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    protected $message = "";

    /**
     * @var \MapasCulturais\Entities\EntityRevisionData[]
     *
     * @ORM\ManyToMany(targetEntity="MapasCulturais\Entities\EntityRevisionData")
     * @ORM\JoinTable(name="entity_revision_revision_data",
     *      joinColumns={@ORM\JoinColumn(name="revision_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="revision_data_id", referencedColumnName="id")}
     * )
     */
    protected $data;


    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    protected $modified = false;

    public function __construct(array $dataRevision, $entity, $action, $message = "") {
        parent::__construct();
        $app = App::i();

        $user = $app->user;

        if($user->is('guest')){
            $user = $entity->getOwnerUser();
        }
        
        $user = $app->repo('User')->find($user->id);
        
        $this->user = $user;

        $this->objectId = $entity->id;
        $this->objectType = $entity->getClassName();
        $this->action = $action;

        $this->data = new \Doctrine\Common\Collections\ArrayCollection();

        if($action == self::ACTION_CREATED) {
            $this->createTimestamp = $entity->createTimestamp;

            foreach($dataRevision as $key => $data) {
                $revisionData = new EntityRevisionData;
                $revisionData->key = $key;
                $revisionData->value = $data;
                $revisionData->save();
                $this->data[] = $revisionData;
            }
            $this->message = \MapasCulturais\i::__("Registro criado.");
        } elseif($action == self::ACTION_MODIFIED) {
            $lastRevision = $entity->getLastRevision();
            $lastRevisionData = $lastRevision->getRevisionData();
            if(isset($lastRevision->updateTimestamp) && $lastRevision->updateTimestamp) {
                $createTimestamp = $lastRevision->updateTimestamp;
            } elseif(isset($this->user->lastLoginTimestamp) && $this->user->lastLoginTimestamp) {
                $createTimestamp = $this->user->lastLoginTimestamp;
            }   
            foreach($dataRevision as $key => $data) {
                $item = isset($lastRevisionData[$key])? $lastRevisionData[$key]: null;
                if(!is_null($item)) {
                    if(is_object($item->getValue())) {
                        $itemValue = (array) $item->getValue();
                        if(is_array($itemValue) && array_key_exists("_empty_",$itemValue)) {
                            $itemValue = array("" => $itemValue['_empty_']);
                        }
                    } else {
                        $itemValue = $item->getValue();
                    }
                } else {
                    $itemValue = null;
                }

                if(json_encode($data) != json_encode($itemValue)) {
                    $revisionData = new EntityRevisionData;
                    $revisionData->key = $key;
                    $revisionData->setValue($data);
                    $revisionData->save();
                    $this->data[] = $revisionData;
                    $this->modified = true;
                } elseif(!is_null($item)) {
                    $this->data[] = $item;
                }
            }

            $this->message = \MapasCulturais\i::__("Registro atualizado.");
        } else {
            $lastRevision = $entity->getLastRevision();
            $lastRevisionData = $lastRevision->getRevisionData();
            foreach($lastRevisionData as $key => $data) {
                $item = isset($lastRevisionData[$key])? $lastRevisionData[$key]: null;
                $this->data[] = $item;
            }
        }
        if(!empty(trim($message))) {
            $this->message = $message;
        }
    }

    public function canUser($action, $userOrAgent = null){
        return true;
        $app = App::i();
        $entity = $app->repo($this->objectType)->find($this->objectId);
        return $entity->canUser($action, $userOrAgent);
    }

    /*
     *
     */
    public function getRevisionData() {
        $result = [];
        foreach($this->data as $revisionData) {
            $result[$revisionData->key] = $revisionData;
        }
        return $result;
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
