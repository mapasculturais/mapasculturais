<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'checkboxes'" id="field_{{::field.id}}">
    <span class="label"> 
        {{field.title}} 
        <span ng-if="requiredField(field)" class="required_form">(<?php \MapasCulturais\i::_e('Obrigatório'); ?>)</span>
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <p>
        <?php $this->part('registration-field-types/fields/checkboxes') ?>
    </p>
</div>