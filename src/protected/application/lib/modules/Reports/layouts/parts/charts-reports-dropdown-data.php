    <?php
        use MapasCulturais\i;
        $route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity' => $opportunity->id, 'action' => $action]); 
    ?>
    <a onclick="openDropdown(this)" name="<?=$chart_id?>" ><i class="fas fa-align-justify"></i></a>
    <div id="drop-<?=$chart_id?>" class="dropdown-content">
        <h4><?php i::_e("Opções"); ?></h4>
        <ul>
            <li><a href="<?=$route?>"><?php i::_e("Baixar CSV"); ?></a></li>
        </ul>
    </div>