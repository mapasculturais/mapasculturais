<?php
$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable' : '';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

?>
<div id="registration-agent-relations" class="registration-fieldset">
    <h4>4. Agentes</h4>
    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e("Toda inscrição obrigatoriamente deve possuir um Agente Individual responsável, mas é possível que a inscrição seja feita em nome de um agente coletivo, com ou sem CNPJ. Nesses casos, é preciso definir abaixo se essas informações são necessárias e se são obrigatórias.");?></p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e("A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto.");?> </p>

    <?php
    foreach ($app->getRegisteredRegistrationAgentRelations() as $def):
        $metadata_name = $def->metadataName;
        if ($can_edit) {
            $option_label = $entity->$metadata_name ? $entity->$metadata_name : 'dontUse';
        } else {
            $option_label = $def->getOptionLabel($entity->$metadata_name);
        }
        ?>
        <div class="registration-related-agent-configuration">
            <p>
                <span class="label <?php echo ($entity->isPropertyRequired($entity,$metadata_name) && $editEntity? 'required': '');?>"><?php echo $def->label ?></span> <span class="registration-help">(<?php echo $def->description ?>)</span>
                <br>
                <span class="<?php echo $ditable_class ?>" data-edit="<?php echo $metadata_name ?>" data-original-title="<?php echo $def->metadataConfiguration['label'] ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma opção");?>"><?php echo $option_label ?></span>
            </p>

        </div>
    <?php endforeach; ?>

    <p>
        <span class="label <?php echo ($entity->isPropertyRequired($entity,"registrationLimit") && $editEntity? 'required': '');?>"><?php \MapasCulturais\i::_e("Número máximo de vagas no projeto");?></span><br>
        <span class="registration-help"><?php \MapasCulturais\i::_e("Zero (0) significa sem limites");?></span><br>
        <span class="<?php echo $ditable_class ?>" data-edit="registrationLimit" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Número máximo de inscrições no projeto");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira o número máximo de inscrições no projeto");?>"><?php echo $entity->registrationLimit ? $entity->registrationLimit : '0'; ?></span>
    </p>

    <p>
        <span class="label <?php echo ($entity->isPropertyRequired($entity,"registrationLimitPerOwner") && $editEntity? 'required': '');?>"><?php \MapasCulturais\i::_e("Número máximo de inscrições por agente responsável");?></span><br>
        <span class="registration-help"><?php \MapasCulturais\i::_e("Zero (0) significa sem limites");?></span><br>
        <span class="<?php echo $ditable_class ?>" data-edit="registrationLimitPerOwner" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Número máximo de inscrições por agente responsável");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira o número máximo de inscrições por agente responsável");?>"><?php echo $entity->registrationLimitPerOwner ? $entity->registrationLimitPerOwner : '0'; ?></span>
    </p>
</div>
<!-- #registration-agent-relations -->
