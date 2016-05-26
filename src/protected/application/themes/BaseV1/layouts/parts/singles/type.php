<?php $_entity = $this->controller->id; ?>
<?php $this->applyTemplateHook('type','before'); ?>
<div class="entity-type <?php echo $_entity ?>-type">
    <div class="icon icon-<?php echo $_entity ?>"></div>
    <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Seleccione un tipo" data-entity='<?php echo $_entity ?>' data-value='<?php echo $entity->type ?>'>
        <?php echo $entity->type ? $entity->type->name : ''; ?>
    </a>
</div>
<!--.entity-type-->
<?php $this->applyTemplateHook('type','after'); ?>