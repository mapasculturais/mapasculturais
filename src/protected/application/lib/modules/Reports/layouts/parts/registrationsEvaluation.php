<?php
use MapasCulturais\i;
$dataOportunity = $opportunity->getEvaluationCommittee();
//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '100%';
$total = 0;
$colors = [];
$title = i::__('Resultado da avaliação');

if ($dataOportunity[0]->owner->type == 'technical') {
    $series[0]['color'] = is_callable($color) ? $color() : $color;

    foreach ($data as $key => $value) {
        $label[] = $key;
        $values[] = $value;
        $legends[] = $key;
    }

    $series[0]['color'];

    $this->part('charts/bar', [
        'labels' => $label,
        'series' => [
            ['label' => 'Quantidade', 'data' => $values, 'color' => '#EB7E33'],
        ],
        'legends' => [],
        'height' => $height,
        'width' => $width,
        // 'legends' => $legends,
        'title' => $title,
        'top' => 70,
        'opportunity' => $opportunity,
        'action' => 'exportRegistrationsByEvaluation',
    ]);
} else {
    //Prepara os dados para o gráfico
    foreach ($data as $key => $value) {
        foreach ($value as $v_key => $v) {

            if ($v_key == "evaluated") {
                $status = i::__('Avaliada');
            } else {
                $status = i::__('Não avaliada');
            }

            $label[] = $status;
            $legends[] = $status;
            $values[] = $v;
            $colors[] = is_callable($color) ? $color() : $color;
        }

    }

    // Imprime o gráfico na tela
    $this->part('charts/pie', [
        'labels' => $label,
        'data' => $values,
        'colors' => $colors,
        'height' => $height,
        'width' => $width,
        'legends' => $legends,
        'title' => $title,
        'top' => 70,
        'opportunity' => $opportunity,
        'action' => 'exportRegistrationsByEvaluation',
    ]);
}
