<?php

namespace MapasCulturais;

$app = App::i();
$agent_fields = $app->modules['RegistrationFieldTypes']->getAgentFields();
$definitions = [];
foreach (Entities\Agent::getPropertiesMetadata() as $key => $def) {
    if (in_array($key, $agent_fields)) {
        $def = (object) $def;
        if (empty($def->field_type)) {
            $def->field_type = 'text';
        }
        $definitions[$key] = $def;
    }
}
?>
<div ng-if="field.fieldType === 'agent-owner-field'" id="registration-field-{{field.id}}">
    <span class="label hltip" title="<?= i::_e("Este campo será salvo no seu agente cultural") ?>">
        <i class="icon icon-agent"></i> 
        {{field.title}} {{field.required ? '*' : ''}}
    </span>

    <span ng-if="field.config.agentField == '@location'">
        <?php $this->part('registration-field-types/fields/_location') ?>
    </span>
    <?php
    foreach ($definitions as $key => $def) :
        $type = $key == 'documento' ? 'cpf' : $def->field_type;
    ?>
        <span ng-if="field.config.agentField == '<?= $key ?>'">
            <?php $this->part('registration-field-types/fields/' . $type) ?>
        </span>
    <?php endforeach; ?>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>