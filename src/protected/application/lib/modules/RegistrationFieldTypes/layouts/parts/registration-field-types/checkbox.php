<div ng-if="field.fieldType === 'checkbox'" id="registration-field-{{field.id}}">
    <span><?php $this->part('registration-field-types/fields/checkbox') ?></span>
    
    {{field.required ? '*' : ''}}
    
    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>

</div>