<?php
$this->jsObject['requestedEntity'] = $relation;
if ($relation->usesTypes()) {
    $this->bodyClasses[] = "{$relation->entityType}-{$relation->type->id}";
} else {
    $this->bodyClasses[] = "{$relation->entityType}";
}
?>
<?php $this->part('header', $render_data) ?>
<?php $this->part('main-header', $render_data) ?>
<mc-entity #default="{entity}">
<?= $TEMPLATE_CONTENT ?>
</mc-entity>
<?php $this->part('main-footer', $render_data) ?>
<?php $this->part('footer', $render_data); 