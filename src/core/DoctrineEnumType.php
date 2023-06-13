<?php

namespace MapasCulturais;

use MyCLabs\Enum\Enum;

abstract class DoctrineEnumType extends Enum
{
    abstract public static function getTypeName(): string;

    abstract protected static function getKeysValues(): array;

    public static function toArray(): array
    {
        $class = get_called_class();

        $result = $class::getKeysValues();

        $app = App::i();

        $class = get_called_class();

        $type_name = $class::getTypeName();
        
        $app->applyHook("doctrine.emum({$type_name}).values", [&$result]);

        return $result;
    }
}
