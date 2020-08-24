<div ng-if="field.fieldType === 'text'" id="registration-field-{{field.id}}">
    <span class="label"> {{field.title}} {{field.required ? '*' : ''}}</span>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    
    <div><?php $this->part('registration-field-types/fields/text') ?></div>
</div>