<?php
$this->import('entity');
$this->jsObject['requestedEntity'] = $relation;
if ($relation->usesTypes()) {
    $this->bodyClasses[] = "{$relation->entityType}-{$relation->type->id}";
} else {
    $this->bodyClasses[] = "{$relation->entityType}";
}
?>
<?php $this->part('header', $render_data) ?>
<?php $this->part('main-header', $render_data) ?>
<entity #default="{entity}">
<?= $TEMPLATE_CONTENT ?>
</entity>
<?php $this->part('main-footer', $render_data) ?>
<?php $this->part('footer', $render_data); 