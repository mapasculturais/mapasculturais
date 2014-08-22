<?php
$this->layout = 'panel'
?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus projetos</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('project', 'create') ?>">Adicionar novo projeto</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledProjects as $entity): ?>
            <?php $this->part('panel/part-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #ativos-->
    <div id="lixeira">
        <?php foreach($user->trashedProjects as $entity): ?>
            <?php $this->part('panel/part-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #lixeira-->
</div>
