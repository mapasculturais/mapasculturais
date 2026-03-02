<?php
namespace MapasCulturais;

/**
 * Classe auxiliar para construção de consultas na API
 * 
 * Esta classe fornece métodos estáticos para criar expressões de filtro
 * utilizadas nas consultas da API do Mapas Culturais. As expressões geradas
 * são usadas pelo sistema de query builder para construir consultas complexas
 * de forma programática.
 * 
 * @package MapasCulturais
 */
class API{
    /**
     * Retorna a expressão NULL()
     * 
     * @return string
     */
    static function NULL() {
        return 'NULL()';
    }
    
    /**
     * Retorna a expressão !NULL()
     * 
     * @return string
     */
    static function NOT_NULL() {
        return '!NULL()';
    }

    /**
     * Retorna a expressão EQ($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function EQ($value) {
        return "EQ($value)";
    }

    /**
     * Retorna a expressão !EQ($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_EQ($value) {
        return '!' . self::EQ($value);
    }

    /**
     * Retorna a expressão LIKE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function LIKE($value) {
        return "LIKE($value)";
    }

    /**
     * Retorna a expressão !LIKE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_LIKE($value) {
        return '!' . self::LIKE($value);
    }

    /**
     * Retorna a expressão ILIKE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function ILIKE($value) {
        return "ILIKE($value)";
    }

    /**
     * Retorna a expressão !ILIKE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_ILIKE($value) {
        return '!' . self::LIKE($value);
    }

    /**
     * Retorna a expressão !EQ($value) (Sinônimo de NOT_EQ)
     * 
     * @param mixed $value
     * @return string
     */
    static function DIFF( $value) {
        return self::NOT_EQ($value);
    }

    /**
     * Retorna a expressão IN($value)
     * 
     * @param array $value
     * @return string
     */
    static function IN(array $value) {
        $value = array_unique($value);
        sort($value);
        $value = implode(',', $value);
        return "IN($value)";
    }

    /**
     * Retorna a expressão !IN($value)
     * 
     * @param array $value
     * @return string
     */
    static function NOT_IN(array $value) {
        return '!' . self::IN($value);
    }

    /**
     * Retorna a expressão GT($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function GT($value) {
        return "GT($value)";
    }

    /**
     * Retorna a expressão GTE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function GTE($value) {
        return "GTE($value)";
    }

    /**
     * Retorna a expressão LT($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function LT($value) {
        return "LT($value)";
    }

    /**
     * Retorna a expressão LTE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function LTE($value) {
        return "LTE($value)";
    }


    /**
     * Retorna a expressão !GT($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_GT($value) {
        return '!' . self::GT($value);
    }

    /**
     * Retorna a expressão !GTE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_GTE($value) {
        return '!' . self::GTE($value);
    }

    /**
     * Retorna a expressão !LT($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_LT($value) {
        return '!' . self::LT($value);
    }

    /**
     * Retorna a expressão !LTE($value)
     * 
     * @param mixed $value
     * @return string
     */
    static function NOT_LTE($value) {
        return '!' . self::LTE($value);
    }

    /**
     * Retorna a expressão BET($value_1,$value_2)
     * 
     * @param mixed $value_1
     * @param mixed $value_2
     * @return string
     */
    static function BET($value_1, $value_2) {
        return "BET($value_1,$value_2)";
    }

    /**
     * Retorna a expressão !BET($value_1,$value_2)
     * 
     * @param mixed $value_1
     * @param mixed $value_2
     * @return string
     */
    static function NOT_BET($value_1, $value_2) {
        return '!' . self::BET($value_1, $value_2);
    }

    /**
     * Retorna a expressão OR($values)
     * 
     * @param string[] $values
     * @return string
     */
    static function OR(...$values) {
        $values = implode(',', $values);
        return "OR($values)";
    }

    /**
     * Retorna a expressão AND($values)
     * 
     * @param string[] $values
     * @return string
     */
    static function AND(...$values) {
        $values = implode(',', $values);
        return "AND($values)";
    }
}
