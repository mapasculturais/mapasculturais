<?php
namespace MapasCulturais;

use Doctrine\ORM\Mapping as ORM;

class EntityMetadata extends Entity {

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", nullable=false)
     */
    public $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    protected $value;


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