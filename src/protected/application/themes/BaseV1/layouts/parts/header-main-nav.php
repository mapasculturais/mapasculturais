<nav id="main-nav" class="clearfix">
    <ul class="menu entities-menu clearfix">
        <li id="entities-menu-event" ng-class="{'active':data.global.filterEntity === 'event'}" ng-click="tabClick('event')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(event:!t),filterEntity:event))'; ?>">
                <div class="icone icon_calendar"></div>
                <div class="menu-item-label">Eventos</div>
            </a>
        </li>
        <li id="entities-menu-space" ng-class="{'active':data.global.filterEntity === 'space'}" ng-click="tabClick('space')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(space:!t),filterEntity:space))'; ?>">
                <div class="icone icon_building"></div>
                <div class="menu-item-label">Espaços</div>
            </a>
        </li>
        <li id="entities-menu-agent" ng-class="{'active':data.global.filterEntity === 'agent'}" ng-click="tabClick('agent')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(agent:!t),filterEntity:agent))'; ?>">
                <div class="icone icon_profile"></div>
                <div class="menu-item-label">Agentes</div>
            </a>
        </li>
        <li id="entities-menu-project"  ng-class="{'active':data.global.filterEntity === 'project'}" ng-click="tabClick('project')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(project:!t),filterEntity:project,viewMode:list))'; ?>">
                <div class="icone icon_document_alt"></div>
                <div class="menu-item-label">Projetos</div>
            </a>
        </li>
    </ul>
    <!--.menu.entities-menu-->
    <ul class="menu session clearfix">
        <?php if ($app->auth->isUserAuthenticated()): ?>
            <li class="notifications" ng-controller="NotificationController" ng-hide="data.length == 0">

                <a ng-click="notificationsSubmenu = !notificationsSubmenu">
                    <div class="icone icon_comment"></div>
                    <div class="menu-item-label">Notificações</div>
                </a>
                <ul class="submenu" ng-show="notificationsSubmenu">
                    <li>
                        <div class="clearfix">
                            <h6 class="alignleft">Notificações</h6>
                            <a href="#" style="display:none" class="staging-hidden hltip icone icon_check_alt" title="Marcar todas como lidas"></a>
                        </div>
                        <ul>
                            <li ng-repeat="notification in data" on-last-repeat="adjustScroll();">
                                <p class="notification clearfix">
                                    <span ng-bind-html="notification.message"></span>
                                    <br>

                                    <a ng-if="notification.request.permissionTo.approve" class="btn btn-small btn-success" ng-click="approve(notification.id)">aceitar</a>

                                    <span ng-if="notification.request.permissionTo.reject">
                                        <span ng-if="notification.request.requesterUser.id === MapasCulturais.userId">
                                            <a class="btn btn-small btn-default" ng-click="reject(notification.id)">cancelar</a>
                                            <a class="btn btn-small btn-success" ng-click="delete(notification.id)">ok</a>
                                        </span>
                                        <span ng-if="notification.request.requesterUser.id !== MapasCulturais.userId">
                                            <a class="btn btn-small btn-danger" ng-click="reject(notification.id)">rejeitar</a>
                                        </span>
                                    </span>

                                    <span ng-if="!notification.isRequest">
                                        <a class="btn btn-small btn-success" ng-click="delete(notification.id)">ok</a>
                                    </span>

                                </p>
                            </li>
                        </ul>
                        <a href="<?php echo $app->createUrl('panel'); ?>">
                            Ver todas atividades
                        </a>
                    </li>
                </ul>
                <!--.submenu-->
            </li>
            <!--.notifications-->
            <li class="user">
                <a href="#" ng-click="userSubmenu = !userSubmenu">
                    <div class="avatar">
                        <?php if ($app->user->profile->avatar): ?>
                            <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                        <?php else: ?>
                            <img src="<?php $this->asset('img/avatar.png'); ?>" />
                        <?php endif; ?>
                    </div>
                </a>
                <ul class="submenu" ng-show="userSubmenu">
                    <li>
                        <a href="<?php echo $app->createUrl('panel'); ?>">Painel</a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'events') ?>">Meus Eventos</a>
                        <a class="add" href="<?php echo $app->createUrl('event', 'create') ?>" ></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'agents') ?>">Meus Agentes</a>
                        <a class="add" href="<?php echo $app->createUrl('agent', 'create') ?>"></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'spaces') ?>">Meus Espaços</a>
                        <a class="add"href="<?php echo $app->createUrl('space', 'create') ?>"></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'projects') ?>">Meus Projetos</a>
                        <a class="add" href="<?php echo $app->createUrl('project', 'create') ?>"></a>
                    </li>
                    <li class="row"></li>
                    <!--<li><a href="#">Ajuda</a></li>-->
                    <li>
                        <?php if($app->getConfig('auth.provider') === 'Fake'): ?>
                            <a href="<?php echo $app->createUrl('auth'); ?>">Trocar Usuário</a>
                        <?php endif; ?>
                        <a href="<?php echo $app->createUrl('auth', 'logout'); ?>">Sair</a>
                    </li>
                </ul>
            </li>
            <!--.user-->
        <?php else: ?>
            <li class="login">
                <a href="<?php echo $app->createUrl('panel') ?>">
                    <div class="icone icon_lock"></div>
                    <div class="menu-item-label">Entrar</div>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <!--.menu.logado-->
</nav>