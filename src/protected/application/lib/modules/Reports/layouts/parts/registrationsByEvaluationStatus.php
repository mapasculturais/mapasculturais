<?php

use MapasCulturais\i;
$dataOportunity = $opportunity->getEvaluationCommittee();
//Recebe os valores do sistema
$label = [];
$values = [];
$height = 'auto';
$width = '100%';
$colors = [];
$series = [];
$count = 0;
$title = i::__('Status de avaliações');




if($dataOportunity[0]->owner->type == 'technical'){
    $series[0]['color'] = is_callable($color) ? $color() : $color;

    foreach ($data as $key => $value) {  
        $label[] = $key;
        $values[] = $value;
    }
    
    $series[0]['color'];

    $this->part('charts/bar', [
        'labels' => $label,
        'series' => [
            ['label' => 'Quantidade', 'data' => $values, 'color' => '#EB7E33'],
        ],
        'legends' => []
    ]);
}else{
    //Prepara os dados para o gráfic
    foreach ($data as $key => $value) {
        $label[] = $key;
        $legends[] = $key;
        $values[] = $value;
        $colors[] = is_callable($color) ? $color() : $color;
    }

 // Imprime o gráfico na tela
 $this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'colors' => $colors,
    'legends' => $legends,
    'title' => $title,
    'height' => $height,
    'width' => $width,
    'top' => 70,
    'bottom' => 30,
    'opportunity' => $opportunity,
    'action' => 'exportRegistrationsByEvaluationStatus'
]);
}
