<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Meus selos</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('seal', 'create'); ?>">Adicionar novo selo</a>
	</header>
    <ul class="abas clearfix clear">
<<<<<<< HEAD
        <li class="active"><a href="#ativos">Ativos (<?php echo count($app->user->enabledSeals);?>)</a></li>
		<li><a href="#permitido">Concedidos (<?php echo count($app->user->hasControlSeals);?>)</a></li>
        <li><a href="#rascunhos">Rascunhos (<?php echo count($app->user->draftSeals);?>)</a></li>
        <li><a href="#lixeira">Lixeira (<?php echo count($app->user->trashedSeals);?>)</a></li>
=======
        <li class="active"><a href="#ativos">Ativos</a></li>
		<li><a href="#permitido">Concedidos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
>>>>>>> rc
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledSeals as $entity): if($app->user->profile->equals($entity)) continue;?>
            <?php $this->part('panel-seal', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($app->user->draftSeals as $entity): ?>
            <?php $this->part('panel-seal', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftSeals): ?>
            <div class="alert info">Você não possui nenhum rascunho selo.</div>
        <?php endif;?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($app->user->trashedSeals as $entity): ?>
            <?php $this->part('panel-seal', array('entity' => $entity));?>
        <?php endforeach; ?>
        <?php if(!$user->trashedSeals):  ?>
            <div class="alert info">Você não possui nenhum selo na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlSeals as $entity): ?>
			<?php $this->part('panel-seal', array('entity' => $entity)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlSeals): ?>
			<div class="alert info">Você não possui nenhum selo liberado.</div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
