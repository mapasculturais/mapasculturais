<?php
$label = [];
$values = [];
$l_notEvaluated = [];
$v_notEvaluated = [];
$total = 0;


foreach ($data as $key => $value){
    foreach ($value as $v_key => $v){

        if($v_key == "evaluated"){
            $status = "Avaliada";
        }else{
            $status = "Não avaliada";
        }

        $label[] = $status;
        $values[] = $v;
    }
    
}



// var_dump($label, $values);
$this->part('charts/pie', [
    'labels' => $label,
    'data' => $values,
    'color' => ['black', 'white', 'yellow']
]);


?>