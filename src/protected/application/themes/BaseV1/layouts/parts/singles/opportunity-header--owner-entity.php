<?php $entity_owner_type = $entity->ownerEntity->controller->id; ?>
<h5 class="entity-parent-title">
    <a href="<?php echo $entity->ownerEntity->singleUrl; ?>" class="color-<?= $entity_owner_type ?>">
        <div class="icon icon-<?php echo $entity_owner_type ?>"></div>
        <?php echo $entity->ownerEntity->name; ?>
    </a>
</h5>