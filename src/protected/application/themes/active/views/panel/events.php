<?php
$this->layout = 'panel'
?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus eventos</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('event', 'create'); ?>">Adicionar novo evento</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledEvents as $entity): ?>
            <?php $this->part('panel/part-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #ativos-->
    <div id="lixeira">
        <?php foreach($user->trashedEvents as $entity): ?>
            <?php $this->part('panel/part-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
</div>
