<?php
$this->layout = 'panel';

if (!($app->user->is('superAdmin') || $app->user->is('admin'))) {
	//$e = new Exceptions\TemplateNotFound("Template $__template_filename not found");
	return;
}
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php echo $this->dict('entities: My Subsites');?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('subsite', 'create') ?>"><?php echo $this->dict('entities: add new subsite');?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos"><?php \MapasCulturais\i::_e('Ativos') ?></a></li>
        <li><a href="#rascunhos"><?php \MapasCulturais\i::_e('Rascunhos') ?></a></li>
        <li><a href="#lixeira"><?php \MapasCulturais\i::_e('Lixeira') ?></a></li>
		<li><a href="#arquivo"><?php \MapasCulturais\i::_e('Arquivo') ?></a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum Subsite.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum rascunho de subsite.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum subsite na lixeira.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($user->archivedSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->archivedSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum subsite arquivado.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
