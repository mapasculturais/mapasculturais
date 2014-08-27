<?php $title = isset($entity) ? $this->getTitle($entity) : $this->getTitle() ?>
<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">
    <head>
        <meta charset="UTF-8" />
        <title><?php echo $title == $app->siteName ? $title : "{$app->siteName} - {$title}"; ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <?php mapasculturais_head(isset($entity) ? $entity : null); ?>
        <!--[if lt IE 9]>
        <script src="<?php echo $assetURL ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
    </head>

    <body <?php body_properties() ?> >
       <?php body_header(); ?>
        <header id="main-header" class="clearfix"  ng-class="{'sombra':data.global.viewMode !== 'list'}">
            <h1 id="logo-spcultura"><a href="<?php echo $app->getBaseUrl() ?>"><img src="<?php echo $assetURL ?>/img/logo-spcultura.png" /></a></h1>
            <nav id="about-nav" class="alignright clearfix">
                <ul id="menu-secundario">
                    <li><a href="<?php echo $app->createUrl('site', 'page', array('sobre')) ?>">Sobre o SP Cultura</a></li>
                    <li><a href="<?php echo $app->createUrl('site', 'page', array('como-usar')) ?>">Como usar</a></li>
                </ul>
                <h1 id="logo-smc"><a href="http://www.prefeitura.sp.gov.br" target="_blank"><img src="<?php echo $assetURL ?>/img/logo-prefeitura.png" /></a></h1>
            </nav>
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
                        <li class="notificacoes staging-hidden">
                            <a href="#">
                                <div class="icone icon_comment"></div>
                                <div>Notificações</div>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <div class="setinha"></div>
                                    <div class="clearfix">
                                        <h6 class="alignleft">Notificações</h6>
                                        <a href="#" class="hltip icone icon_check_alt" title="Marcar todas como lidas"></a>
                                    </div>
                                    <ul>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano quer adicionar um evento em seu espaço.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao lida clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="notificacao clearfix">
                                                Fulano aprovou seu evento no teatro.<br />
                                                <span class="small">Há 00min.</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <a href="#">
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
                                        <img src="<?php echo $app->assetUrl; ?>/img/avatar.png" />
                                    <?php endif; ?>
                                </div>
                            </a>
                            <ul class="submenu">
                                <div class="setinha"></div>
                                <li>
                                    <a href="<?php echo $app->createUrl('panel');?>">Painel</a>
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
                                <li><a href="<?php echo $app->createUrl('auth', 'logout') ?>">Sair</a></li>
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
        </header>
        <section id="main-section" class="clearfix">
            <?php if (is_editable()): ?>
                <div id="ajax-response-errors" class="js-dialog" title="Corrija os erros abaixo e tente novamente.">
                    <div class="js-dialog-content"></div>
                </div>
            <?php endif; ?>
