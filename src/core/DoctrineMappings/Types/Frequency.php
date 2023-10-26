<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Definition of a PostGIS Geometry type to be used by Doctrine.
 */
class Frequency extends Type
{
    const FREQUENCY = 'frequency'; // modify to match your type name

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'frequency';
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
        return self::FREQUENCY; // modify to match your constant name
    }

    static function register(){
        Type::addType('frequency', '\MapasCulturais\DoctrineMappings\Types\Frequency');
    }
}
