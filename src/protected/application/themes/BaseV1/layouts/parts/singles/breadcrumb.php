<?php
    //espaço, projetos
    $br = [];
    $curr = $entity;
    $br[] = $curr;

    if(isset($entity->parent)){
        while($curr->parent){
            $curr = $curr->parent;
            $br[] = $curr;
        }
        while(!$curr->equals($curr->owner)){
            $curr = $curr->owner;
            $br[] = $curr;
        }
    }

    $br = array_reverse($br);
    echo '<ul class="breadcrumb">';
    foreach ($br as $curr) {
        echo "<li><a href=\"$curr->singleUrl\" title=\"Ir para $curr->name\">" . $curr->name . '</a></li>';
    }
    echo '</ul>';

?>
