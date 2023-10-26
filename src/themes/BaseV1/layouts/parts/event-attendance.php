<?php if($this->isEditable() || $entity->event_attendance): ?>
<p>
    <span class="label">Total de Público</span>
    <span class="js-editable" data-edit="event_attendance" data-original-title="Público presente" data-emptytext="Selecione">
        <?php echo $entity->event_attendance; ?>
    </span>
</p>
<?php endif; ?>