<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

class ObjectType extends Type
{
    const OBJECT_TYPE = 'object_type';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::OBJECT_TYPE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function getName()
    {
        return self::OBJECT_TYPE;
    }

    static function register(){
        Type::addType('object_type', '\MapasCulturais\DoctrineMappings\Types\ObjectType');
    }
}
