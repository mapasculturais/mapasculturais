<div ng-if="field.fieldType === 'space-field'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <p style="position: relative;">
        <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="textarea" data-tpl="<textarea onkeyup='charCounter(this);' maxlength='{{ !field.maxSize ?'': field.maxSize }}'></textarea><span id='charCounter'></span>" data-original-title="{{field.title}}" data-maxlength="{{ field.maxSize }}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe"); ?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
    </p>
</div>