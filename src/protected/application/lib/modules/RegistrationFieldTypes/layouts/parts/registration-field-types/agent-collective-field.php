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
<div ng-if="field.fieldType === 'agent-collective-field'" id="registration-field-{{field.id}}">
    <span class="label">
        <i class="icon icon-agent"></i> 
        {{field.title}} {{field.required ? '*' : ''}}
    </span>
    
    <em class="relation-field-info">(<?php i::_e('Este campo será salvo no agente coletivo') ?>)</em>
    
    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>

    <div ng-if="field.config.entityField == '@location'">
        <?php $this->part('registration-field-types/fields/_location') ?>
    </div>
    <?php
    foreach ($definitions as $key => $def) :
        $type = $key == 'documento' ? 'cnpj' : $def->field_type;
    ?>
        <div ng-if="field.config.entityField == '<?= $key ?>'">
            <?php $this->part('registration-field-types/fields/' . $type) ?>
        </div>
    <?php endforeach; ?>

</div>