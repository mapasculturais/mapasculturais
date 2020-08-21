<div ng-if="field.fieldType === 'select'" id="registration-field-{{field.id}}">
    <span class="label"> {{field.title}} {{field.required ? '*' : ''}}</span>
    
    <span><?php $this->part('registration-field-types/fields/select') ?></span>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>