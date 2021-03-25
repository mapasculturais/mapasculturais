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

$generate_colors = [];

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {

    do {
        $new_color = is_callable($color) ? $color() : $color;
    } while (in_array($new_color, $generate_colors));
    
    $generate_colors[] = $new_color;

    if ($key != i::__('Rascunho')) {

        if($value == 0 || $value == "0"){
            $percent = 0;
        }else{
            $percent = number_format(($value / $total) * 100, 2, '.', '');
        }

        $label[] = $key;
        $legends[] = $key . '<br>' . $value . ' (' . $percent . '%)';
        $values[] = $value;
        $colors[] = $new_color;
    }
}

if ($self->checkIfChartHasData($values)) {

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

}