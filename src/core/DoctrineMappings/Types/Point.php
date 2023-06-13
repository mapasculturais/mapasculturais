<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;
use MapasCulturais\Types\GeoPoint;

/**
 * Definition of a PostgreSQL POINT type to be used by Doctrine.
 */
class Point extends Type
{
    const POINT = 'point'; // modify to match your type name

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'point';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if(preg_match('#^\([-\.0-9]+,[-\.0-9]+\)$#', $value)){
            $value = explode(',',substr($value, 1, -1));
            $point = new GeoPoint($value[0], $value[1]);
        }else{
            $point = new GeoPoint(0, 0);
        }
        return $point;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if(!is_object($value) || !($value instanceof GeoPoint))
            throw new Exception ('Value must be an instance of \MapasCulturais\Types\GeoPoint');

        return "({$value->longitude},{$value->latitude})";
    }

    public function getName()
    {
        return self::POINT; // modify to match your constant name
    }

    static function register(){
        Type::addType('point', '\MapasCulturais\DoctrineMappings\Types\Point');
    }
}