<?php
    use MapasCulturais\App;
    $app = App::i();

    $br = [];
    $curr = $app->entity;
    $br[] = $curr;

    $app->log->debug(isset($entity->parent));

    if(isset($entity->parent)){
        while($curr->parent){
            $curr = $curr->parent;
            $br[] = $curr;
        }
        while(!$curr->equals($curr->owner)){
            $curr = $curr->owner;
            $br[] = $curr;
            foreach ($br as $curr) {
                echo "<li><a href=\"$curr->singleUrl\" title=\"Ir para $curr->name\">" . $curr->name . '</a></li>';
            }
            echo '</ul>';

        }
        $br = array_reverse($br);
        echo '<ul class="breadcrumb">';
        if($app->user->is('superAdmin')){
            echo '<li><a href="' . $app->createUrl('panel') . '">Painel</a></li>';
        }
        foreach ($br as $curr) {
            echo "<li><a href=\"$curr->singleUrl\" title=\"Ir para $curr->name\">" . $curr->name . '</a></li>';
        }
        echo '</ul>';
    }
?>
