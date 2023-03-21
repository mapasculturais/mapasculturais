<?php
namespace MapasCulturais\Definitions;

/**
 * This class defines an Entity Type.
 *
 * To the new Entity Type take effects, you need to register it in to the application.
 *
 * @see \MapasCulturais\App::registerEntityType()
 * @see \MapasCulturais\App::entityTypeExists()
 * @see \MapasCulturais\App::getRegisteredEntityTypes()
 * 
 * @property string $entity_class
 * @property int $id
 * @property string $name
 * 
 */
class EntityType extends \MapasCulturais\Definition{

    /**
     * The entity class of this type
     *
     * @var string
     */
    protected $entity_class;

    /**
     * The id of this entity type.
     *
     * Because the types is not in database, the types id must be defined hardcoded in the project configuration
     * and never changed in production, otherwise the references will be lost.
     *
     * @var int
     */
    protected $id;

    /**
     * Name of this entity type.
     *
     * @var string
     */
    protected $name;

    /**
     * Creates a new entity type for the given entity class.
     *
     * @param string $entity_class Entity class name
     * @param string $name Name of this entity type
     */
    function __construct($entity_class, $id, $name) {
        $this->entity_class = $entity_class;
        $this->id = $id;
        $this->name = $name;
    }

    function __toString() {
        return (string)$this->id;
    }

    function jsonSerialize() {
        return ['id' => $this->id, 'name' => $this->name];
    }
}