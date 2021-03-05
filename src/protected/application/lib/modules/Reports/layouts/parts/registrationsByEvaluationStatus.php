<?php

use MapasCulturais\i;
// Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '60%';
$colors = [];
$series = [];
$count = 0;
$title = i::__('Status de avaliações');
$total = array_sum(array_column($data, null));

// Prepara os dados para o gráfico
foreach ($data as $key => $value) {
    $label[] = $key;
    $legends[] = $key . '<br>' . $value . ' (' . number_format(($value / $total) * 100, 2, '.', '') . '%)';
    $values[] = $value;
    $colors[] = is_callable($color) ? $color() : $color;
}

 // Imprime o gráfico na tela
 $this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'total' => $total,
    'colors' => $colors,
    'legends' => $legends,
    'title' => $title,
    'height' => $height,
    'width' => $width,
    'opportunity' => $opportunity,
    'action' => 'exportRegistrationsByEvaluationStatus'
]);

