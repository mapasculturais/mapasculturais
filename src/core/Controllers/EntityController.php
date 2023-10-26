<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;

/**
 * This is the base class to Entity Controllers
 *
 * @property-read \MapasCulturais\Entity $newEntity An empty new entity object of the class related to this controller
 * @property-read \Doctrine\ORM\EntityRepository $repository the Doctrine Entity Repository to the entity with the same name of the controller in the same parent namespace.
 * @property-read array $fields the fields of the entity with the same name of the controller in the same parent namespace.
 * @property-read \MapasCulturais\Entity $requestedEntity The requested Entity
 */
abstract class EntityController extends \MapasCulturais\Controller{
    use Traits\ControllerEntity,
        Traits\ControllerEntityActions,
        Traits\ControllerEntityViews;

    /**
     * The controllers constructor.
     *
     * This method sets the controller entity class name with an class with the same name of the controller in the parent namespace.
     *
     * @see \MapasCulturais\Controller::$entityClassName
     */
    protected function __construct() {
        $this->entityClassName = preg_replace("#Controllers\\\([^\\\]+)$#", 'Entities\\\$1', get_class($this));
    }
}