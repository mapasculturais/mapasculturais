<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Tipo Doctrine para armazenamento de ações de permissão
 * 
 * Esta classe implementa um tipo personalizado do Doctrine
 * para armazenar ações de permissão no banco de dados.
 * 
 * @package MapasCulturais\DoctrineMappings\Types
 */
class PermissionAction extends Type
{
    /**
     * @const string PERMISSION_ACTION Nome do tipo no banco de dados
     */
    const PERMISSION_ACTION = 'permission_action';

    /**
     * Obtém a declaração SQL do tipo
     * 
     * @param array $fieldDeclaration Declaração do campo
     * @param AbstractPlatform $platform Plataforma de banco de dados
     * @return string Declaração SQL
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::PERMISSION_ACTION;
    }

    /**
     * Converte valor do banco para PHP
     * 
     * @param mixed $value Valor do banco
     * @param AbstractPlatform $platform Plataforma de banco de dados
     * @return mixed Valor PHP
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * Converte valor do PHP para banco
     * 
     * @param mixed $value Valor PHP
     * @param AbstractPlatform $platform Plataforma de banco de dados
     * @return mixed Valor do banco
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * Obtém o nome do tipo
     * 
     * @return string Nome do tipo
     */
    public function getName()
    {
        return self::PERMISSION_ACTION;
    }

    /**
     * Registra o tipo no Doctrine
     * 
     * @return void
     */
    static function register(){
        Type::addType('permission_action', '\MapasCulturais\DoctrineMappings\Types\PermissionAction');
    }
}
