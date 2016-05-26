<article class="objeto clearfix">
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
    <div class="objeto-meta">

        <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
        <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
		<div><span class="label">Tipos de eventos:</span> <?php echo implode(', ', $entity->terms->linguagem)?></div>
		<!--div><span class="label">Horário:</span> <time>00h00</time></div-->
		<!--div><span class="label">Local:</span> Teatro</div-->
		<?php if($entity->classificacaoEtaria): ?>
                    <div><span class="label">Clasificación:</span> <?php echo $entity->classificacaoEtaria; ?></div>
                <?php endif; ?>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Event::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->unpublishUrl; ?>">cambiar a borrador</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">eliminar</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Event::STATUS_DRAFT): ?>
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