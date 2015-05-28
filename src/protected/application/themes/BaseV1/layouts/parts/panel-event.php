<article class="objeto clearfix">
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
    <div class="objeto-meta">
		<div><span class="label">Linguagens:</span> <?php echo implode(', ', $entity->terms['linguagem'])?></div>
		<!--div><span class="label">Horário:</span> <time>00h00</time></div-->
		<!--div><span class="label">Local:</span> Teatro</div-->
		<?php if($entity->classificacaoEtaria): ?>
                    <div><span class="label">Classificação:</span> <?php echo $entity->classificacaoEtaria; ?></div>
                <?php endif; ?>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Event::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->unpublishUrl; ?>">tornar rascunho</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Event::STATUS_DRAFT): ?>
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