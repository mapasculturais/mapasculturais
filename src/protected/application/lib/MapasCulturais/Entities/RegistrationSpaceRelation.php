<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RegistrationSpaceRelation extends SpaceRelation{

    /**
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;

    public function save($flush = false) {
        $old_relations = $this->repo()->findBy(['owner' => $this->owner]);
        foreach($old_relations as $rel){
            if(!$this->equals($rel)){
                $rel->delete($flush);
            }
        }
        parent::save($flush);
    }

    static public function getOptionSelected($idOpportunity) {
        $conn = \MapasCulturais\App::i()->em->getConnection();
        $terms = $conn->fetchAll("SELECT * FROM opportunity_meta WHERE object_id = $idOpportunity AND key = 'useSpaceRelationIntituicao' ");
        return $terms;
    }
    /**
     * Passando o valor vindo do metodo getOptionSelected, pois é o valor que está armazenado
     * no DB
     * Retornando o valor e o label para fazer o select sem o essa opção
     * @param [type] $optionSelect
     * @return void
     */
    static public function getOptionLabel($optionSelect) {
        switch ($optionSelect) {
            case 'dontUse':
                $optionValue = $optionSelect;
                $optionLabel = 'Não utilizar';
                break;
            case 'required':
                $optionValue = $optionSelect;
                $optionLabel = 'Obrigatório';
                break;
            case 'optional':
                $optionValue = $optionSelect;
                $optionLabel = 'Opcional';
                break;    
            default:
                $optionValue = $optionSelect;
                $optionLabel = 'Não utilizar';
                break;
        }
        return ['optionValue' => $optionValue, 'optionLabel' => $optionLabel];
    }
}