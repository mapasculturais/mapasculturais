<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Definition of a PostGIS Geography type to be used by Doctrine.
 */
class Geography extends Type
{
    const GEOGRAPHY = 'geography'; // modify to match your type name

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'geography';
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
        return self::GEOGRAPHY; // modify to match your constant name
    }

    static function register(){
        Type::addType('geography', '\MapasCulturais\DoctrineMappings\Types\Geography');
    }
}
