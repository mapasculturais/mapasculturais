<?php

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\Entity;

/**
 * TestEntity
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class TestEntity extends Entity{
    
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
    
    static function getValidations() {
        return [
            'notRequiredBrPhone' => [
                'v::brPhone()' => "Não é um telefone válido"
            ],

            'requiredBrPhone' => [
                'required' => "Requerido",
                'v::brPhone()' => "Não é um telefone válido"
            ]
        ];
    }
}