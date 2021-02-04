<?php
use MapasCulturais\i;

//Recebe os valores do sistema
$label = [];
$values = [];
$colors = [];
$title = i::__('Inscrições por status');

//Prepara os dados para o gráfico
foreach ($data as $key => $value) {
    if ($key != i::__('Rascunho')) {
        $label[] = $key;
        $values[] = $value;
        $colors[] = is_callable($color) ? $color() : $color;
    }
}

// Imprime o gráfico na tela
$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'colors' => $colors,
    'legends' => $label,
    'title' => $title,
]);
