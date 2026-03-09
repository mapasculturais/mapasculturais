<?php
namespace MapasCulturais;

/**
 * Classe base para definições de entidades e outros objetos do sistema
 * 
 * @package MapasCulturais
 */
class Definition implements \JsonSerializable{
    use \MapasCulturais\Traits\MagicGetter,
        \MapasCulturais\Traits\MagicSetter,
        \MapasCulturais\Traits\MagicCallers;

    /**
     * Indica se o objeto é uma definição
     * 
     * @return bool
     */
    public function isDefinition(){
        return true;
    }

    /**
     * Serializa o objeto para JSON, ignorando propriedades que começam com '_'
     * 
     * @return array
     */
    public function jsonSerialize(): array {
        $result = [];
        foreach ($this as $prop => $val)
            if($prop[0] !== '_')
                $result[$prop] = $val;

        return $result;
    }
}