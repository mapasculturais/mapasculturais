<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '60%';
$total = 0;
$colors = [];
$legends = [];
$title = i::__('Categorias da oportunidade');
$total = array_sum(array_column($data, 'count'));

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {

    $color = $self->getChartColors();

    $values[] = $value['count'];
    $label[] = $value['category'];
    $colors[] = $color[0];
    $legends[] = $value['category'] . '<br>' . $value['count'] . ' (' . number_format(($value['count'] / $total) * 100, 2, '.', '') . '%)';
    
}

if ($self->checkIfChartHasData($values)) {

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
        'action' => 'exportRegistrationsByCategory'
    ]);

}