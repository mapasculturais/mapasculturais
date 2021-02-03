<?php
use MapasCulturais\i;

var_dump($data);
$l_initiated = [];
$v_initiated = [];
$l_sent = [];
$v_sent = [];
$height = '30vh';
$width = '100%';
$color = [
    '#FF9A00',
    '#e500ff'
];

$serie = [];
$cont = 0;
foreach ($data as $key_data => $values){
    $serie[$cont] = [
        'label' => $key_data,              
        'color' => $color[$cont],
        'type' => 'line',
        'fill' => false
    ];

    foreach ($values as $key_v => $value){
        $labels[] = $key_v;
        $serie[$cont]['data'][] = $value;
    }
    $cont ++;
}

$labels = array_unique($labels);
sort($labels);

$this->part('charts/line', [
    'vertical' => true,
    'labels' => $labels,
    'series' => $serie,
    'height' => $height,
    'width' => $width
]);

function color()
{
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return "#".$c;
}
