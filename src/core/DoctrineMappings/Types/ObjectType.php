<?php
namespace MapasCulturais\DoctrineMappings\Types;

use \Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Tipo Doctrine para armazenamento de tipos de objeto
 * 
 * Esta classe implementa um tipo personalizado do Doctrine
 * para armazenar nomes de classes de objetos no banco de dados.
 * 
 * @package MapasCulturais\DoctrineMappings\Types
 */
class ObjectType extends Type
{
    /**
     * @const string OBJECT_TYPE Nome do tipo no banco de dados
     */
    const OBJECT_TYPE = 'object_type';

    /**
     * Obtém a declaração SQL do tipo
     * 
     * @param array $fieldDeclaration Declaração do campo
     * @param AbstractPlatform $platform Plataforma de banco de dados
     * @return string Declaração SQL
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::OBJECT_TYPE;
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
        return self::OBJECT_TYPE;
    }

    /**
     * Registra o tipo no Doctrine
     * 
     * @return void
     */
    static function register(){
        Type::addType('object_type', '\MapasCulturais\DoctrineMappings\Types\ObjectType');
    }
}
