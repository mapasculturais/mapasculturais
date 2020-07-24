<a class="<?php echo $classes; ?> js-open-dialog" href="javascript:void(0)"
   data-dialog-block="true" data-dialog="#<?php echo $modal_id; ?>" data-dialog-callback="MapasCulturais.addEntity"
   data-form-action='insert' data-dialog-title="<?php \MapasCulturais\i::esc_attr_e('Modal de Entidade'); ?>">
    <?php echo $text ?>
</a>

<?php $this->part('modal/form', ['entity_name' => $entity_name, 'new_entity' => $new_entity, 'modal_id' => $modal_id, 'use_modal' => true]); ?>