<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '100%';
$colors = [];
$title = i::__('Status de avaliações');

//Prepara os dados para o gráfic
foreach ($data as $key => $value) {
    $label[] = $key;
    $legends[] = $key;
    $values[] = $value;
    $colors[] = is_callable($color) ? $color() : $color;
}

// Imprime o gráfico na tela
$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'colors' => $colors,
    'legends' => $legends,
    'title' => $title,
    'height' => $height,
    'width' => $width,
    'opportunity' => $opportunity,
    'action' => 'exportRegistrationsByEvaluationStatus'
]);
