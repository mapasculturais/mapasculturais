<?php
use MapasCulturais\i;

$phase =  $this->controller->requestedEntity;

$get_fields = function ($opportunity) {
    $previous_phases = $opportunity->isFirstPhase ? [$opportunity] : $opportunity->previousPhases;

    if ($opportunity->firstPhase->id != $opportunity->id) {
        $previous_phases[] = $opportunity;
    }

    $_fields = [];
    foreach ($previous_phases as $phase) {
        foreach ($phase->registrationFieldConfigurations as $field) {
            $_fields[] = $field;
        }
        
        foreach ($phase->registrationFileConfigurations as $file) {
            $_fields[] = $file;
        }
    }

    return $_fields;
};

$get_workplan_fields = function ($opportunity) {
    $firstPhase = $opportunity->firstPhase;
    if (!$firstPhase->enableWorkplan) {
        return [];
    }

    $opp = $firstPhase;
    $workplanLabel = $opp->workplanLabelDefault ?: i::__('Plano de metas');
    $goalLabel = $opp->goalLabelDefault ?: i::__('Metas');
    $deliveryLabel = $opp->deliveryLabelDefault ?: i::__('Entregas');

    $fields = [];

    $fields[] = [
        'fieldName' => 'workplan_projectDuration',
        'title' => $workplanLabel . ' - ' . i::__('Duração do projeto (meses)'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_culturalArtisticSegment',
        'title' => $workplanLabel . ' - ' . i::__('Segmento artístico-cultural'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_goalMonthInitial',
        'title' => $goalLabel . ' - ' . i::__('Mês inicial'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_goalMonthEnd',
        'title' => $goalLabel . ' - ' . i::__('Mês final'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_goalTitle',
        'title' => $goalLabel . ' - ' . i::__('Título'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_goalDescription',
        'title' => $goalLabel . ' - ' . i::__('Descrição'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_goalCulturalMakingStage',
        'title' => $goalLabel . ' - ' . i::__('Etapa do fazer cultural'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryName',
        'title' => $deliveryLabel . ' - ' . i::__('Nome'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryDescription',
        'title' => $deliveryLabel . ' - ' . i::__('Descrição'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryTypeDelivery',
        'title' => $deliveryLabel . ' - ' . i::__('Tipo de entrega'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliverySegmentDelivery',
        'title' => $deliveryLabel . ' - ' . i::__('Segmento artístico cultural'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryExpectedNumberPeople',
        'title' => $deliveryLabel . ' - ' . i::__('Número previsto de pessoas'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryGeneraterRevenue',
        'title' => $deliveryLabel . ' - ' . i::__('Irá gerar receita?'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryRevenueDetails',
        'title' => $deliveryLabel . ' - ' . i::__('Detalhamento de receita (quantidade e valores)'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryPeriod',
        'title' => $deliveryLabel . ' - ' . i::__('Período de realização'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryArtChainLink',
        'title' => $deliveryLabel . ' - ' . i::__('Principal elo das artes'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryTotalBudget',
        'title' => $deliveryLabel . ' - ' . i::__('Orçamento total'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryNumberOfCities',
        'title' => $deliveryLabel . ' - ' . i::__('Número de municípios'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryNumberOfNeighborhoods',
        'title' => $deliveryLabel . ' - ' . i::__('Número de bairros'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryMediationActions',
        'title' => $deliveryLabel . ' - ' . i::__('Ações de mediação previstas'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryRevenueType',
        'title' => $deliveryLabel . ' - ' . i::__('Tipo de receita'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryCommercialUnits',
        'title' => $deliveryLabel . ' - ' . i::__('Unidades para comercialização'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryPaidStaffByRole',
        'title' => $deliveryLabel . ' - ' . i::__('Pessoas remuneradas por função'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryTeamComposition',
        'title' => $deliveryLabel . ' - ' . i::__('Composição da equipe (gênero e raça/cor)'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryCommunityCoauthors',
        'title' => $deliveryLabel . ' - ' . i::__('Envolvimento de comunidades como coautores'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryTransInclusion',
        'title' => $deliveryLabel . ' - ' . i::__('Estratégias de inclusão Trans e Travestis'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryAccessibilityPlan',
        'title' => $deliveryLabel . ' - ' . i::__('Medidas de acessibilidade previstas'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryEnvironmentalPractices',
        'title' => $deliveryLabel . ' - ' . i::__('Práticas socioambientais'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryPressStrategy',
        'title' => $deliveryLabel . ' - ' . i::__('Estratégia de imprensa'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryCommunicationChannels',
        'title' => $deliveryLabel . ' - ' . i::__('Canais de comunicação'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryInnovation',
        'title' => $deliveryLabel . ' - ' . i::__('Experimentação/inovação'),
        'checked' => false,
    ];

    $fields[] = [
        'fieldName' => 'workplan_deliveryDocumentationTypes',
        'title' => $deliveryLabel . ' - ' . i::__('Tipos de documentação'),
        'checked' => false,
    ];

    return $fields;
};

$phases_fields = [];

$current = $phase;
do {
    $fields = $get_fields($current);
    $workplan_fields = $get_workplan_fields($current);
    $phases_fields[$current->id] = array_merge($fields, $workplan_fields);
} while ($current = $current->nextPhase);

$this->jsObject['config']['fieldsVisibleEvaluators'] = $phases_fields;