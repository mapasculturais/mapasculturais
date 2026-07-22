<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Repository;

trait ControllerEntity {

    static function useEntity() {
        return true;
    } 

    /**
     * Nome da classe da entidade com o mesmo nome do controlador no mesmo namespace pai.
     *
     * @example para o controlador \MapasCulturais\Controllers\User o valor será \MapasCulturais\Entities\User
     * @example para o controlador \MyPlugin\Controllers\User o valor será \MyPlugin\Entities\User
     *
     * @var string Nome da classe da entidade
     */
    protected $entityClassName;
    
    /**
     * Repositório da entidade
     * @var Repository|\Doctrine\ORM\EntityRepository|null
     */
    protected Repository|\Doctrine\ORM\EntityRepository|null $entityRepository = null;

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
     * Retorna o Repositório de Entidade Doctrine para a entidade com o mesmo nome do controlador no mesmo namespace pai.
     *
     * @see \MapasCulturais\App::repo()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(){
        $this->entityRepository =  $this->entityRepository ?: App::i()->repo($this->entityClassName);
        
        return $this->entityRepository;
    }

    /**
     * Apelido para getRepository
     *
     * @see \MapasCulturais\Controller::getRepository()
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(){
        return $this->getRepository();
    }

    /**
     * Cria e retorna um novo objeto de entidade vazio da classe de entidade relacionada com este controlador.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     *
     * @return \MapasCulturais\entityClassName Um novo objeto de entidade vazio.
     */
    public function getNewEntity(){
        $class = $this->entityClassName;
        return new $class;
    }

    /**
     * Retorna a entidade com o ID solicitado.
     *
     * @example for the url http://mapasculturais/agent/33  or http://mapasculturais/agent/id:33 returns the agent with the id 33
     *
     * @return \MapasCulturais\Entity|null
     */
    public function getRequestedEntity(): ?Entity
    {
        if (key_exists('id', $this->urlData)) {
            $entity = $this->repository->find($this->urlData['id']);
            $app = App::i();
            
        } elseif ($this->action === 'create' || ($this->method == 'POST' && $this->action === 'index')) {
            $entity = $this->newEntity;
        } else {
            $entity = null;
        }

        return $entity;
    }

    /**
     * Retorna os campos da entidade com o mesmo nome do controlador no mesmo namespace pai.
     *
     * @see \MapasCulturais\App::fields()
     *
     * @return array of fields
     */
    public function getFields(){
        return App::i()->fields($this->entityClassName);
    }

    /**
     * Apelido para getFields()
     *
     * @see \MapasCulturais\Entities\EntityController::getFields()
     *
     * @return array of fields
     */
    public function fields(){
        return $this->getFields();
    }
}