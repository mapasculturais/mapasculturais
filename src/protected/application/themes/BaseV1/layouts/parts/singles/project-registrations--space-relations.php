<?php
use MapasCulturais\App;

$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable' : '';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

$metadata_name = 'useSpaceRelation';

$option_label = $entity->$metadata_name ? $entity->$metadata_name : 'dontUse';

$projectMeta = \MapasCulturais\Entities\Project::getPropertiesMetadata();
$message = $projectMeta['useSpaceRelation']['options'];
?>

<?php if ($entity->isRegistrationOpen() || $this->isEditable()): ?>
        <div id="registration-space-relation" class="registration-fieldset">
            <h4><?php \MapasCulturais\i::_e("Espaço Cultural");?></h4>
            <p class="registration-help">
                <?php \MapasCulturais\i::_e("Uma inscrição pode pedir para que o agente relacione um Espaço Cultural a ela. 
                                            Indique aqui se quer habilitar esta opção.");?>
            </p>
           <span class="<?php echo $ditable_class ?>" 
                 data-edit="<?php echo $metadata_name ?>" 
                 data-original-title="Selecione" 
                 data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma opção");?>">
                <?php \MapasCulturais\i::_e($message[$option_label]); ?>
           </span>
        </div>

    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("");?></p>
<?php endif; ?>
