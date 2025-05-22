<?php

namespace Spreadsheets;

class FieldParser
{
    /**
     * Converte uma string de campos com estrutura aninhada para um array associativo com caminhos formatados.
     *
     * @param string $input Ex: "number,owner.{geoMesoregiao.{email}}"
     * @return array<string, string> Ex: ['ownerGeoMesoregiaoEmail' => "['owner']['geoMesoregiao']['email']"]
     */
    public static function parse(string $input): array
    {
        $tokens = self::tokenizeFields($input);
        return self::flattenFields($tokens);
    }

    /**
     * Converte a string original em uma estrutura de tokens aninhados.
     *
     * @param string $input
     * @return array<int|string|array> Estrutura intermediária dos campos
     */
    private static function tokenizeFields(string $input): array
    {
        $stack = [];
        $current = [];
        $buffer = '';
        $context = [];

        $length = strlen($input);
        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];

            if ($char === '{') {
                array_push($stack, $current);
                $rawPrefix = trim(rtrim($buffer, '.'));
                array_push($context, $rawPrefix);
                $current = [];
                $buffer = '';
            } elseif ($char === '}') {
                if ($buffer !== '') {
                    $current[] = trim($buffer);
                    $buffer = '';
                }
                $parent = array_pop($stack);
                $prefix = trim(array_pop($context));
                $parent[] = [$prefix => $current];
                $current = $parent;
            } elseif ($char === ',') {
                if ($buffer !== '') {
                    $current[] = trim($buffer);
                    $buffer = '';
                }
            } else {
                $buffer .= $char;
            }
        }

        if ($buffer !== '') {
            $current[] = trim($buffer);
        }

        return $current;
    }

    /**
     * Percorre a estrutura de campos e monta o array associativo final.
     *
     * @param array<int|string|array> $structure Estrutura de campos tokenizada
     * @param string $prefix Prefixo atual para a chave final (ex: "ownerGeo")
     * @param string $path Caminho acumulado (ex: "owner.geo")
     * @param bool $isNested Se está dentro de um nível aninhado (define se será convertido para ['x']['y'])
     * @return array<string, string> Chave camelCase => Caminho para acessar no array
     */
    private static function flattenFields(array $structure, string $prefix = '', string $path = '', bool $isNested = false): array
    {
        $result = [];

        foreach ($structure as $field) {
            if (is_string($field)) {
                $key = self::buildKey($prefix, $field);
                $val = $isNested ? self::buildArrayPath($path, $field) : self::buildSimplePath($path, $field);
                $result[$key] = $val;
            } elseif (is_array($field)) {
                foreach ($field as $subPrefix => $subFields) {
                    $newPrefix = self::buildKey($prefix, $subPrefix);
                    $newPath = self::buildSimplePath($path, $subPrefix);
                    $result += self::flattenFields($subFields, $newPrefix, $newPath, true);
                }
            }
        }

        return $result;
    }

    /**
     * Constrói a chave camelCase da entrada.
     *
     * @param string $prefix Prefixo atual
     * @param string $field Campo atual
     * @return string Chave final (ex: ownerGeoMesoregiaoEmail)
     */
    private static function buildKey(string $prefix, string $field): string
    {
        if (!$prefix) {
            return lcfirst($field);
        }

        return $prefix . ucfirst($field);
    }

    /**
     * Retorna caminho simples com pontos (para campos não aninhados).
     *
     * @param string $path
     * @param string $field
     * @return string
     */
    private static function buildSimplePath(string $path, string $field): string
    {
        return $path ? "$path.$field" : $field;
    }

    /**
     * Retorna caminho estilo array (['campo']['subcampo']) para campos aninhados.
     *
     * @param string $path
     * @param string $field
     * @return string
     */
    private static function buildArrayPath(string $path, string $field): string
    {
        $full = array_filter(explode('.', $path . '.' . $field));
        return implode('', array_map(fn($part) => "['$part']", $full));
    }
}
