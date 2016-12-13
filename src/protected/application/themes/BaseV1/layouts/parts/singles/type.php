<?php 
$_entity = $this->controller->id; 
$class = isset($disable_editable) ? '' : 'js-editable-type';
?>
<?php $this->applyTemplateHook('type','before'); ?>
<div class="entity-type <?php echo $_entity ?>-type">
    <div class="icon icon-<?php echo $_entity ?>"></div>
    <a href="#" class='<?php echo $class ?> required' data-original-title="<?php \MapasCulturais\i::esc_attr_e("Tipo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione um tipo");?>" data-entity='<?php echo $_entity ?>' data-value='<?php echo $entity->type ?>'>
        <?php echo $entity->type ? $entity->type->name : ''; ?>
    </a>
</div>
<!--.entity-type-->
<?php $this->applyTemplateHook('type','after'); ?>