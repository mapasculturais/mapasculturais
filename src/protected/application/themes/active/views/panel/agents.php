<?php
$this->layout = 'panel'
?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus agentes</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('agent', 'create'); ?>">Adicionar novo agente</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #ativos-->
    <div id="lixeira">
        <?php foreach($app->user->trashedAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #lixeira-->
</div>
