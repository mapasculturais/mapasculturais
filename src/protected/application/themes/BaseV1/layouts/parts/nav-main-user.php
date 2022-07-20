<?php $this->applyTemplateHook('nav.main.user','before'); ?>
<li class="user">
    <a href="javascript:void(0);" class="js-submenu-toggle" data-submenu-target="$(this).parent().find('.submenu')" rel='noopener noreferrer'>
        <div class="avatar">
            <?php if ($app->user->profile->avatar): ?>
                <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
            <?php else: ?>
                <img src="<?php $this->asset('img/avatar--agent.png'); ?>" />
            <?php endif; ?>
        </div>
    </a>
    <ul class="submenu hidden">
        <li>
            <a href="<?php echo $app->createUrl('panel'); ?>"><?php echo $this->dict('site: panel');?></a>
        </li>
        
        <li>
            <a href="<?php echo $app->createUrl('agente', $app->user->profile->id) ?>"></span><?php \MapasCulturais\i::_e("Meu Perfil");?></a>
        </li>

        <?php if($app->isEnabled('agents')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.agents','before'); ?>

            <li>
                <a href="<?php echo $app->createUrl('panel', 'agents') ?>"><?php \MapasCulturais\i::_e("Meus Agentes");?></a>
                <?php $this->renderModalFor('agent'); ?>
            </li>

            <?php $this->applyTemplateHook('nav.dropdown.agents','after'); ?>
        <?php endif; ?>
        
        <?php if($app->isEnabled('events')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.events','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'events') ?>"><?php \MapasCulturais\i::_e("Meus Eventos");?></a>
                <a class="add" href="<?php echo $app->createUrl('event', 'create') ?>" ></a>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.events','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('spaces')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.spaces','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'spaces') ?>"><?php $this->dict('entities: My Spaces') ?></a>
                <?php $this->renderModalFor('space'); ?>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.spaces','after'); ?>
        <?php endif; ?>

       <?php if($app->isEnabled('seals') && $app->user->is('admin')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.seals','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'seals') ?>"><?php $this->dict('entities: My Seals') ?></a>
                <a class="add"href="<?php echo $app->createUrl('seal', 'create') ?>"></a>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.seals','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('projects')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.projects','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'projects') ?>"><?php \MapasCulturais\i::_e("Meus Projetos");?></a>
                <?php $this->renderModalFor('project'); ?>                
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.projects','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('opportunities')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.opportunities','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'opportunities') ?>"><?php \MapasCulturais\i::_e("Minhas Oportunidades");?></a>
                <?php $this->renderModalFor('opportunity'); ?>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.opportunities','after'); ?>
        <?php endif; ?>

        <?php if ($app->isEnabled('projects') || $app->isEnabled('opportunities')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.registrations','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'registrations') ?>"><?php \MapasCulturais\i::_e("Minhas Inscrições");?></a>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.registrations','after'); ?>
        <?php endif; ?>

        <?php if($app->user->is('saasAdmin') && $app->isEnabled('subsite')): ?>
            <?php $this->applyTemplateHook('nav.dropdown.subsite','before'); ?>
            <li>
                <a href="<?php echo $app->createUrl('panel', 'subsite') ?>"><?php $this->dict('entities: My Subsites') ?></a>
                <a class="add"href="<?php echo $app->createUrl('subsite', 'create') ?>"></a>
            </li>
            <?php $this->applyTemplateHook('nav.dropdown.subsite','after'); ?>
        <?php endif; ?>

        <li class="row"></li>
        <!--<li><a href="#" rel='noopener noreferrer'>Ajuda</a></li>-->
        <li>
            <?php if($app->getConfig('auth.provider') === 'Fake'): ?>
                <a href="<?php echo $app->createUrl('auth'); ?>"><?php \MapasCulturais\i::_e("Trocar Usuário");?></a>
                <?php if(!empty($fake_options)) echo $fake_options; ?>
            <?php endif; ?>
            <a href="<?php echo $app->createUrl('auth', 'logout'); ?>"><?php \MapasCulturais\i::_e("Sair");?></a>
        </li>
    </ul>
</li>
<!--.user-->
<?php $this->applyTemplateHook('nav.main.user','after'); ?>
