<?php $this->part('panel/part-nav.php')?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus eventos</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('event', 'create'); ?>">Adicionar novo evento</a>
	</header>
    <?php foreach($entityList as $entity): ?>
    <article class="objeto clearfix">
        <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
        <div class="objeto-meta">
			<div><span class="label">Linguagem:</span> <?php echo implode(',', $entity->terms['linguagem'])?></div>
			<div><span class="label">Horário:</span> <time>00h00</time></div>
			<div><span class="label">Local:</span> Teatro</div>
			<div><span class="label">Classificação:</span> Livre</div>
		</div>
        <div>
            <a class="action" href="<?php echo $entity->editUrl; ?>">editar</a>
            <a class="action" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>
