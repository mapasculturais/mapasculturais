<?php
$this->layout = 'panel';
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php $this->dict('entities: My spaces') ?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('space', 'create'); ?>">Adicionar <?php $this->dict('entities: new space') ?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info">Você não possui <?php $this->dict('entities: no registered spaces') ?>.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info">Você não possui nenhum rascunho de <?php $this->dict('entities: space') ?>.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info">Você não possui <?php $this->dict('entities: no spaces') ?> na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
