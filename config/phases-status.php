<?php

return [
    'opportunityPhase.defaultStatuses.dataCollection'  => [],
    'opportunityPhase.defaultStatuses.simple' => [
        // '2' => i::__('Negado'),
        // '3' => i::__('Indeferido'),
        // '10' => i::__('Deferido'),
    ],
    'opportunityPhase.defaultStatuses.documentary'  => [],
    'opportunityPhase.defaultStatuses.technical'  => [],
    'opportunityPhase.defaultStatuses.qualification'  => [],
    'opportunityPhase.defaultStatuses.appeal'  => [], // Fase de recurso
    'opportunityPhase.defaultStatuses.lastPhase'  => [],
    'opportunityPhase.defaultStatuses.reporting'  => [], // Fase de prestação de informações

    // Oculta o status Negado nas fases de recurso
    'opportunityPhase.appeal.hideInvalidStatus' => env('APPEAL_PHASE_HIDE_INVALID_STATUS', false),
];