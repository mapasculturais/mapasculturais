<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$l_notEvaluated = [];
$v_notEvaluated = [];
$height = '30%';
$width = '100%';
$total = 0;
$colors = [];
$title = i::__('Inscrições por avaliações');

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
]);
