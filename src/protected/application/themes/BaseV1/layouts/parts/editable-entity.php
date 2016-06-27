<?php
if (!$this->isEditable() && !$entity->canUser('modify')){
    ?><div id="editable-entity" class="clearfix sombra js-not-editable" style='display:none; min-height:0; height:42px;'></div><?php
    return;
}

$can_edit_roles = $this->controller->id == 'agent' && $entity->user->id != $app->user->id && $entity->id == $entity->user->profile->id && $entity->user->canUser('addRole');
if($this->isEditable()){
    $classes = 'editable-entity-edit';
    if($can_edit_roles)
        $classes .= ' can-edit-roles';
}else{
    $classes = 'editable-entity-single';
}

$class = $entity->getClassName();
$params = [
    'entity' => $entity,
    'status_draft' => $class::STATUS_DRAFT,
    'status_enabled' => $class::STATUS_ENABLED,
    'status_trash' => $class::STATUS_TRASH
];
?>

<div id="editable-entity" class="clearfix sombra <?php echo $classes ?>" data-action="<?php echo $action; ?>" data-entity="<?php echo $this->controller->id ?>" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <div class="controles">
    <?php if ($this->isEditable()): ?>
        <?php if ($can_edit_roles): ?>
            <?php $this->part('singles/control--roles', $params) ?>
        <?php endif; ?>

        <?php $this->part('singles/control--edit-buttons', $params) ?>
    <?php else: ?>
        <?php $this->part('singles/control--view-buttons', $params) ?>
    <?php endif; ?>
    </div>
</div>
