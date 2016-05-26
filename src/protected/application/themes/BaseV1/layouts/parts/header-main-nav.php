<?php if($app->getConfig('auth.provider') === 'Fake' && $app->user->id !== 1): ob_start(); ?>
    
<?php $fake_options = ob_get_clean(); endif; ?>

<nav id="main-nav" class="clearfix">
    <ul class="menu entities-menu clearfix">
        <?php if($app->isEnabled('events')): ?>
            <?php $this->applyTemplateHook('nav.main.events','before'); ?>
            <li id="entities-menu-event" ng-class="{'active':data.global.filterEntity === 'event'}" ng-click="tabClick('event')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(event:!t),filterEntity:event))'; ?>">
                    <div class="icon icon-event"></div>
                    <div class="menu-item-label">Eventos</div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.events','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('spaces')): ?>
            <?php $this->applyTemplateHook('nav.main.spaces','before'); ?>
            <li id="entities-menu-space" ng-class="{'active':data.global.filterEntity === 'space'}" ng-click="tabClick('space')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(space:!t),filterEntity:space))'; ?>">
                    <div class="icon icon-space"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Spaces') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.spaces','after'); ?>
        <?php endif; ?>
        
        <?php if($app->isEnabled('agents')): ?>
            <?php $this->applyTemplateHook('nav.main.agents','before'); ?>
            <li id="entities-menu-agent" ng-class="{'active':data.global.filterEntity === 'agent'}" ng-click="tabClick('agent')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(agent:!t),filterEntity:agent))'; ?>">
                    <div class="icon icon-agent"></div>
                    <div class="menu-item-label">Agentes</div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.agents','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('projects')): ?>
            <?php $this->applyTemplateHook('nav.main.projects','before'); ?>
            <li id="entities-menu-project"  ng-class="{'active':data.global.filterEntity === 'project'}" ng-click="tabClick('project')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(project:!t),filterEntity:project,viewMode:list))'; ?>">
                    <div class="icon icon-project"></div>
                    <div class="menu-item-label">Proyectos</div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.projects','after'); ?>
        <?php endif; ?>
    </ul>
    <!--.menu.entities-menu-->
    <ul class="menu session-menu clearfix">
        <?php if ($app->auth->isUserAuthenticated()): ?>
            <?php $this->applyTemplateHook('nav.main.notifications','before'); ?>
            <li class="notifications" ng-controller="NotificationController" ng-hide="data.length == 0">
                <a class="js-submenu-toggle" data-submenu-target="$(this).parent().find('.submenu')">
                    <div class="icon icon-notifications"></div>
                    <div class="menu-item-label">Notificaciones</div>
                </a>
                <ul class="submenu hidden">
                    <li>
                        <div class="clearfix">
                            <h6 class="alignleft">Notificaciones</h6>
                            <a href="#" style="display:none" class="staging-hidden hltip icon icon-check_alt" title="Marcar todas como leídas"></a>
                        </div>
                        <ul>
                            <li ng-repeat="notification in data" on-last-repeat="adjustScroll();">
                                <p class="notification clearfix">
                                    <span ng-bind-html="notification.message"></span>
                                    <br>

                                    <a ng-if="notification.request.permissionTo.approve" class="btn btn-small btn-success" ng-click="approve(notification.id)">aceptar</a>

                                    <span ng-if="notification.request.permissionTo.reject">
                                        <span ng-if="notification.request.requesterUser.id === MapasCulturais.userId">
                                            <a class="btn btn-small btn-default" ng-click="reject(notification.id)">cancelar</a>
                                            <a class="btn btn-small btn-success" ng-click="delete(notification.id)">ok</a>
                                        </span>
                                        <span ng-if="notification.request.requesterUser.id !== MapasCulturais.userId">
                                            <a class="btn btn-small btn-danger" ng-click="reject(notification.id)">rechazar</a>
                                        </span>
                                    </span>

                                    <span ng-if="!notification.isRequest">
                                        <a class="btn btn-small btn-success" ng-click="delete(notification.id)">ok</a>
                                    </span>

                                </p>
                            </li>
                        </ul>
                        <a href="<?php echo $app->createUrl('panel'); ?>">
                            Ver todas las actividades
                        </a>
                    </li>
                </ul>
                <!--.submenu-->
            </li>
            <!--.notifications-->
            <?php $this->applyTemplateHook('nav.main.notifications','after'); ?>
            
            <?php $this->applyTemplateHook('nav.main.user','before'); ?>
            <li class="user">
                <a href="#" class="js-submenu-toggle" data-submenu-target="$(this).parent().find('.submenu')">
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
                        <a href="<?php echo $app->createUrl('panel'); ?>">Panel</a>
                    </li>
                    <?php if($app->isEnabled('events')): ?>
                        <?php $this->applyTemplateHook('nav.dropdown.events','before'); ?>
                        <li>
                            <a href="<?php echo $app->createUrl('panel', 'events') ?>">Mis Eventos</a>
                            <a class="add" href="<?php echo $app->createUrl('event', 'create') ?>" ></a>
                        </li>
                        <?php $this->applyTemplateHook('nav.dropdown.events','after'); ?>
                    <?php endif; ?>
                        
                    <?php if($app->isEnabled('agents')): ?>
                        <?php $this->applyTemplateHook('nav.dropdown.agents','before'); ?>
                        <li>
                            <a href="<?php echo $app->createUrl('panel', 'agents') ?>">Mis Agentes</a>
                            <a class="add" href="<?php echo $app->createUrl('agent', 'create') ?>"></a>
                        </li>
                        <?php $this->applyTemplateHook('nav.dropdown.agents','after'); ?>
                    <?php endif; ?>
                        
                    <?php if($app->isEnabled('spaces')): ?>
                        <?php $this->applyTemplateHook('nav.dropdown.spaces','before'); ?>
                        <li>
                            <a href="<?php echo $app->createUrl('panel', 'spaces') ?>"><?php $this->dict('entities: My Spaces') ?></a>
                            <a class="add"href="<?php echo $app->createUrl('space', 'create') ?>"></a>
                        </li>
                        <?php $this->applyTemplateHook('nav.dropdown.spaces','after'); ?>
                    <?php endif; ?>
                        
                    <?php if($app->isEnabled('projects')): ?>
                        <?php $this->applyTemplateHook('nav.dropdown.projects','before'); ?>
                        <li>
                            <a href="<?php echo $app->createUrl('panel', 'projects') ?>">Mis Proyectos</a>
                            <a class="add" href="<?php echo $app->createUrl('project', 'create') ?>"></a>
                        </li>
                        <?php $this->applyTemplateHook('nav.dropdown.projects','after'); ?>
                        
                        <?php $this->applyTemplateHook('nav.dropdown.registrations','before'); ?>
                        <li>
                            <a href="<?php echo $app->createUrl('panel', 'registrations') ?>">Mis Inscripciones</a>
                        </li>
                        <?php $this->applyTemplateHook('nav.dropdown.registrations','after'); ?>
                    <?php endif; ?>
                        
                    <li class="row"></li>
                    <!--<li><a href="#">Ayuda</a></li>-->
                    <li>
                        <?php if($app->getConfig('auth.provider') === 'Fake'): ?>
                            <a href="<?php echo $app->createUrl('auth'); ?>">Cambiar Usuario</a>
                            <?php if(!empty($fake_options)) echo $fake_options; ?>
                        <?php endif; ?>
                        <a href="<?php echo $app->createUrl('auth', 'logout'); ?>">Salir</a>
                    </li>
                </ul>
            </li>
            <!--.user-->
            <?php $this->applyTemplateHook('nav.main.user','after'); ?>
        <?php else: ?>
            <?php $this->applyTemplateHook('nav.main.login','before'); ?>
            <li class="login">
                <a href="<?php echo $app->createUrl('panel') ?>">
                    <div class="icon icon-login"></div>
                    <div class="menu-item-label">Entrar</div>
                </a>
                
            </li>
            <!--.login-->
            <?php $this->applyTemplateHook('nav.main.login','after'); ?>
        <?php endif; ?>
    </ul>
    <!--.menu.session-menu-->
</nav>
