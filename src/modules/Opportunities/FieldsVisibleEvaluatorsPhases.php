<?php

namespace Opportunities;

use MapasCulturais\Entities\Opportunity;

/**
 * Monta o índice opportunityId => campos usados pela tela
 * "Configurar campos visíveis para os avaliadores".
 */
class FieldsVisibleEvaluatorsPhases
{
    /**
     * Percorre a cadeia nextPhase e inclui a fase de recurso de cada etapa,
     * porque o recurso não entra em nextPhase.
     *
     * @param callable(Opportunity):array $get_fields
     * @param callable(Opportunity):array $get_workplan_fields
     * @return array<int, array>
     */
    public static function buildIndex(Opportunity $phase, callable $get_fields, callable $get_workplan_fields): array
    {
        $phases_fields = [];

        $current = $phase;
        do {
            $phases_fields[$current->id] = array_merge($get_fields($current), $get_workplan_fields($current));

            if ($current->appealPhase) {
                $appeal = $current->appealPhase;
                $phases_fields[$appeal->id] = array_merge($get_fields($appeal), $get_workplan_fields($appeal));
            }
        } while ($current = $current->nextPhase);

        return $phases_fields;
    }
}
