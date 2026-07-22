<?php
namespace MapasCulturais\Definitions;

/**
 * Define um Tipo de Entidade.
 *
 * Para que um novo Tipo de Entidade tenha efeito, é necessário registrá-lo na aplicação.
 *
 * @see \MapasCulturais\App::registerEntityType()
 * @see \MapasCulturais\App::entityTypeExists()
 * @see \MapasCulturais\App::getRegisteredEntityTypes()
 * 
 * @property string $entity_class Classe da entidade
 * @property int $id ID do tipo de entidade
 * @property string $name Nome do tipo de entidade
 * 
 * @package MapasCulturais\Definitions
 */
class EntityType extends \MapasCulturais\Definition{

    /**
     * Classe da entidade deste tipo
     *
     * @var string
     */
    public $entity_class;

    /**
     * ID deste tipo de entidade.
     *
     * Como os tipos não estão no banco de dados, o ID do tipo deve ser definido
     * de forma fixa na configuração do projeto e nunca alterado em produção,
     * caso contrário as referências serão perdidas.
     *
     * @var int
     */
    public $id;

    /**
     * Nome deste tipo de entidade.
     *
     * @var string
     */
    public $name;

    /**
     * Cria um novo tipo de entidade para a classe de entidade fornecida.
     *
     * @param string $entity_class Nome da classe da entidade
     * @param int $id ID do tipo de entidade
     * @param string $name Nome deste tipo de entidade
     */
    function __construct($entity_class, $id, $name) {
        $this->entity_class = $entity_class;
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Retorna a representação em string do tipo de entidade (seu ID)
     * 
     * @return string
     */
    function __toString() {
        return (string)$this->id;
    }

    /**
     * Serializa o tipo de entidade para JSON
     * 
     * @return array Array com id e name
     */
    function jsonSerialize(): array {
        return ['id' => $this->id, 'name' => $this->name];
    }
}