<?php

namespace MapasCulturais;

$app = App::i();
$agent_fields = $app->modules['RegistrationFieldTypes']->getSpaceFields();
$definitions = [];
foreach (Entities\Space::getPropertiesMetadata() as $key => $def) {
    if (in_array($key, $agent_fields)) {
        $def = (object) $def;
        if (empty($def->field_type)) {
            $def->field_type = 'text';
        }
        $definitions[$key] = $def;
    }
}
?>
<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'space-field'" id="field_{{::field.id}}">
    <span class="label">
        <i class="icon icon-space"></i> 
        {{::field.title}} 
        <span ng-if="::field.required ">obrigatório</span>   
    </span>
    
    <em class="relation-field-info">(<?php i::_e('Este campo será salvo no espaço relacionado') ?>)</em>
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div ng-if="::field.config.entityField == '@location'">
        <?php $this->part('registration-field-types/fields/_location') ?>
    </div>
    <div ng-if="::field.config.entityField == '@terms:area'">
        <?php $this->part('registration-field-types/fields/checkboxes') ?>
    </div>
    <div ng-if="::field.config.entityField == '@links'">
        <?php $this->part('registration-field-types/fields/links') ?>
    </div>
    <?php
    foreach ($definitions as $key => $def) :
    ?>
        <div ng-if="::field.config.entityField == '<?= $key ?>'">
            <?php $this->part('registration-field-types/fields/' . $def->field_type) ?>
        </div>
    <?php endforeach; ?>

</div>