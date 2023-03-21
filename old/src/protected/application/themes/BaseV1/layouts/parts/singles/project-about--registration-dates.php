<?php
$editable = $this->isEditable() && !isset($disable_editable);
?>
<?php if($editable || $entity->startsOn): ?>
    <div class="registration-dates clear">
        <?php /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */ ?>
        <?php \MapasCulturais\i::_e("Inscrições abertas de");?>
        <strong <?php if($editable): ?> class="js-editable" <?php endif; ?> data-type="date" data-yearrange="2000:+25" <?php echo $entity->startsOn ? "data-value='".$entity->startsOn->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="startsOn" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data inicial");?>"><?php echo $entity->startsOn ? $entity->startsOn->format('d/m/Y') : \MapasCulturais\i::__("Data inicial"); ?></strong>
        <?php /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */ ?>
        <?php \MapasCulturais\i::_e("a");?>
        <strong <?php if($editable): ?> class="js-editable" <?php endif; ?> data-type="date" data-yearrange="2000:+25" <?php echo $entity->endsOn ? "data-value='".$entity->endsOn->format('Y-m-d') . "'" : ''?> data-viewformat="dd/mm/yyyy" data-edit="endsOn" data-timepicker="#endsOn_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data final");?>"><?php echo $entity->endsOn ? $entity->endsOn->format('d/m/Y') : \MapasCulturais\i::__("Data final"); ?></strong>
        <?php /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */ ?>
        <?php \MapasCulturais\i::_e("às");?>
        <strong <?php if($editable): ?> class="js-editable" id="endsOn_time" <?php endif; ?> data-datetime-value="<?php echo $entity->endsOn ? $entity->endsOn->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e("Hora final");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Hora final");?>"><?php echo $entity->endsOn ? $entity->endsOn->format('H:i') : ''; ?></strong>
    </div>
<?php endif; ?>
