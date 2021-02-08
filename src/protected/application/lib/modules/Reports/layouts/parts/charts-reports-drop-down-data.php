    <?php
        $route = MapasCulturais\App::i()->createUrl('reports', 'exportRegistrationsByStatus', ['opportunity_id' => $opportunity->id]); 
    ?>
    <a onclick="openDropdown(this)" name="<?=$chart_id?>" ><i class="fas fa-align-justify teste"></i></a>
    <div id="drop-<?=$chart_id?>" class="open-dropDown">
        <ul>
            <a href="<?=$route?>"><li>Baixar CSV</li></a>
        </ul>
    </div>
