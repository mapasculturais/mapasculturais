<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

class PermissionAction extends Type
{
    const PERMISSION_ACTION = 'permission_action';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::PERMISSION_ACTION;
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
        return self::PERMISSION_ACTION;
    }

    static function register(){
        Type::addType('permission_action', '\MapasCulturais\DoctrineMappings\Types\PermissionAction');
    }
}
