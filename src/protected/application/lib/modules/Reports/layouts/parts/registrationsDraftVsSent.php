<?php
$label = ['Rascunho', "Enviadas"];
$values = [];
$total = 0;
foreach ($data as $key => $value){
    if($key == "Rascunho"){
        $values[0] = $value;
    }else{
        $total = ($total + $value);
        $values[1] = $total;
    }
}

$this->part('charts/pie', [
    'serie' => [
        ['label' => $label[0], 'data' => $values[0], 'color' => 'yellow'],
        ['label' => $label[1], 'data' => $values[1], 'color' => 'green']
    ]
]);
?>