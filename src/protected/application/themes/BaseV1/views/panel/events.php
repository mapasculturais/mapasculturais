<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php \MapasCulturais\i::_e("Meus eventos"); ?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('event', 'create'); ?>"><?php \MapasCulturais\i::_e('Adicionar novo evento'); ?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos"><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo count($enabled); ?>)</a></li>
		<li><a href="#permitido"><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($app->user->hasControlEvents);?>)</a></li>
		<li><a href="#rascunhos"><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($draft); ?>)</a></li>
        <li><a href="#lixeira"><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($trashed); ?>)</a></li>
		<li><a href="#arquivo"><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($app->user->archivedEvents);?>)</a></li>
    </ul>
    <div id="ativos">

        <?php $this->part('panel-search', ['meta' => $meta, 'search_entity' => 'event']); ?>

        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento cadastrado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de evento.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlEvents as $entity): ?>
			<?php $this->part('panel-event', array('entity' => $entity)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlEvents): ?>
			<div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento liberado.");?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
	<!-- #arquivo-->
    <div id="arquivo">
		<?php foreach($app->user->archivedEvents as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->archivedEvents): ?>
            <div class="alert info">Você não possui nenhum <?php $this->dict('entities: no events') ?> arquivado.</div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
</div>
