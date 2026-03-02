<?php
namespace MapasCulturais;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classe base para metadados de entidades
 * 
 * @property string $value O valor do metadado
 * 
 * @package MapasCulturais
 */
class EntityMetadata extends Entity {

    /**
     * Chave do metadado
     * @var string
     *
     * @ORM\Column(name="key", type="string", nullable=false)
     */
    public $key;

    /**
     * Valor do metadado
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    protected $value;


    /**
     * Salva o metadado, garantindo que não haja duplicidade de chave para o mesmo objeto
     * 
     * @param bool $flush
     * @return void
     */
    function save($flush = false)
    {
        if (!$this->isNew()) {
            parent::save($flush);
            return;
        }
        $app = App::i();
        $class_metadata = $app->em->getClassMetadata($this->className);
        $table = $class_metadata->getTableName();

        $has_metadata = $app->conn->fetchScalar("SELECT id FROM $table WHERE key = :key AND object_id = :objectId", [
            'key' => $this->key,
            'objectId' => $this->owner->id
        ]);

        if ($has_metadata) {
            return;
        }
        
        parent::save($flush);
    }
}