<div ng-if="field.fieldType === 'email'" id="registration-field-{{field.id}}">
    <div class="label icon"> {{field.title}} {{field.required ? '*' : ''}}</div>
    
    <span><?php $this->part('registration-field-types/fields/email') ?></span>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>