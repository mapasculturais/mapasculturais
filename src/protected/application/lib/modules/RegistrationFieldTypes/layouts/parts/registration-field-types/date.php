<div ng-if="field.fieldType === 'date'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <p>
        <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-yearrange="1900:+10" data-viewformat="dd-mm-yyyy" data-name="{{field.fieldName}}" data-type="date" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe"); ?>" data-value="{{entity[field.fieldName]}}"></span>
    </p>
</div>