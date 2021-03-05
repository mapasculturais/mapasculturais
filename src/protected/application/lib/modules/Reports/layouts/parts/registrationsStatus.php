<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$colors = [];
$height = 'auto';
$width = '60%';
$title = i::__('Status das inscrições');

$total = [];
foreach ($data as $key => $value) {
    if ($key != i::__('Rascunho')) {
        $total[] = $value;
    }
}
$total = array_sum($total);

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {
    if ($key != i::__('Rascunho')) {
        $label[] = $key;
        $legends[] = $key . '<br>' . $value . ' (' . number_format(($value / $total) * 100, 2, '.', '') . '%)';
        $values[] = $value;
        $colors[] = is_callable($color) ? $color() : $color;
    }
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
    'action' => 'exportRegistrationsByStatus'
]);
