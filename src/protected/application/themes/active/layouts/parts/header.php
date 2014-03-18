<!DOCTYPE html>
<html lang="pt-BR" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>SP Cultura -
        <?php echo isset($entity) ? $entity->getTitle() :
            $app->getReadableName($this->controller->id) . ' - '.
            $app->getReadableName($this->controller->action)
        ; ?>
    </title>
 <link rel="profile" href="http://gmpg.org/xfn/11" />
    <script type="text/javascript">
        var MapasCulturais = {
            baseURL: '<?php echo $baseURL?>',
            assetURL: '<?php echo $assetURL?>',
            request: {
                controller: '<?php if($this->controller) echo $this->controller->id?>',
                action: '<?php if($this->controller) echo str_replace($this->controller->id . '/', '', $this->template)?>',
                id: <?php echo (isset($entity) && $entity->id) ? $entity->id : 'null'; ?>,
            },
            mode: "<?php echo $app->config('mode'); ?>"
        };
    </script>
    <?php
    $app->printStyles('vendor');
	$app->printStyles('fonts');
    $app->printStyles('app');
    $app->printScripts('vendor');
    $app->printScripts('app');

    $app->applyHook('mapasculturais.scripts');
    ?>
    <!--[if lt IE 9]>
    <script src="<?php echo $assetURL?>/js/html5.js" type="text/javascript"></script>
    <![endif]-->
    <style>

		/* Styling for the ngProgress itself */
		#ngProgress {
		    margin: 0;
		    padding: 10 0;
		    z-index: 99998;
		    background-color: white;
		    color: red;
		    box-shadow: 0 0 10px 0; /* Inherits the font color */
		    height: 5px;
		    opacity: 0;

		    /* Add CSS3 styles for transition smoothing */
		    -webkit-transition: all 0.2s ease-in-out;
		    -moz-transition: all 0.2s ease-in-out;
		    -o-transition: all 0.2s ease-in-out;
		    transition: all 0.2s ease-in-out;
		}

		/* Styling for the ngProgress-container */
		#ngProgress-container {
		    position: fixed;
		    margin: 0;
		    padding: 0;
		    top: 0px;
		    left: 0;
		    right: 0;
		    z-index: 99999;
		}

    </style>
</head>
<body<?php if($this->controller->action == 'search') echo ' ng-app="search" ng-controller="SearchController"';?> class="<?php
    if($this->controller) echo "controller-{$this->controller->id} action-{$this->controller->action} ";
    if(isset($entity)) echo 'entity ';
?>">
<?php if($this->controller && ($this->controller->action == 'single' || $this->controller->action == 'edit' )):?>
	<!--sham-spinner text="Loading..."></sham-spinner-->

<!--facebook compartilhar-->
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
<!--fim do facebook-->
<?php endif; ?>
<?php $app->applyHook('mapasculturais.body:before'); ?>
<header id="main-header" class="clearfix"  ng-class="{'sombra':data.global.viewMode!=='list'}">
    <h1 id="logo-spcultura"><a href="<?php echo $app->getBaseUrl() ?>"><img src="<?php echo $assetURL?>/img/logo-spcultura.png" /></a></h1>
    <nav id="about-nav" class="alignright clearfix">
        <ul id="menu-secundario">
            <li><a href="#">Sobre o SP Cultura</a></li>
            <li><a href="#">Como usar</a></li>
        </ul>
        <h1 id="logo-smc"><a href="http://www.prefeitura.sp.gov.br" target="_blank"><img src="<?php echo $assetURL?>/img/logo-prefeitura.png" /></a></h1>
    </nav>
	<nav id="main-nav" class="alignright clearfix">
		<ul class="menu abas-objetos clearfix">
			<li id="aba-eventos" ng-class="{'active':data.global.filterEntity==='event','disabled':!data.global.enabled.event,'enabled':data.global.enabled.event}" ng-mouseenter="tabOver('event')" ng-click="tabClick('event')">
                <a href="<?php if($this->controller->action !== 'search') echo $app->createUrl('busca').'##(global:(enabled:(event:!t),filterEntity:event))'; ?>">
                	<div class="icone icon_calendar"></div>
                	<div>Eventos</div>
                </a>
            </li>
			<li id="aba-agentes" ng-class="{'active':data.global.filterEntity==='agent','disabled':!data.global.enabled.agent,'enabled':data.global.enabled.agent}" ng-mouseenter="tabOver('agent')" ng-click="tabClick('agent')">
                <a href="<?php if($this->controller->action !== 'search') echo $app->createUrl('busca').'##(global:(enabled:(agent:!t),filterEntity:agent))'; ?>">
	                <div class="icone icon_profile"></div>
	                <div>Agentes</div>
                </a>
            </li>
			<li id="aba-espacos" ng-class="{'active':data.global.filterEntity==='space','disabled':!data.global.enabled.space,'enabled':data.global.enabled.space}" ng-mouseenter="tabOver('space')" ng-click="tabClick('space')">
                <a href="<?php if($this->controller->action !== 'search') echo $app->createUrl('busca').'##(global:(enabled:(space:!t),filterEntity:space))'; ?>">
                	<div class="icone icon_building"></div>
                	<div>Espaços</div>
                </a>
            </li>
        </ul>
        <!--.menu.abas-objetos-->
        <ul class="menu abas-objetos clearfix staging-hidden">
			<li id="aba-projetos"> <!--se estiver na busca de projetos adicionar nessa li class="active enabled"-->
				<a href="<?php echo $app->createUrl('project'); ?>">
					<div class="icone icon_document_alt"></div>
					<div>Projetos</div>
				</a>
			</li>
        </ul>
        <!--.menu.abas-objetos-->
        <ul class="menu logado clearfix">
            <?php if($app->user): ?>
				<li class="notificacoes">
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
                            <?php if($app->user->profile->avatar): ?>
							<img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                            <?php else: ?>
                            <img src="<?php echo $app->assetUrl; ?>/img/avatar-padrao.png" />
                            <?php endif; ?>
						</div>
					</a>
					<ul class="submenu">
						<div class="setinha"></div>
						<li><a href="<?php echo $app->createUrl('panel') ?>">Painel</a></li>
						<li><a href="#">Ajuda</a></li>
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
<?php if(is_editable()): ?>
    <div id="ajax-response-errors" class="js-dialog" title="Corrija os erros abaixo e tente novamente.">
        <div class="js-dialog-content"></div>
    </div>
<?php endif; ?>
