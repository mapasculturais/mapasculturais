<?php
namespace MapasCulturais;

class Definition implements \JsonSerializable{
    use \MapasCulturais\Traits\MagicGetter;

    public function isDefinition(){
        return true;
    }

    public function jsonSerialize() {
        $result = [];
        foreach ($this as $prop => $val)
            if($prop[0] !== '_')
                $result[$prop] = $val;

        return $result;
    }
}