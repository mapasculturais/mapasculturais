<?php

namespace OpportunityAppealPhase\Services;

use DomainException;

class AppealTechnicalCorrectionCalculator
{
    public function buildCorrectedEvaluationData(array $criteria, array $before, array $changes): array
    {
        $criteriaById = $this->indexCriteriaById($criteria);

        if ($changes === []) {
            throw new DomainException('Selecione ao menos um critério para correção.');
        }

        $after = $before;
        $changedCriteria = [];
        foreach ($changes as $criterionId => $value) {
            if (!isset($criteriaById[$criterionId])) {
                throw new DomainException("O critério {$criterionId} não existe mais na configuração técnica.");
            }
            if (!is_numeric($value)) {
                throw new DomainException("A nota do critério {$criterionId} deve ser numérica.");
            }

            $criterion = $criteriaById[$criterionId];
            $value = (float) $value;
            $min = (float) ($criterion['min'] ?? 0);
            $max = (float) ($criterion['max'] ?? 0);
            if ($value < $min || $value > $max) {
                throw new DomainException("A nota do critério {$criterionId} deve estar entre {$min} e {$max}.");
            }

            $oldValue = $before[$criterionId] ?? null;
            if (!is_numeric($oldValue)) {
                throw new DomainException("A avaliação original não possui nota válida para o critério {$criterionId}.");
            }

            if ((float) $oldValue !== $value) {
                $after[$criterionId] = $value;
                $changedCriteria[$criterionId] = [
                    'before' => (float) $oldValue,
                    'after' => $value,
                ];
            }
        }

        if ($changedCriteria === []) {
            throw new DomainException('A correção não altera nenhuma nota. Use a confirmação sem mudança.');
        }

        return [
            'before' => $before,
            'after' => $after,
            'beforeResult' => $this->calculateEvaluationResult($criteria, $before),
            'afterResult' => $this->calculateEvaluationResult($criteria, $after),
            'changedCriteria' => $changedCriteria,
        ];
    }

    public function calculateConsolidatedResult(array $criteria, array $evaluations): float
    {
        if ($evaluations === []) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($evaluations as $evaluationData) {
            $total += $this->calculateEvaluationResult($criteria, (array) $evaluationData);
        }

        return round($total / count($evaluations), 2);
    }

    public function calculateScorePreview(float $consolidatedResult, mixed $appliedPointReward, ?bool $eligible): array
    {
        $reward = $appliedPointReward ? (array) $appliedPointReward : [];
        $type = (string) ($reward['type'] ?? 'percentage');
        $percentage = (float) ($reward['percentage'] ?? 0);
        $fixed = (float) ($reward['fixed'] ?? 0);

        $score = $type === 'fixed'
            ? $consolidatedResult + $fixed
            : $consolidatedResult + (($consolidatedResult * $percentage) / 100);

        $reward['raw'] = $consolidatedResult;
        $reward['type'] = $type;
        $reward['percentage'] = $percentage;
        $reward['fixed'] = $fixed;
        $reward['roof'] = (float) ($reward['roof'] ?? 0);
        $reward['rules'] = array_map(
            fn($rule) => is_object($rule) ? (array) $rule : $rule,
            (array) ($reward['rules'] ?? [])
        );

        return [
            'score' => round($score, 2),
            'eligible' => $eligible,
            'appliedPointReward' => $reward,
        ];
    }

    private function indexCriteriaById(array $criteria): array
    {
        $criteriaById = [];
        foreach ($criteria as $criterion) {
            $criterion = (array) $criterion;
            if (!empty($criterion['id'])) {
                $criteriaById[(string) $criterion['id']] = $criterion;
            }
        }
        return $criteriaById;
    }

    private function calculateEvaluationResult(array $criteria, array $evaluationData): float
    {
        $total = 0.0;
        foreach ($criteria as $criterion) {
            $criterion = (array) $criterion;
            $criterionId = (string) ($criterion['id'] ?? '');
            if ($criterionId === '' || !isset($evaluationData[$criterionId]) || !is_numeric($evaluationData[$criterionId])) {
                throw new DomainException("A avaliação está incompleta no critério {$criterionId}.");
            }
            $total += (float) $evaluationData[$criterionId] * (float) ($criterion['weight'] ?? 0);
        }
        return $total;
    }
}
