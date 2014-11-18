<nav id="main-nav" class="alignright clearfix">
    <ul class="menu abas-objetos clearfix">
        <li id="aba-eventos" ng-class="{'active':data.global.filterEntity === 'event'}" ng-click="tabClick('event')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(event:!t),filterEntity:event))'; ?>">
                <div class="icone icon_calendar"></div>
                <div>Eventos</div>
            </a>
        </li>
        <li id="aba-espacos" ng-class="{'active':data.global.filterEntity === 'space'}" ng-click="tabClick('space')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(space:!t),filterEntity:space))'; ?>">
                <div class="icone icon_building"></div>
                <div>Espaços</div>
            </a>
        </li>
        <li id="aba-agentes" ng-class="{'active':data.global.filterEntity === 'agent'}" ng-click="tabClick('agent')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(agent:!t),filterEntity:agent))'; ?>">
                <div class="icone icon_profile"></div>
                <div>Agentes</div>
            </a>
        </li>
        <li id="aba-projetos"  ng-class="{'active':data.global.filterEntity === 'project'}" ng-click="tabClick('project')">
            <a href="<?php if ($this->controller->action !== 'search') echo $app->createUrl('busca') . '##(global:(enabled:(project:!t),filterEntity:project,viewMode:list))'; ?>">
                <div class="icone icon_document_alt"></div>
                <div>Projetos</div>
            </a>
        </li>
    </ul>
    <!--.menu.abas-objetos-->
    <ul class="menu logado clearfix">
        <?php if ($app->auth->isUserAuthenticated()): ?>

            <li class="notificacoes" ng-controller="NotificationController" ng-hide="data.length == 0">

                <a>
                    <div class="icone icon_comment"></div>
                    <div>Notificações</div>
                </a>
                <ul class="submenu">
                    <li>
                        <div class="setinha"></div>
                        <div class="clearfix">
                            <h6 class="alignleft">Notificações</h6>
                            <a href="#" style="display:none" class="staging-hidden hltip icone icon_check_alt" title="Marcar todas como lidas"></a>
                        </div>
                        <ul>
                            <li ng-repeat="notification in data" on-last-repeat="adjustScroll();">
                                <p class="notificacao clearfix">
                                    <span ng-bind-html="notification.message"></span>
                                    <br>

                                    <a ng-if="notification.request.permissionTo.approve" class="action" ng-click="approve(notification.id)">aceitar</a>

                                    <span ng-if="notification.request.permissionTo.reject">
                                        <span ng-if="notification.request.requesterUser.id === MapasCulturais.userId">
                                            <a class="action" ng-click="reject(notification.id)">cancelar</a>
                                            <a class="action" ng-click="delete(notification.id)">ok</a>
                                        </span>
                                        <span ng-if="notification.request.requesterUser.id !== MapasCulturais.userId">
                                            <a class="action" ng-click="reject(notification.id)">rejeitar</a>
                                        </span>
                                    </span>

                                    <span ng-if="!notification.isRequest">
                                        <a class="action" ng-click="delete(notification.id)">ok</a>
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
            <!--.notificacoes-->

            <li class="usuario">
                <a href="<?php echo $app->createUrl('panel'); ?>">
                    <div class="avatar">
                        <?php if ($app->user->profile->avatar): ?>
                            <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                        <?php else: ?>
                            <img src="<?php $this->asset('img/avatar.png'); ?>" />
                        <?php endif; ?>
                    </div>
                </a>
                <ul class="submenu">
                    <div class="setinha"></div>
                    <li>
                        <a href="<?php echo $app->createUrl('panel'); ?>">Painel</a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'events') ?>">Meus Eventos</a>
                        <a class="adicionar" href="<?php echo $app->createUrl('event', 'create') ?>" ></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'agents') ?>">Meus Agentes</a>
                        <a class="adicionar" href="<?php echo $app->createUrl('agent', 'create') ?>"></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'spaces') ?>">Meus Espaços</a>
                        <a class="adicionar"href="<?php echo $app->createUrl('space', 'create') ?>"></a>
                    </li>
                    <li>
                        <a href="<?php echo $app->createUrl('panel', 'projects') ?>">Meus Projetos</a>
                        <a class="adicionar" href="<?php echo $app->createUrl('project', 'create') ?>"></a>
                    </li>
                    <li class="row"></li>
                    <!--<li><a href="#">Ajuda</a></li>-->
                    <li>
                        <?php if($app->getConfig('auth.provider') === 'Fake'): ?>
                            <a href="<?php echo $app->createUrl('auth'); ?>">Trocar Usuário</a>
                        <?php endif; ?>
                        <a href="<?php echo $app->createUrl('auth', 'login'); ?>">Sair</a>
                    </li>
                </ul>
            </li>
            <!--.usuario-->
        <?php else: ?>
            <li class="entrar">
                <a href="<?php echo $app->createUrl('panel') ?>">
                    <div class="icone icon_lock"></div>
                    <div>Entrar</div>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <!--.menu.logado-->
</nav>