<?php

namespace MapasCulturais;

use MyCLabs\Enum\Enum;

/**
 * Classe base para tipos enumerados (Enum) integrados com o Doctrine
 * 
 * Esta classe abstrata estende a biblioteca MyCLabs\Enum para fornecer
 * funcionalidades adicionais de integração com o Doctrine ORM, permitindo
 * que enums sejam mapeados para tipos personalizados no banco de dados.
 * 
 * @package MapasCulturais
 */
abstract class DoctrineEnumType extends Enum
{
    /**
     * Retorna o nome do tipo usado no banco de dados
     * 
     * Este método deve ser implementado pelas classes filhas para definir
     * o nome do tipo que será registrado no Doctrine.
     * 
     * @return string Nome do tipo no banco de dados
     */
    abstract public static function getTypeName(): string;

    /**
     * Retorna um array com as chaves e valores do enum
     * 
     * Este método deve ser implementado pelas classes filhas para definir
     * os pares chave-valor que compõem o enum.
     * 
     * @return array Array associativo com chaves e valores do enum
     */
    abstract protected static function getKeysValues(): array;

    /**
     * Retorna os valores do enum como array, permitindo modificação via hooks
     * 
     * Este método sobrescreve o método toArray() da classe base para permitir
     * que os valores do enum sejam modificados através de hooks da aplicação.
     * 
     * @return array Valores do enum, possivelmente modificados por hooks
     */
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
