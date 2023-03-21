<?php

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions", 'before'); ?>
<div class="actions">
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions",'begin'); ?>
    <?php $app->applyHook('mapasculturais.add_entity_modal.form-actions:begin'); ?>
    <button type="button" class="btn btn-default <?php echo $classes['cancel_class']; ?>" data-form-id='<?php echo $modal_id; ?>'>
        <?php \MapasCulturais\i::_e("Cancelar"); ?>
    </button>
    <input type="submit" class="btn btn-primary" value="Adicionar <?php echo $name; ?>">
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions",'end'); ?>
</div>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions",'after'); ?>