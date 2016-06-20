<?php
use MapasCulturais\Entities\Seal;
?>
<article class="objeto clearfix">
    <h1>
        <a class="icon icon-seal hltip"></a>
        <a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a>
    </h1>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>
            <?php if($entity->status === Seal::STATUS_ENABLED): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

            <?php elseif ($entity->status === Seal::STATUS_DRAFT): ?>
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
