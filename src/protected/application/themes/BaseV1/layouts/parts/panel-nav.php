<nav id="panel-nav" class="sidebar-panel">
    <ul>
        <?php $app->applyHookBoundTo($this, 'panel.menu:before') ?>
        <li><a <?php if($this->template == 'panel/index') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel') ?>"><span class="icon icon-panel"></span> <?php \MapasCulturais\i::_e("Painel");?></a></li>
        
        <li>
            <a href="<?php echo $app->createUrl('agente', $app->user->profile->id) ?>">
                <span class="icon icon-agent"></span> <?php \MapasCulturais\i::_e("Meu Perfil"); ?>
            </a>
        </li>

        <?php if($app->isEnabled('agents')): ?>
            <?php $this->applyTemplateHook('nav.panel.agents','before'); ?>
            <li><a <?php if($this->template == 'panel/agents') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'agents') ?>"><span class="icon icon-group"></span> <?php \MapasCulturais\i::_e("Meus Agentes");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.agents','after'); ?>
        <?php endif; ?>
        
        <?php if($app->isEnabled('events')): ?>
            <?php $this->applyTemplateHook('nav.panel.events','before'); ?>
            <li><a <?php if($this->template == 'panel/events') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'events') ?>"><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Meus Eventos");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.events','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('spaces')): ?>
            <?php $this->applyTemplateHook('nav.panel.spaces','before'); ?>
            <li><a <?php if($this->template == 'panel/spaces') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'spaces') ?>"><span class="icon icon-space"></span> <?php $this->dict('entities: My Spaces') ?></a></li>
            <?php $this->applyTemplateHook('nav.panel.spaces','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('seals') && $app->user->is('admin')): ?>
            <?php $this->applyTemplateHook('nav.panel.seals','before'); ?>
            <li><a <?php if($this->template == 'panel/seals') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'seals') ?>"><span class="icon icon-seal"></span> <?php $this->dict('entities: My Seals') ?></a></li>
            <?php $this->applyTemplateHook('nav.panel.seals','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('projects')): ?>
            <?php $this->applyTemplateHook('nav.panel.projects','before'); ?>
            <li><a <?php if($this->template == 'panel/projects') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'projects') ?>"><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Meus Projetos");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.projects','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('opportunities')): ?>
            <?php $this->applyTemplateHook('nav.panel.opportunities','before'); ?>
            <li><a <?php if($this->template == 'panel/opportunities') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'opportunities') ?>"><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Minhas Oportunidades");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.opportunities','after'); ?>

            <?php $this->applyTemplateHook('nav.panel.registrations','before'); ?>
            <li><a <?php if($this->template == 'panel/registrations') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'registrations') ?>"><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Minhas Inscrições");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.registrations','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('subsite') && $app->user->is('saasAdmin')): ?>
            <?php $this->applyTemplateHook('nav.panel.subsite','before'); ?>
            <li><a <?php if($this->template == 'panel/subsite') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'subsite') ?>"><span class="icon icon-subsite"></span> <?php $this->dict('entities: My Subsites') ?></a></li>
            <?php $this->applyTemplateHook('nav.panel.subsite','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('apps')): ?>
            <?php $this->applyTemplateHook('nav.panel.apps','before'); ?>
            <li><a <?php if($this->template == 'panel/apps') echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'apps') ?>"><span class="icon icon-api"></span> <?php \MapasCulturais\i::_e("Meus Apps");?></a></li>
            <?php $this->applyTemplateHook('nav.panel.apps','after'); ?>
        <?php endif; ?>

        <?php if($app->user->is('admin')): ?>
            <?php $this->applyTemplateHook('nav.panel.userManagement','before'); ?>
            <li>
                <a <?php if($this->template == 'panel/user-management' && !isset($_GET['admin'])) echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'userManagement') ?>">
                    <span class="icon icon-login"></span> <?php \MapasCulturais\i::_e("Gestão de Usuários");?>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.panel.userManagement','after'); ?>
        <?php endif; ?>

        <?php if($app->user->is('superAdmin')): ?>
            <?php $this->applyTemplateHook('nav.panel.adminManagement','before'); ?>
            <li>
                <a <?php if ($this->template == 'panel/user-management' && isset($_GET['admin'])) echo 'class="active"'; ?> href="<?php echo $app->createUrl('panel', 'userManagement')?>?admin">
                    <span class="icon icon-group"></span> <?php \MapasCulturais\i::_e("Administradores");?>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.panel.adminManagement','after'); ?>
        <?php endif; ?>

        <?php $app->applyHookBoundTo($this, 'panel.menu:after') ?>
    </ul>
</nav>
<!--#panel-nav-->
