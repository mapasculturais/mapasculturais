<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$height = '50vw';
$width = '100%';
$colors = [];
$title = i::__('Inscrições por status da avaliação');

//Prepara os dados para o gráfic
foreach ($data as $key => $value) {
    $label[] = $key;
    $values[] = $value;
    $colors[] = is_callable($color) ? $color() : $color;
}

// Imprime o gráfico na tela
$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'colors' => $colors,
    'legends' => $label,
    'title' => $title,
]);
