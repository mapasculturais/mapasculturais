<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Meus eventos</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('event', 'create'); ?>">Adicionar novo evento</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledEvents as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledEvents): ?>
            <div class="alert info">Você não possui nenhum evento cadastrado.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftEvents as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftEvents): ?>
            <div class="alert info">Você não possui nenhum rascunho de evento.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedEvents as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedEvents): ?>
            <div class="alert info">Você não possui nenhum evento na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
