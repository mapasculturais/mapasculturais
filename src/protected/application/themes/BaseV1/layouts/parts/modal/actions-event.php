<?php

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions-events", 'before'); ?>
<div class="actions">
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'begin'); ?>
    <?php $app->applyHook('mapasculturais.add_entity_modal.form-actions:begin'); ?>
    
    <button type="button" class="btn btn-default <?php echo $classes['cancel_class']; ?>" data-form-id='<?php echo $modal_id; ?>'>
        <?php i::_e("Cancelar");?>
    </button>
    
    <button class="btn save-after-complete"><?php i::_e("Salvar e completar depois");?></button>

    <input type="submit" class="btn btn-primary save-complete" value="<?php i::_e("Completar criação agora");?> <?php echo $name; ?>">
    
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'end'); ?>
</div>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'after'); ?>