<article class="objeto clearfix">
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
    <div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
        <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
        <?php $linguagem = isset($entity->terms->linguagem)?$entity->terms->linguagem: $entity->terms['linguagem'];?>
		<div><span class="label"><?php \MapasCulturais\i::_e("Linguagens: ");?></span> <?php echo implode(', ', $linguagem)?></div>
		<?php if($entity->classificacaoEtaria): ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Classificação:");?></span> <?php echo $entity->classificacaoEtaria; ?></div>
                <?php endif; ?>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>

        <?php if($entity->status === \MapasCulturais\Entities\Event::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->unpublishUrl; ?>"><?php \MapasCulturais\i::_e("tornar rascunho");?></a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
            <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Event::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Event::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>
        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
