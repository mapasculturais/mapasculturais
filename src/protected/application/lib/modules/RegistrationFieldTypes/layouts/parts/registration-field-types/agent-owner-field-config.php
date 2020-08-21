<?php
use MapasCulturais\i;

$app = MapasCulturais\App::i();
$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$agent_fields = $app->modules['RegistrationFieldTypes']->config['availableAgentFields'];

$fields_options = [];

foreach ($agent_fields as $field) {
    if (isset($definitions[$field])) {
        $def = $definitions[$field];
        $fields_options[$field] = $def['label'] ?: $field;
    } else if($field == '@location'){
        $fields_options[$field] = i::__(' Campos de endereço');
    } else {
        $fields_options[$field] = $field;
    }
}
?>
<div ng-if="field.fieldType === 'agent-owner-field'" >
    <?php i::_e('Campo do agente responsável:') ?>
    <select ng-model="field.config.agentField">
        <?php foreach ($fields_options as $key => $label) : ?>
            <option value="<?= $key ?>"><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>