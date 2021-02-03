<?php

$label = [];
$values = [];
$height = '50vw';
$width = '100%';

foreach ($data as $key => $value){   
        $label[] = $key;
        $values[] = $value;
}



$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'color' => ['black', 'white', 'yellow', 'green', 'red', 'blue']
]);