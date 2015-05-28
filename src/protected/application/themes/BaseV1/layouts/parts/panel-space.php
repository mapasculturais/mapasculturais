<article class="objeto clearfix">
    <?php if($avatar = $entity->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
		<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
		<div><span class="label">Área(s) de atuação:</span> <?php echo implode(', ', $entity->terms['area'])?></div>
		<div><span class="label">Local:</span> <?php echo $entity->endereco?></div>
		<div><span class="label">Acessibilidade:</span> <?php echo $entity->acessibilidade ? $entity->acessibilidade : 'Não informado' ?></div>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Space::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Space::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>">publicar</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
            <?php if($entity->canUser('destroy')): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>">excluir definitivamente</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>