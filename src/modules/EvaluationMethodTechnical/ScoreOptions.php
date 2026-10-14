<?php

namespace EvaluationMethodTechnical;

use InvalidArgumentException;

/** Escalas numéricas dos critérios técnicos, independentes da interface. */
class ScoreOptions
{
    public static function mode($mode): string
    {
        $mode = $mode ?: 'typed';
        if (!in_array($mode, ['typed', 'fixed'], true)) {
            throw new InvalidArgumentException('Modo de atribuição de notas inválido.');
        }
        return $mode;
    }

    public static function parse($value): array
    {
        $values = is_string($value) ? explode(',', $value) : $value;
        if (!is_array($values) || !$values) {
            throw new InvalidArgumentException('Informe as notas permitidas separadas por vírgula.');
        }

        $options = [];
        foreach ($values as $item) {
            if (is_string($item)) {
                $item = trim($item);
                if (!preg_match('/^\d+(?:\.\d+)?$/D', $item)) {
                    throw new InvalidArgumentException('Use notas não negativas e ponto para decimais.');
                }
            } elseif (!is_int($item) && !is_float($item)) {
                throw new InvalidArgumentException('As notas permitidas devem ser numéricas.');
            }
            $number = (float) $item;
            if (!is_finite($number) || $number < 0 || in_array($number, $options, true)) {
                throw new InvalidArgumentException('As notas devem ser finitas, não negativas e sem repetições.');
            }
            $options[] = $number;
        }
        sort($options, SORT_NUMERIC);
        return $options;
    }

    public static function normalizeCriteria($mode, array $criteria): array
    {
        if (self::mode($mode) === 'typed') {
            return $criteria;
        }
        return array_map(function($criterion) {
            $criterion = clone (object) $criterion;
            $criterion->scoreOptions = self::parse($criterion->scoreOptions ?? null);
            $criterion->min = $criterion->scoreOptions[0];
            $criterion->max = $criterion->scoreOptions[count($criterion->scoreOptions) - 1];
            return $criterion;
        }, $criteria);
    }

    public static function contains(array $options, $value): bool
    {
        if (!is_numeric($value) || !is_finite((float) $value)) {
            return false;
        }
        return in_array((float) $value, self::parse($options), true);
    }

    public static function hasChanged($old_mode, array $old_criteria, $new_mode, array $new_criteria): bool
    {
        if (self::mode($old_mode) !== self::mode($new_mode)) {
            return true;
        }
        if (self::mode($new_mode) === 'typed') {
            return false;
        }
        $scales = function(array $criteria) {
            $result = [];
            foreach ($criteria as $criterion) {
                $criterion = (object) $criterion;
                $result[$criterion->id] = self::parse($criterion->scoreOptions ?? null);
            }
            ksort($result);
            return $result;
        };
        return $scales($old_criteria) !== $scales($new_criteria);
    }
}
