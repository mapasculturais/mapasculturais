<?php if($app->getConfig('auth.provider') === 'Fake' && $app->user->id !== 1): ob_start(); ?>
    <style>
        span.fake-dummy{
            white-space:nowrap; padding: 0.5rem 0 0 0.5rem; cursor:default;
        }
        span.fake-dummy a{
            display:inline !important; font-weight:bold !important; vertical-align: baseline !important;
        }
    </style>
    <span class="fake-dummy">
        Admin:
        <a onclick="jQuery.get('<?php echo $app->createUrl('auth', 'fakeLogin') ?>/?fake_authentication_user_id=1',
            function(){
                console.info(<?php \MapasCulturais\i::_e('Logado como Admin');?>);
                MapasCulturais.Messages.success(<?php \MapasCulturais\i::_e('Logado como Admin.');?>);
            })">
            Login
        </a>
        <a onclick="jQuery.get('<?php echo $app->createUrl('auth', 'fakeLogin') ?>/?fake_authentication_user_id=1',
            function(){ location.reload();})">
            Reload
        </a>
    </span>
<?php $fake_options = ob_get_clean(); endif; ?>

<nav id="main-nav" class="clearfix">
    <ul class="menu entities-menu clearfix">
        <?php if($app->isEnabled('events')): ?>
            <?php $this->applyTemplateHook('nav.main.events','before'); ?>
            <li id="entities-menu-event"
                ng-class="{'active':data.global.filterEntity === 'event',
                           'current-entity-parent':'<?php echo $this->controller->id;?>' == 'event'}"
                ng-click="tabClick('event')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('site', 'search') . '##(global:(enabled:(event:!t),filterEntity:event))'; ?>">
                    <div class="icon icon-event"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Events') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.events','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('spaces')): ?>
            <?php $this->applyTemplateHook('nav.main.spaces','before'); ?>
            <li id="entities-menu-space"
                ng-class="{'active':data.global.filterEntity === 'space',
                           'current-entity-parent':'<?php echo $this->controller->id;?>' == 'space'}"
                ng-click="tabClick('space')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('site', 'search') . '##(global:(enabled:(space:!t),filterEntity:space))'; ?>">
                    <div class="icon icon-space"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Spaces') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.spaces','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('agents')): ?>
            <?php $this->applyTemplateHook('nav.main.agents','before'); ?>
            <li id="entities-menu-agent"
                ng-class="{'active':data.global.filterEntity === 'agent',
                           'current-entity-parent':'<?php echo $this->controller->id;?>' == 'agent'}"
                ng-click="tabClick('agent')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('site', 'search') . '##(global:(enabled:(agent:!t),filterEntity:agent))'; ?>">
                    <div class="icon icon-agent"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Agents') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.agents','after'); ?>
        <?php endif; ?>

        <?php if($app->isEnabled('projects')): ?>
            <?php $this->applyTemplateHook('nav.main.projects','before'); ?>
            <li id="entities-menu-project"
                ng-class="{'active':data.global.filterEntity === 'project',
                           'current-entity-parent':'<?php echo $this->controller->id;?>' == 'project'}"
                ng-click="tabClick('project')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('site', 'search') . '##(global:(enabled:(project:!t),filterEntity:project,viewMode:list))'; ?>">
                    <div class="icon icon-project"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Projects') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.projects','after'); ?>
        <?php endif; ?>
            
        <?php if($app->isEnabled('opportunities')): ?>
            <?php $this->applyTemplateHook('nav.main.opportunities','before'); ?>
            <li id="entities-menu-opportunity"
                ng-class="{'active':data.global.filterEntity === 'opportunity',
                           'current-entity-parent':'<?php echo $this->controller->id;?>' == 'opportunity'}"
                ng-click="tabClick('opportunity')">
                <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('site', 'search') . '##(global:(enabled:(opportunity:!t),filterEntity:opportunity,viewMode:list))'; ?>">
                    <div class="icon icon-opportunity"></div>
                    <div class="menu-item-label"><?php $this->dict('entities: Opportunities') ?></div>
                </a>
            </li>
            <?php $this->applyTemplateHook('nav.main.opportunities','after'); ?>
        <?php endif; ?>

    </ul>
    <!--.menu.entities-menu-->
    <ul class="menu session-menu clearfix">
        <?php if ($app->auth->isUserAuthenticated()): ?>
            <?php $this->applyTemplateHook('nav.main.notifications','before'); ?>
            <li class="notifications" ng-controller="NotificationController" style="display:none" ng-class="{'visible': data.length > 0}">
                <a class="js-submenu-toggle" data-submenu-target="$(this).parent().find('.submenu')" rel='noopener noreferrer'>
                    <div class="icon icon-notifications"></div>
                    <div class="menu-item-label"><?php \MapasCulturais\i::_e("Notificações");?></div>
                </a>
                <ul class="submenu hidden">
                    <li>
                        <div class="clearfix">
                            <h6 class="alignleft"><?php \MapasCulturais\i::_e("Notificações");?></h6>
                            <a href="#" style="display:none" class="staging-hidden hltip icon icon-check_alt" title="<?php \MapasCulturais\i::esc_attr_e("Marcar todas como lidas");?>"></a>
                        </div>
                        <ul>
                            <li ng-repeat="notification in data" on-last-repeat="adjustScroll();">
                                <p class="notification clearfix">
                                    <span ng-bind-html="notification.message"></span>
                                    <br>

                                    <a ng-if="notification.request.permissionTo.approve" class="btn btn-small btn-success" ng-click="approve(notification.id)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("aceitar");?></a>

                                    <span ng-if="notification.request.permissionTo.reject">
                                        <span ng-if="notification.request.requesterUser === MapasCulturais.userId">
                                            <a class="btn btn-small btn-default" ng-click="reject(notification.id)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("cancelar");?></a>
                                            <a class="btn btn-small btn-success" ng-click="delete(notification.id)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("ok");?></a>
                                        </span>
                                        <span ng-if="notification.request.requesterUser !== MapasCulturais.userId">
                                            <a class="btn btn-small btn-danger" ng-click="reject(notification.id)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("rejeitar");?></a>
                                        </span>
                                    </span>

                                    <span ng-if="!notification.request">
                                        <a class="btn btn-small btn-success" ng-click="delete(notification.id)" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("ok");?></a>
                                    </span>

                                </p>
                            </li>
                        </ul>
                        <a href="<?php echo $app->createUrl('panel'); ?>">
                            <?php \MapasCulturais\i::_e("Ver todas atividades");?>
                        </a>
                    </li>
                </ul>
                <!--.submenu-->
            </li>
            <!--.notifications-->
            <?php $this->applyTemplateHook('nav.main.notifications','after'); ?>

            <?php $this->part('nav-main-user') ?>
            
        <?php else: ?>
            <?php $this->applyTemplateHook('nav.main.login','before'); ?>
            <li class="login">
                <a ng-click="setRedirectUrl()" <?php echo $this->getLoginLinkAttributes() ?> >
                    <div class="icon icon-login"></div>
                    <div class="menu-item-label"><?php \MapasCulturais\i::_e("Entrar");?></div>
                </a>
                <?php if(!empty($fake_options)): ?>
                    <ul class="submenu" style="margin: 2px 0 0 -12px"><li><?php echo str_ireplace("Login\n        </a>", 'Login</a> |', $fake_options) ?></li></ul>
                <?php endif; ?>
            </li>
            <!--.login-->
            <?php $this->applyTemplateHook('nav.main.login','after'); ?>
        <?php endif; ?>
    </ul>
    <!--.menu.session-menu-->
</nav>