<?php
$app = MapasCulturais\App::i();
$definitions = MapasCulturais\Entities\Space::getPropertiesMetadata();
$space_fields = $app->modules['RegistrationFieldTypes']->config['availableSpaceFields'];

$fields_options = [];

foreach ($space_fields as $field) {
    if (isset($definitions[$field])) {
        $def = $definitions[$field];
        $fields_options[$field] = $def['label'] ?: $field;
    } else {
        $fields_options[$field] = $field;
    }
}
?>
<div ng-if="field.fieldType === 'space-field'">
    <select ng-model="field.config.spaceField">
        <?php foreach ($fields_options as $key => $label) : ?>
            <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
    </select>
</div>