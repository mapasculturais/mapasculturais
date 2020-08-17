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
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <?php foreach ($definitions as $key => $def): ?> 
        <p ng-if="field.config.agentField == '<?= $key ?>'" style="position: relative;">
            <?php $this->part('registration-field-types/fields/' . $def->field_type); ?>
        </p>
    <?php endforeach; ?>
</div>