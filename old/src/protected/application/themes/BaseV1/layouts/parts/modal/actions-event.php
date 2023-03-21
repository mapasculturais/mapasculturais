<?php

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions-events", 'before'); ?>
<div class="actions-events">
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'begin'); ?>
    <?php $app->applyHook('mapasculturais.add_entity_modal.form-actions:begin'); ?>    
    <div>
        <button class="btn-primary btn-event" onclick="saveEvent('form-for-<?=$modal_id?>')"><?php i::_e("Criar evento");?></button>
        <button type="button" class="btn-default btn-event close-modal" data-form-id='<?php echo $modal_id; ?>'>
            <?php i::_e("Cancelar");?>
        </button>
    </div>
    <?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'end'); ?>
</div>
<?php $this->applyTemplateHook("{$entity_name}-modal-actions-events",'after'); ?>