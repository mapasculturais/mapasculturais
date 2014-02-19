<?php $this->part('panel/part-nav.php')?>
<div class="lista-sem-thumb main-content">
	<header class="header-do-painel clearfix">
		<h2 class="alignleft">Meus espaços</h2>
		<a class="botao adicionar" href="<?php echo $app->createUrl('space', 'create'); ?>">Adicionar novo espaço</a>
	</header>
    <?php foreach($entityList as $entity): ?>
    <article class="objeto clearfix">
        <?php if($avatar = $entity->avatar): ?>
            <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
        <?php else: ?>
            <div class="thumb"></div>
        <?php endif; ?>
        <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
		<div class="objeto-meta">
			<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
			<div><span class="label">Área de atuação:</span> Cinema</div>
			<div><span class="label">Local:</span> <?php echo $entity->endereco?></div>
			<div><span class="label">Acessibilidade:</span> Sim</div>			
		</div>
        <div>
            <a class="action" href="<?php echo $entity->editUrl; ?>">editar</a>
            <a class="action" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>
