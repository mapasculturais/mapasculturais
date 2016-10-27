<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Meus eventos</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('event', 'create'); ?>">Adicionar novo evento</a>
	</header>
    <ul class="abas clearfix clear">
<<<<<<< HEAD
        <li class="active"><a href="#ativos">Ativos (<?php echo count($enabled); ?>)</a></li>
		<li><a href="#permitido">Concedidos (<?php echo count($app->user->hasControlEvents);?>)</a></li>
		<li><a href="#rascunhos">Rascunhos (<?php echo count($draft); ?>)</a></li>
        <li><a href="#lixeira">Lixeira (<?php echo count($trashed); ?>)</a></li>
=======
        <li class="active"><a href="#ativos">Ativos</a></li>
		<li><a href="#permitido">Concedidos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
>>>>>>> rc
    </ul>
    <div id="ativos">

        <?php $this->part('panel-search', ['meta' => $meta, 'search_entity' => 'event']); ?>

        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info">Você não possui nenhum evento cadastrado.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info">Você não possui nenhum rascunho de evento.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info">Você não possui nenhum evento na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlEvents as $entity): ?>
			<?php $this->part('panel-event', array('entity' => $entity)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlEvents): ?>
			<div class="alert info">Você não possui nenhum evento liberado.</div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
