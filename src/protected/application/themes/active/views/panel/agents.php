<?php
$this->part('panel/part-nav.php');
?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus agentes</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('agent', 'create'); ?>">Adicionar novo agente</a>
	</header>
    <ul class="abas clearfix clear" style="display: block; ">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#inativos">Inativos</a></li>
        <li><a href="#relacionados">Relacionados</a></li>
        <li><a href="#convidados">Convidados</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($app->user->enabledAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <div id="inativos">
        <?php foreach($app->user->disabledAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <div id="relacionados">
        <?php foreach($app->user->relatedAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <div id="convidados">
        <?php foreach($app->user->invitedAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <div id="lixeira">
        <?php foreach($app->user->trashedAgents as $entity): ?>
            <?php $this->part('panel/part-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
</div>
