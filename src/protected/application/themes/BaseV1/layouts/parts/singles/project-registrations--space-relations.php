<?php
$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable' : '';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

$metadata_name = 'useSpaceRelation';

$option_label = $entity->$metadata_name ? $entity->$metadata_name : 'dontUse';

?>

<?php if ($entity->isRegistrationOpen() || $this->isEditable()): ?>
        <div id="registration-space-relation" class="registration-fieldset">
            <h4>4. <?php \MapasCulturais\i::_e("Espaços Culturais");?></h4>
            <p class="registration-help"><?php \MapasCulturais\i::_e("Selecione o espaço relacionado com o evento");?></p>
           <span class="<?php echo $ditable_class ?>" data-edit="<?php echo $metadata_name ?>" data-original-title="Selecione" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma opção");?>"><?php echo $option_label ?></span>
        </div>

    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("");?></p>
<?php endif; ?>