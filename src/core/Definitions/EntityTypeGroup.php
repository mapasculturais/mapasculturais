<?php
namespace MapasCulturais\Definitions;

/**
 * Define um Grupo de Tipos de Entidade.
 *
 * Um Grupo de Tipos de Entidade é basicamente um ID mínimo, um ID máximo e uma descrição.
 *
 * A intenção desta classe é ser usada em buscas. Quando o usuário busca por um Grupo de Tipos de Entidade,
 * internamente a aplicação busca por entidades cujo tipo está entre o ID mínimo e máximo.
 *
 * Para que esta definição tenha efeito, você deve registrá-la na aplicação.
 *
 * @property-read string $entity_class Nome da Classe da Entidade
 * @property-read string $name Nome do Grupo
 * @property-read int $min_id ID mínimo
 * @property-read int $max_id ID máximo
 * @property-read \MapasCulturais\Definitions\EntityType[] $registered_types Tipos registrados para este grupo.
 *
 * @see \MapasCulturais\App::registerEntityTypeGroup()
 * @see \MapasCulturais\App::getRegisteredEntityTypeGroupByTypeId()
 * @package MapasCulturais\Definitions
 */
class EntityTypeGroup extends \MapasCulturais\Definition{

    /**
     * Nome da classe da entidade.
     *
     * @var string
     */
    public $entity_class;

    /**
     * Nome deste grupo.
     * 
     * @var string
     */
    public $name;

    /**
     * ID mínimo para tipos neste grupo.
     *
     * @var int
     */
    public $min_id;

    /**
     * ID máximo para tipos neste grupo.
     *
     * @var int
     */
    public $max_id;

    /**
     * Tipos registrados para este grupo.
     *
     * @var \MapasCulturais\Definitions\EntityType[]
     */
    public $registered_types = [];

    /**
     * Cria um novo Grupo de Tipos de Entidade.
     *
     * Para que este grupo de tipos de entidade tenha efeito, você precisa registrá-lo na aplicação.
     *
     * @param string $entity_class Nome da Classe da Entidade
     * @param string $name Nome do Grupo
     * @param int $minId ID mínimo
     * @param int $maxId ID máximo
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
     * Registra um tipo de entidade neste grupo.
     *
     * @param \MapasCulturais\Definitions\EntityType $type
     */
    function registerType(EntityType $type){
        if(!in_array($type, $this->registered_types))
            $this->registered_types[] = $type;
    }

    /**
     * Serializa o grupo de tipos de entidade para JSON
     * 
     * @return array Array com nome, IDs mínimo/máximo e tipos registrados
     */
    function jsonSerialize(): array {
        return [
            'name' => $this->name,
            'minId' => $this->min_id,
            'maxId' => $this->max_id,
            'types' => $this->registered_types
        ];
    }
}