<?php
use MapasCulturais\i;

$app = MapasCulturais\App::i();
$definitions = MapasCulturais\Entities\Space::getPropertiesMetadata();
$space_fields = $app->modules['RegistrationFieldTypes']->config['availableSpaceFields'];

$fields_options = [];

foreach ($space_fields as $field) {
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
<div ng-if="field.fieldType === 'space-field'" >
    <?php i::_e('Campo do epaço:') ?>

    <select ng-model="field.config.entityField">
    <?php foreach ($fields_options as $key => $label) : ?>
        <option value="<?= $key ?>"><?= $label ?></option>
    <?php endforeach; ?>
    </select>
    
    <div ng-if="field.config.entityField == '@location'">
        <label><input type="checkbox" ng-model="field.config.setLatLon" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Definir a latitude e longitude baseado no CEP?') ?></label><br>
        <label><input type="checkbox" ng-model="field.config.setPrivacy" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Fornecer opção para mudar a privacidade da localização?') ?></label>
    </div>
</div>