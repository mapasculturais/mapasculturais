<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '100%';
$total = 0;
$colors = [];
$legends = [];
$title = i::__('Categorias da oportunidade');

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {
    $values[] = $value['count'];
    $label[] = $value['category'];
    $colors[] = is_callable($color) ? $color() : $color;
    $legends[] = $value['category'];

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
    'action' => 'exportRegistrationsByCategory'
]);