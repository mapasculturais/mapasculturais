<?php $this->part('panel/part-nav.php')?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus projetos</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('project', 'create') ?>">Adicionar novo projeto</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li class="staging-hidden"><a href="#lixeira">Lixeira</a></li>
    </ul>
	<?php foreach($entityList as $entity): ?>
        <?php $this->part('panel/part-project', array('entity' => $entity)); ?>
    <?php endforeach; ?>	
</div>
