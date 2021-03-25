<?php

use MapasCulturais\i;

//Recebe os valores do sistema
$count = 0;
$colors = [];
$serie = [];
$height = 'auto';
$width = '100%';
$title = i::__('Inscrições ao longo do tempo');

$generate_colors = [];

//Prepara os dados para o gráfico
foreach ($data as $key_data => $values) {

    do {
        $new_color = is_callable($color) ? $color() : $color;
    } while (in_array($new_color, $generate_colors));
    
    $generate_colors[] = $new_color;
        
    $serie[$count] = [
        'label' => $key_data,
        'colors' => $new_color,
        'type' => 'line',
        'fill' => false,
    ];
    $legends[$count] = $key_data;
    $colors[$count] = $new_color;
    
    foreach ($values as $key_v => $value) {
        $labels[] = $key_v;
        $serie[$count]['data'][] = $value;
    }
    $count++;
}

$labels = array_unique($labels);
sort($labels);

$dataLabels = array_map(function ($label) {
    return (new DateTime($label))->format('d/m/Y');
}, $labels);

if ($self->checkIfChartHasData(array_column($serie, 'data'))) {

    // Imprime o gráfico na tela
    $this->part('charts/line', [
        'vertical' => true,
        'title' => $title,
        'labels' => $dataLabels,
        'series' => $serie,
        'height' => $height,
        'width' => $width,
        'legends' => $legends,
        'colors' => $colors,
        'opportunity' => $opportunity,
        'action' => 'registrationsByTime'
    ]);

}