<?php
use MapasCulturais\i;
$dataOportunity = $opportunity->getEvaluationCommittee();
//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '60%';
$total = 0;
$colors = [];
$title = i::__('Resultado da avaliação');
$total = '';

if ($opportunity->evaluationMethod->slug == 'technical') {
    
    $color = $self->getChartColors();
    $series[0]['color'] = $color[0];

    foreach ($data as $key => $value) {
        $label[] = $key;
        $values[] = $value;
        $legends[] = $key;
    }

    if ($self->checkIfChartHasData($values)) {

        $this->part('charts/bar', [
            'labels' => $label,
            'series' => [
                ['label' => 'Quantidade', 'data' => $values, 'color' => '#EB7E33'],
            ],
            'legends' => [],
            'height' => $height,
            'width' => '100%',
            'title' => $title,
            'opportunity' => $opportunity,
            'action' => 'registrationsByEvaluationStatusBar',
        ]);

    }
} else {

    $total = [];
    foreach ($data as $key => $value) {
        foreach ($value as $v_key => $v) {
            $total[] = $v;
        }
    }
    $total = array_sum(array_column($total, null));

    if($total <=0){
        return;
    }

    // Prepara os dados para o gráfico
    foreach ($data as $key => $value) {

        foreach ($value as $v_key => $v) {

            $color = $self->getChartColors();

            if ($v_key == "evaluated") {
                $status = i::__('Avaliada');
            } else {
                $status = i::__('Não avaliada');
            }
            $label[] = $status;
            $legends[] = $status . '<br>' . $v . ' (' . number_format(($v / $total) * 100, 2, '.', '') . '%)';
            $values[] = $v;
            $colors[] = $color[0];
        }
    }

    if ($status != "all" || $self->checkIfChartHasData($values)) {

        // Imprime o gráfico na tela
        $this->part('charts/pie', [
            'labels' => $label,
            'data' => $values,
            'total' => $total,
            'colors' => $colors,
            'height' => $height,
            'width' => $width,
            'legends' => $legends,
            'title' => $title,
            'opportunity' => $opportunity,
            'action' => 'exportRegistrationsByEvaluation',
        ]);

    }    
}
