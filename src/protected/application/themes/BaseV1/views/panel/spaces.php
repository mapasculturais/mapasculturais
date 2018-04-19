<?php
$this->layout = 'panel';
$app = \MapasCulturais\App::i();
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php $this->dict('entities: My spaces') ?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('space', 'create'); ?>">Adicionar <?php $this->dict('entities: new space') ?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos"><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo $meta->count; ?>)</a></li>
        <li><a href="#permitido"><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($app->user->hasControlSpaces);?>)</a></li>
        <li><a href="#rascunhos"><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($draft); ?>)</a></li>
        <li><a href="#lixeira"><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($trashed); ?>)</a></li>
        <li><a href="#arquivo"><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($app->user->archivedSpaces); ?>)</a></li>
    </ul>
    <div id="ativos">

        <?php $this->part('panel-search', ['meta' => $meta, 'search_entity' => 'space']); ?>

        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info"><?php $this->dict('entities: no registered spaces') ?>.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__("Você não possui nenhum rascunho de %s"), $this->dict('entities: space'));?>.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__('%s na lixeira.'), $this->dict('entities: no spaces')); ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
		<?php foreach($app->user->archivedSpaces as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->archivedSpaces): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__('%s arquivado.'), $this->dict('entities: no spaces')); ?></div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlSpaces as $entity): ?>
			<?php $this->part('panel-space', array('entity' => $entity, 'only_edit_button' => true)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlSpaces): ?>
			<div class="alert info"><?php printf(\MapasCulturais\i::__('%s liberado.'), $this->dict('entities: no spaces')); ?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
