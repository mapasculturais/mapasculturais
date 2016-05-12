<?php
namespace MapasCulturais\Traits;

use \MapasCulturais\App;

/**
 * Defines that the controller has types.
 *
 * Use this trait in controllers of entities that have types (use the trait EntityTypes)
 *
 * @property-read \MapasCulturais\Definitions\EntityType[] $types Array with all Entity Type Definitions registered for the entity related with this controller
 * @property-read \MapasCulturais\Definitions\EntityTypeGroups[] $typeGroups Array with all Entity Type Groups registered for the entity related with this controller
 *
 */
trait ControllerTypes{
    /**
     * Returns an array with all Entity Type Definitions registered for the entity related with this controller
     *
     * @return \MapasCulturais\Definitions\EntityType[] The Entity Types
     */
    public function getTypes(){
        $types = App::i()->getRegisteredEntityTypes($this->entityClassName);
        sort($types);
        return $types;
    }

    /**
     * Returns an array with all registered Eneity Type Groups for the entity related with this controller
     *
     * @return \MapasCulturais\Definitions\EntityTypeGroup[] The
     */
    public function getTypeGroups(){
        $groups = App::i()->getRegisteredEntityTypeGroupsByEntity($this->entityClassName);
        return $groups;
    }

    /**
     * Prints a JSON with the registered entity types for the entity related with this controller.
     */
    public function API_getTypes(){
        $this->apiArrayResponse($this->getTypes());
    }

    /**
     * Prints a JSON with the registered entity type groups for the entity related with this controller.
     */
    public function API_getTypeGroups(){
        $this->apiArrayResponse($this->getTypeGroups());
    }
}