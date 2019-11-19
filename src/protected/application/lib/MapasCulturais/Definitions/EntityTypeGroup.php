<?php
namespace MapasCulturais\Definitions;

/**
 * This class defines an Entity Type Group.
 *
 * An Entity Type Group is, basically, a minimun id, a maximun id and a description.
 *
 * The intent of this class is to be used in searches. When the user searches for an Entity Type Group,
 * internally the application searches for entities that the type is between the minimun and maximun id.
 *
 * To this definition take effects, you must register it in to application.
 *
 * @property-read string $entity_class Entity Class Name
 * @property-read string $name Group Name
 * @property-read int $min_id Minimum id
 * @property-read int $max_id Maximum id
 * @property-read \MapasCulturais\Definitions\EntityType[]  $registered_types Registered types for this group.
 *
 * @see \MapasCulturais\App::registerEntityTypeGroup()
 * @see \MapasCulturais\App::getRegisteredEntityTypeGroupByTypeId()
 */
class EntityTypeGroup extends \MapasCulturais\Definition{

    /**
     * The entity class name.
     *
     * @var string
     */
    protected $entity_class;

    /**
     * The name of this group.
     * @var type
     */
    protected $name;

    /**
     * The minimum id for types in this group.
     *
     * @var int
     */
    protected $min_id;

    /**
     * The maximum id for types in this group.
     *
     * @var int
     */
    protected $max_id;

    /**
     * Registered types for this group.
     *
     * @var \MapasCulturais\Definitions\EntityType[]
     */
    protected $registered_types = [];

    /**
     * Create a new Entity Type Group.
     *
     * To this entity type group take effects you need to register it in to the application
     *
     * @param string $entity_class Entity Class Name
     * @param string $name Group Name
     * @param int $minId Minimum id
     * @param int $maxId Maximum id
     *
     * @see \MapasCulturais\App::registerEntityTypeGroup()
     * @see \MapasCulturais\App::getRegisteredEntityTypeGroupByTypeId()
     */
    function __construct($entity_class, $name, $minId, $maxId) {
        $this->entity_class = $entity_class;
        $this->name = $name;
        $this->min_id = $minId;
        $this->max_id = $maxId;
    }

    /**
     * Register a entity type to this group.
     *
     * @param \MapasCulturais\Definitions\EntityType $type
     */
    function registerType(EntityType $type){
        if(!in_array($type, $this->registered_types))
            $this->registered_types[] = $type;
    }

    function jsonSerialize() {
        return [
            'name' => $this->name,
            'minId' => $this->min_id,
            'maxId' => $this->max_id,
            'types' => $this->registered_types
        ];
    }
}