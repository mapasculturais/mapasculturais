<?php
$editable = $this->isEditable() && !isset($disable_editable);
?>
<?php if($editable || $entity->registrationFrom): ?>
    <div class="registration-dates clear">
        <?php \MapasCulturais\i::_e("Inscrições abertas de");?>
        <strong <?php if($editable): ?> class="js-editable" <?php endif; ?> data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::_e("Data inicial");?>"><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong>
        <?php \MapasCulturais\i::_e("a");?>
        <strong <?php if($editable): ?> class="js-editable" <?php endif; ?> data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::_e("Data final");?>"><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong>
        <?php \MapasCulturais\i::_e("às");?>
        <strong <?php if($editable): ?> class="js-editable" id="registrationTo_time" <?php endif; ?> data-datetime-value="<?php echo $entity->registrationTo ? $entity->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::_e("Hora final");?>" data-emptytext="<?php \MapasCulturais\i::_e("Hora final");?>"><?php echo $entity->registrationTo ? $entity->registrationTo->format('H:i') : ''; ?></strong>
        .
    </div>
<?php endif; ?>
