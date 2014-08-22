<nav id="nav-do-painel" class="barra-esquerda barra-lateral painel">
    <ul>
    	<li><a <?php if($this->template == 'panel/index') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel') ?>"><span class="icone icon_house"></span> Início</a></li>
    	<li><a <?php if($this->template == 'panel/events') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'events') ?>"><span class="icone icon_calendar"></span> Meus Eventos</a></li>
        <li><a <?php if($this->template == 'panel/agents') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'agents') ?>"><span class="icone icon_profile"></span> Meus Agentes</a></li>
        <li><a <?php if($this->template == 'panel/spaces') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'spaces') ?>"><span class="icone icon_building"></span> Meus Espaços</a></li>
        <li><a <?php if($this->template == 'panel/projects') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'projects') ?>"><span class="icone icon_document_alt"></span> Meus Projetos</a></li>
    </ul>
</nav>
<!--#nav-do-painel-->

