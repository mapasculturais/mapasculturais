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
                <?php if($entity->type): ?>
		<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
                <?php endif; ?>
        <div><span class="label">URL:</span> <a href="<?php echo "http://" . $entity->url;?>"><?php echo "http://" . $entity->url;?></a></div>
        </br>
		<div><span class="label">Data de Criação:</span> <?php echo $entity->createTimestamp->format('d/m/Y H:i:s');?></div>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Subsite::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
            <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>">arquivar</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Subsite::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>">publicar</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Subsite::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>">desarquivar</a>

        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
            <?php if($entity->canUser('destroy')): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>">excluir definitivamente</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
