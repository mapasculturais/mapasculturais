<article class="objeto clearfix">
    <?php if($avatar = $entity->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
		<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
		<div><span class="label">Inscrições:</span> 00/00/00</div>
		<div><span class="label">Organização:</span> Nome do owner</div>			
	</div>
    <div>
        <a class="action" href="<?php echo $entity->editUrl; ?>">editar</a>
        <a class="action" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
    </div>
</article>