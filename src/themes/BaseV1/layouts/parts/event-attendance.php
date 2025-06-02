<?php if($this->isEditable() || $entity->event_attendance): ?>
<p>
    <span class="label"><?php \MapasCulturais\i::_e('Total de Público'); ?></span>
    <span class="js-editable" data-edit="event_attendance" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Público presente'); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Selecione'); ?>">
        <?php echo $entity->event_attendance; ?>
    </span>
</p>
<?php endif; ?>
