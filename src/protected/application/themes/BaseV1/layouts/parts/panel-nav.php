<nav id="panel-nav" class="sidebar-panel">
    <ul>
        <?php $app->applyHookBoundTo($this, 'panel.menu:before') ?>
    	<li><a <?php if($this->template == 'panel/index') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel') ?>"><span class="icon icon-panel"></span> Painel</a></li>
    	<li><a <?php if($this->template == 'panel/events') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'events') ?>"><span class="icon icon-event"></span> Meus Eventos</a></li>
        <li><a <?php if($this->template == 'panel/agents') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'agents') ?>"><span class="icon icon-agent"></span> Meus Agentes</a></li>
        <li><a <?php if($this->template == 'panel/spaces') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'spaces') ?>"><span class="icon icon-space"></span> Meus Espaços</a></li>
        <li><a <?php if($this->template == 'panel/projects') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'projects') ?>"><span class="icon icon-project"></span> Meus Projetos</a></li>
        <li><a <?php if($this->template == 'panel/registrations') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'registrations') ?>"><span class="icon icon-project"></span> Minhas Inscrições</a></li>
        <li><a <?php if($this->template == 'panel/apps') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'apps') ?>"><span class="icon icon-api"></span> Meus Apps</a></li>
        <?php $app->applyHookBoundTo($this, 'panel.menu:after') ?>
    </ul>
</nav>
<!--#panel-nav-->
