<?php
$l_initiated = [];
$v_initiated = [];
$l_sent = [];
$v_sent = [];
$height = '30vh';
$width = '100%';

foreach ($data['initiated'] as $key => $value){
    $l_initiated[] = $key;
    $v_initiated[] = $value;
}

foreach ($data['sent'] as $key => $value){
    $l_sent[] = $key;
    $v_sent[] = $value;
}

$this->part('charts/line', [
    // 'horizontal' => true,
    'labels' => array_merge($l_initiated, $l_sent),
    'series' => [
        ['label' => 'Iniciada', 'data' => $v_initiated, 'color' => '#800', 'type' => 'line', 'fill' => false],
        ['label' => 'Enviadas', 'data' => $v_sent, 'color' => '#76e012', 'type' => 'line', 'fill' => false],
        
    ],
    'height' => $height,
    'width' => $width,

]);