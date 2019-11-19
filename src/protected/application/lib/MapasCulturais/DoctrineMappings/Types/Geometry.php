<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Definition of a PostGIS Geometry type to be used by Doctrine.
 */
class Geometry extends Type
{
    const GEOMETRY = 'geometry'; // modify to match your type name

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'geometry';
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
        return self::GEOMETRY; // modify to match your constant name
    }

    static function register(){
        Type::addType('geometry', '\MapasCulturais\DoctrineMappings\Types\Geometry');
    }
}
