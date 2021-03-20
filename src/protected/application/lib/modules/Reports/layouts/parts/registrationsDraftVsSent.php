<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [i::__('Rascunhos'), i::__('Enviadas')];
$legends = [i::__('Rascunhos'), i::__('Enviadas')];
$values = [];
$height = 'auto';
$width = '60%';
$count = 0;
$title = i::__('Status de envio das inscrições');

$total = array_sum($data);

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {
    
    if ($key == i::__('Rascunho')) {
        $values[0] = $value;
        $colors[0] = is_callable($color) ? $color() : $color;
        $legends[0] = i::__('Rascunhos') . '<br>' . $value . ' (' . number_format(($value / $total) * 100, 2, '.', '') . '%)';
    } else {
        $count = ($count + $value);
        $values[1] = $count;
        $colors[1] = is_callable($color) ? $color() : $color;
        $legends[1] = i::__('Enviadas') . '<br>' . $count . ' (' . number_format(($count / $total) * 100, 2, '.', '') . '%)';
    }

}

// Imprime o gráfico na tela
$this->part('charts/pie', [
    'serie' => [
        ['label' => $label[0], 'data' => $values[0], 'colors' => $colors[0]],
        ['label' => $label[1], 'data' => $values[1], 'colors' => $colors[1]],
    ],
    'total' => $total,
    'height' => $height,
    'width' => $width,
    'legends' => $legends,
    'colors' => $colors,
    'title' => $title,
    'height' => $height,
    'width' => $width,
    'opportunity' => $opportunity,
    'action' => 'exportRegistrationsDraftVsSent'
]);