<div ng-if="field.fieldType === 'date'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <span><?php $this->part('registration-field-types/fields/date') ?></span>
    
    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>