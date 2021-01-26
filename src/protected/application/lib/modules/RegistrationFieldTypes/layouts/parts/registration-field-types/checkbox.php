<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'checkbox'" id="field_{{::field.id}}">
    <span >
        <?php $this->part('registration-field-types/fields/checkbox') ?>
        <span ng-if="requiredField(field)" class="required_form">(<?php \MapasCulturais\i::_e('Obrigatório'); ?>)</span>
    </span>
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
</div>