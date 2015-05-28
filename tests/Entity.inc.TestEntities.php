<?php

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\Entity;

/**
 * TestEntity
 *
 * @property-read string $notRequiredBrPhone
 * @property-read string $requiredBrPhone
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class TestEntity extends Entity{
    protected static $validations = array(
        'notRequiredBrPhone' => array(
            'v::brPhone()' => "Não é um telefone válido"
        ),

        'requiredBrPhone' => array(
            'required' => "Requerido",
            'v::brPhone()' => "Não é um telefone válido"
        )
    );
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    protected $id;

    protected $notRequiredBrPhone;

    protected $requiredBrPhone;
}