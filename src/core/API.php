<?php
namespace MapasCulturais;

class API{
    static function NULL() {
        return 'NULL()';
    }
    
    static function NOT_NULL() {
        return '!NULL()';
    }

    static function EQ($value) {
        return "EQ($value)";
    }

    static function NOT_EQ($value) {
        return '!' . self::EQ($value);
    }

    static function DIFF( $value) {
        return self::NOT_EQ($value);
    }

    static function IN(array $value) {
        $value = array_unique($value);
        sort($value);
        $value = implode(',', $value);
        return "IN($value)";
    }

    static function NOT_IN(array $value) {
        return '!' . self::IN($value);
    }

    static function GT($value) {
        return "GT($value)";
    }

    static function GTE($value) {
        return "GTE($value)";
    }

    static function LT($value) {
        return "LT($value)";
    }

    static function LTE($value) {
        return "LTE($value)";
    }


    static function NOT_GT($value) {
        return '!' . self::GT($value);
    }

    static function NOT_GTE($value) {
        return '!' . self::GTE($value);
    }

    static function NOT_LT($value) {
        return '!' . self::LT($value);
    }

    static function NOT_LTE($value) {
        return '!' . self::LTE($value);
    }

    static function BET($value_1, $value_2) {
        return "BET($value_1,$value_2)";
    }

    static function NOT_BET($value_1, $value_2) {
        return '!' . self::BET($value_1, $value_2);
    }

    static function OR(...$values) {
        $values = implode(',', $values);
        return "OR($values)";
    }

    static function AND(...$values) {
        $values = implode(',', $values);
        return "AND($values)";
    }
}