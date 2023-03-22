<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\Entity;

trait ControllerEntity {

    static function useEntity() {
        return true;
    } 

    /**
     * The class name of the entity with the same name of the controller in the same parent namespace.
     *
     * @example for the controller \MapasCulturais\Controllers\User the value will be \MapasCulturais\Entities\User
     * @example for the controller \MyPlugin\Controllers\User the value will be \MyPlugin\Entities\User
     *
     * @var string the entity class name
     */
    protected $entityClassName;


    protected $_requestedEntity = false;

    static $changeStatusMap = [
        Entity::STATUS_ENABLED => [
            Entity::STATUS_ENABLED => null,
            Entity::STATUS_DRAFT => 'unpublish',
            Entity::STATUS_TRASH => 'delete',
            Entity::STATUS_ARCHIVED => 'archive'
        ],
        Entity::STATUS_DRAFT => [
            Entity::STATUS_ENABLED => 'publish',
            Entity::STATUS_DRAFT => null,
            Entity::STATUS_TRASH => 'delete',
            Entity::STATUS_ARCHIVED => 'archive'
        ],
        Entity::STATUS_TRASH => [
            Entity::STATUS_ENABLED => 'undelete',
            Entity::STATUS_DRAFT => 'undelete',
            Entity::STATUS_TRASH => null,
            Entity::STATUS_ARCHIVED => 'archive'
        ],
        Entity::STATUS_ARCHIVED => [
            Entity::STATUS_ENABLED => 'publish',
            Entity::STATUS_DRAFT => 'unpublish',
            Entity::STATUS_TRASH => 'delete',
            Entity::STATUS_ARCHIVED => null
        ]
    ];

    /**
     * Returns the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::repo()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(){
        return App::i()->repo($this->entityClassName);
    }

    /**
     * Alias to getRepository
     *
     * @see \MapasCulturais\Controller::getRepository()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return $this->getRepository();
    }

    /**
     * Creates and returns an empty new entity object of the entity class related with this controller.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     *
     * @return \MapasCulturais\entityClassName An empty new entity object.
     */
    public function getNewEntity(){
        $class = $this->entityClassName;
        return new $class;
    }

    /**
     * Returns the etity with the requested id.
     *
     * @example for the url http://mapasculturais/agent/33  or http://mapasculturais/agent/id:33 returns the agent with the id 33
     *
     * @return \MapasCulturais\Entity|null
     */
    public function getRequestedEntity(){
        if ($this->_requestedEntity !== false) {
            return $this->_requestedEntity;
        }

        if (key_exists('id', $this->urlData)) {
            $this->_requestedEntity = $this->repository->find($this->urlData['id']);
        } elseif ($this->action === 'create' || ($this->method == 'POST' && $this->action === 'index')) {
            $this->_requestedEntity = $this->newEntity;
        } else {
            $this->_requestedEntity = null;
        }

        return $this->_requestedEntity;
    }

    /**
     * Returns the fields of the entity with the same name of the controller in the same parent namespace.
     *
     * @see \MapasCulturais\App::fields()
     *
     * @return array of fields
     */
    public function getFields(){
        return App::i()->fields($this->entityClassName);
    }

    /**
     * Alias to getFields()
     *
     * @see \MapasCulturais\Entities\EntityController::getFields()
     *
     * @return array of fields
     */
    public function fields(){
        return $this->getFields();
    }
}