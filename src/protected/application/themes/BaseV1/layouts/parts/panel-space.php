<article class="objeto clearfix">
    <?php if(isset($entity->{'@files:avatar.avatarSmall'}) && $avatar = $entity->{'@files:avatar.avatarSmall'}): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
                <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
                <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
		<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
		<div><span class="label">Área(s) de actuación:</span> <?php echo implode(', ', $entity->terms->area)?></div>
		<div><span class="label">Local:</span> <?php echo $entity->endereco?></div>
		<div><span class="label">Accesibilidad:</span> <?php echo $entity->acessibilidade ? $entity->acessibilidade : 'No informado' ?></div>
		<div><span class="label">Fecha de creación:</span> <?php echo (new DateTime($entity->createTimestamp->date))->format('d/m/Y H:i:s'); ?></div>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Space::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">eliminar</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Space::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>">publicar</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">eliminar</a>

        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
            <?php if($entity->canUser('destroy')): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>">eliminar definitivamente</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
