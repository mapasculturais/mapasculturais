<?php
$label = [];
$values = [];
$total = 0;
foreach ($data as $key => $value){
    if($key != "Rascunho"){
        $label[] = $key;
        $values[] = $value;
    }
}

$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'color' => ['black', 'white', 'yellow']
]);
?>