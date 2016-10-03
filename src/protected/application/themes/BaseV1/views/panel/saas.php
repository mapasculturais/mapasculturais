<?php
$this->layout = 'panel';

if (!($app->user->is('superAdmin') || $app->user->is('admin'))) {
	$e = new Exceptions\TemplateNotFound("Template $__template_filename not found");
}
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php echo $this->dict('entities: My SaaS');?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('saas', 'create') ?>"><?php echo $this->dict('entities: add new saas');?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
		<li><a href="#arquivo">Arquivo</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledSaaS): ?>
            <div class="alert info">Você não possui nenhum SaaS.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftSaaS): ?>
            <div class="alert info">Você não possui nenhum rascunho de saas.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedSaaS): ?>
            <div class="alert info">Você não possui nenhum saas na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($user->archivedSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->archivedSaaS): ?>
            <div class="alert info">Você não possui nenhum saas arquivado.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
