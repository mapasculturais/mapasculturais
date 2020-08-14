<div ng-if="field.fieldType === 'select'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <p>
        <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="select" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe"); ?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
    </p>
</div>