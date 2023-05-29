<?php
$this->addRequestedEntityToJs($entity ? $entity->className : null, $entity ? $entity->id : null);
if ($entity->usesTypes()) {
    $this->bodyClasses[] = "{$entity->entityType}-{$entity->type->id}";
} else {
    $this->bodyClasses[] = "{$entity->entityType}";
}
?>
<?php $this->part('header', $render_data) ?>
<?php $this->part('main-header', $render_data) ?>
<mc-entity #default="{entity}">
<?= $TEMPLATE_CONTENT ?>
</mc-entity>
<?php $this->part('main-footer', $render_data) ?>
<?php $this->part('footer', $render_data); 