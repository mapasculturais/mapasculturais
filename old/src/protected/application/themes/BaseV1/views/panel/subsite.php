<?php
use MapasCulturais\i;
$this->layout = 'panel';

if (!$app->user->is('admin')) {
	//$e = new Exceptions\TemplateNotFound("Template $__template_filename not found");
	return;
}
?>
<div class="panel-list panel-main-content">
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>

		<h2><?php echo $this->dict('entities: My Subsites');?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('subsite', 'create') ?>"><?php echo $this->dict('entities: add new subsite');?></a>

        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">
        <?php $this->part('tab', ['id' => 'ativos', 'label' => i::__("Ativos"), 'active' => true]) ?>
        <?php $this->part('tab', ['id' => 'rascunhos', 'label' => i::__("Rascunhos")]) ?>
        <?php $this->part('tab', ['id' => 'lixeira', 'label' => i::__("Lixeira")]) ?>
        <?php $this->part('tab', ['id' => 'arquivo', 'label' => i::__("Arquivo")]) ?>
    </ul>
    <div id="ativos">
        <?php foreach($app->user->enabledSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->enabledSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum Subsite.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($app->user->draftSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->draftSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum rascunho de subsite.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($app->user->trashedSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->trashedSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum subsite na lixeira.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($app->user->archivedSubsite as $entity): ?>
            <?php $this->part('panel-subsite', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->archivedSubsite): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e('Você não possui nenhum subsite arquivado.') ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
