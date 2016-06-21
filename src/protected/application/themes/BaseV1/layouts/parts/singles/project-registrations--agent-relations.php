<?php 
$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable' : '';

?>
<div id="registration-agent-relations" class="registration-fieldset">
    <h4>4. Agentes</h4>
    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Toda inscrição obrigatoriamente deve possuir um Agente Individual responsável, mas é possível que a inscrição seja feita em nome de um agente coletivo, com ou sem CNPJ. Nesses casos, é preciso definir abaixo se essas informações são necessárias e se são obrigatórias.</p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto. </p>

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
                <span class="label"><?php echo $def->label ?></span> <span class="registration-help">(<?php echo $def->description ?>)</span>
                <br>
                <span class="<?php echo $ditable_class ?>" data-edit="<?php echo $metadata_name ?>" data-original-title="<?php echo $def->metadataConfiguration['label'] ?>" data-emptytext="Selecione uma opção"><?php echo $option_label ?></span>
            </p>

        </div>
    <?php endforeach; ?>

    <p>
        <span class="label">Número máximo de inscrições por agente responsável</span><br>
        <span class="registration-help">Zero (0) significa sem limites</span><br>
        <span class="<?php echo $ditable_class ?>" data-edit="registrationLimitPerOwner" data-original-title="Número máximo de inscrições por agente responsável" data-emptytext="Insira o número máximo de inscrições por agente responsável"><?php echo $entity->registrationLimitPerOwner ? $entity->registrationLimitPerOwner : '0'; ?></span>
    </p>
</div>
<!-- #registration-agent-relations -->