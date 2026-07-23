<?php

namespace Reports;

/**
 * Fonte única de verdade para os filtros de status e tipo de proponente
 * usados tanto pelo construtor de gráficos dinâmicos (Controller::buildQuery)
 * quanto pelos gráficos estáticos (Module::registrationsBy*).
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
        $proponentTypes = array_values(array_filter($proponentTypes ?? [], function ($value) {
            return $value !== null && $value !== '';
        }));

        if (empty($proponentTypes)) {
            return ['', []];
        }

        $placeholders = [];
        $params = [];
        foreach ($proponentTypes as $i => $type) {
            $key = "{$paramPrefix}{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $type;
        }

        $sql = " AND {$tableAlias}.proponent_type IN (" . implode(',', $placeholders) . ")";
        return [$sql, $params];
    }
}
