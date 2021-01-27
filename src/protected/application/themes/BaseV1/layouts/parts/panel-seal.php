<?php
use MapasCulturais\Entities\Seal;
use MapasCulturais\i;

?>
<article class="objeto clearfix">
    <h1>
        <a class="icon icon-seal" rel='noopener noreferrer'></a>
        <a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a>
    </h1>
    <div class="objeto-meta">
        <?php if(isset($entity->originSiteUrl)): ?>
            <div><span class="label">Url: </span> <?php echo $entity->originSiteUrl;?></div>
        <?php endif; ?>
        <?php if(!$entity->ownerUser->equals($app->user)): ?>
            <?php i::_e("Publicado por") ?>: <a href="<?= $entity->owner->singleUrl ?>"><?php echo $entity->owner->name ?></a>
        <?php endif; ?>
    </div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>
        <?php if(!isset($only_edit_button)): ?>
            <?php if($entity->status === Seal::STATUS_ENABLED): ?>
                <?php if($entity->canUser('remove')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                <?php endif; ?>
                <?php if($entity->canUser('archive')): ?>
                    <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>
                <?php endif; ?>

            <?php elseif ($entity->status === Seal::STATUS_DRAFT): ?>
                <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                <?php if($entity->canUser('remove')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                <?php endif; ?>
            <?php elseif ($entity->status === \MapasCulturais\Entities\Seal::STATUS_ARCHIVED): ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

            <?php elseif ($entity->status === \MapasCulturais\Entities\Subsite::STATUS_ARCHIVED): ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

            <?php else: ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
