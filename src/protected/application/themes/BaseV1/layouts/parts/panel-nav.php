<nav id="panel-nav" class="sidebar-panel">
    <ul>
        <?php $app->applyHookBoundTo($this, 'panel.menu:before') ?>
        <li><a <?php if($this->template == 'panel/index') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel') ?>"><span class="icon icon-panel"></span> Panel</a></li>
        
        <?php if($app->isEnabled('events')): ?>
            <?php $this->applyTemplateHook('nav.panel.events','before'); ?>
            <li><a <?php if($this->template == 'panel/events') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'events') ?>"><span class="icon icon-event"></span> Mis Eventos</a></li>
            <?php $this->applyTemplateHook('nav.panel.events','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('agents')): ?>
            <?php $this->applyTemplateHook('nav.panel.agents','before'); ?>
            <li><a <?php if($this->template == 'panel/agents') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'agents') ?>"><span class="icon icon-agent"></span> Mis Agentes</a></li>
            <?php $this->applyTemplateHook('nav.panel.agents','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('spaces')): ?>
            <?php $this->applyTemplateHook('nav.panel.spaces','before'); ?>
            <li><a <?php if($this->template == 'panel/spaces') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'spaces') ?>"><span class="icon icon-space"></span> <?php $this->dict('entities: My Spaces') ?></a></li>
            <?php $this->applyTemplateHook('nav.panel.spaces','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('projects')): ?>
            <?php $this->applyTemplateHook('nav.panel.projects','before'); ?>
            <li><a <?php if($this->template == 'panel/projects') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'projects') ?>"><span class="icon icon-project"></span> Mis Proyectos</a></li>
            <?php $this->applyTemplateHook('nav.panel.projects','after'); ?>
        
            <?php $this->applyTemplateHook('nav.panel.registrations','before'); ?>
            <li><a <?php if($this->template == 'panel/registrations') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'registrations') ?>"><span class="icon icon-project"></span> Mis Inscripciones</a></li>
            <?php $this->applyTemplateHook('nav.panel.registrations','after'); ?>
        <?php endif; ?>
    <!--#oculto Mis Apps del panel-->        
      <?php //if($app->isEnabled('apps')): ?> 
           <?php //$this->applyTemplateHook('nav.panel.apps','before'); ?>
           <!-- <li><a <?php //if($this->template == 'panel/apps') echo 'class="active"'; ?> href="<?php // echo $app->createUrl('panel', 'apps') ?>"><span class="icon icon-api"></span> Mis Apps</a></li> -->
            <?php // $this->applyTemplateHook('nav.panel.apps','after'); ?>
        <?php //endif; ?>
            
        <?php $app->applyHookBoundTo($this, 'panel.menu:after') ?>
    </ul>
</nav>
<!--#panel-nav-->
