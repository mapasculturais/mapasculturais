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
        
        <?php if($entity->status === \MapasCulturais\Entities\Space::STATUS_ENABLED): ?>
            <a class="action" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
        <?php else: ?>
            <a class="action" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
        <?php endif; ?>
    </div>
</article>