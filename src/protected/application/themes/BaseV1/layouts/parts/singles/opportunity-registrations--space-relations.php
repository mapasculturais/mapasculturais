<?php
use MapasCulturais\App;

$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable editable editable-click' : '';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
//var_dump($app->_config);
$metadata_name = 'useSpaceRelation';
dump($entity->$metadata_name);
$option_label = $entity->$metadata_name ? $entity->$metadata_name : 'dontUse';

$projectMeta = \MapasCulturais\Entities\Project::getPropertiesMetadata();
dump($projectMeta['useSpaceRelation']);
$message = $projectMeta['useSpaceRelation']['options'];
// dump($message);
// dump($app);
// die();
?>

<?php if ($entity->isRegistrationOpen() || $this->isEditable()): ?>
        <div id="registration-space-relation" class="registration-fieldset">
            <h4><?php \MapasCulturais\i::_e("Espaço Cultural");?></h4>
            <p class="registration-help">
                <?php \MapasCulturais\i::_e("Uma inscrição pode pedir para que o agente relacione um Espaço Cultural a ela. Indique aqui se quer habilitar esta opção.");?>
            </p>
            <!-- <span class="js-editable editable editable-click" data-edit="useAgentRelationInstituicao" data-original-title="Instituição responsável" data-emptytext="Selecione uma opção">Não utilizar</span> -->
           <span class="<?php echo $ditable_class; ?>" 
                 data-edit="<?php echo $metadata_name; ?>" 
                 data-original-title="Selecione" 
                 data-type = "select"
                 data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma opção");?>">
                <?php \MapasCulturais\i::_e($message[$option_label]); ?>
           </span>
           <script>
            $(document).ready(function() {
                $('.js-editable').editable();
            });
            </script>
        </div>

    <?php else: ?>
        <p><?php \MapasCulturais\i::_e("");?></p>
<?php endif; ?>
