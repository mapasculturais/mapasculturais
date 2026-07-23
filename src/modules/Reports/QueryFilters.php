<?php

namespace Reports;

/**
 * Fonte única de verdade para os filtros de status, tipo de proponente e
 * faixa/linha usados tanto pelo construtor de gráficos dinâmicos
 * (Controller::buildQuery) quanto pelos gráficos estáticos (Module::registrationsBy*).
 */
class QueryFilters
{
    const STATUS_OPERATORS = [
        'all'         => '>= 0',
        'draft'       => '= 0',
        'send'        => '>= 1',
        'invalid'     => '= 2',
        'notapproved' => '= 3',
        'waitlist'    => '= 8',
        'approved'    => '= 10',
    ];

    public static function statusOperator(string $statusValue): string
    {
        return self::STATUS_OPERATORS[$statusValue] ?? '> 0';
    }

    /**
     * @param string[]|null $proponentTypes
     * @return array{0:string,1:array<string,string>} [cláusula SQL (com "AND" e espaço à frente, ou string vazia), bind params]
     */
    public static function proponentTypeClause(?array $proponentTypes, string $tableAlias = 'r', string $paramPrefix = 'proponentType'): array
    {
        return self::inClause($proponentTypes, "{$tableAlias}.proponent_type", $paramPrefix);
    }

    /**
     * @param string[]|null $ranges
     * @return array{0:string,1:array<string,string>} [cláusula SQL (com "AND" e espaço à frente, ou string vazia), bind params]
     */
    public static function rangeClause(?array $ranges, string $tableAlias = 'r', string $paramPrefix = 'range'): array
    {
        return self::inClause($ranges, "{$tableAlias}.range", $paramPrefix);
    }

    /**
     * @param string[]|null $values
     * @return array{0:string,1:array<string,string>} [cláusula SQL "AND {$column} IN (...)" (ou string vazia), bind params]
     */
    private static function inClause(?array $values, string $column, string $paramPrefix): array
    {
        $values = array_values(array_filter($values ?? [], function ($value) {
            return $value !== null && $value !== '';
        }));

        if (empty($values)) {
            return ['', []];
        }

        $placeholders = [];
        $params = [];
        foreach ($values as $i => $value) {
            $key = "{$paramPrefix}{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $value;
        }

        $sql = " AND {$column} IN (" . implode(',', $placeholders) . ")";
        return [$sql, $params];
    }
}
