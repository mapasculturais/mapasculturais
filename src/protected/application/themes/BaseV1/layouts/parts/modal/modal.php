<?php
$form_part = [
    'event' => 'modal/form-event',
    'opportunity' => 'modal/form-opportunity'
];

$part_name = $form_part[$entity_name] ?? 'modal/form';

?>

<a class="<?php echo $classes; ?> js-open-dialog" href="javascript:void(0)"
   data-dialog-block="true" data-dialog="#<?php echo $modal_id; ?>" data-dialog-callback="MapasCulturais.addEntity"
   data-form-action='insert' data-dialog-title="<?php \MapasCulturais\i::esc_attr_e('Modal de Entidade'); ?>">
    <?php echo $text ?>
</a>

<?php $this->part($part_name, ['entity_name' => $entity_name, 'entity_classname' => $entity_classname, 'modal_id' => $modal_id, 'use_modal' => true]); ?>

<div id="dialog-event-occurrence" class="js-dialog" style="z-index:1901">
    <div class="js-dialog-content js-dialog-event-occurrence"></div>
</div>   