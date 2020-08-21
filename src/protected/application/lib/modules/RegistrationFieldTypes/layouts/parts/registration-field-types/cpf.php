<div ng-if="field.fieldType === 'cpf'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
    
    <span><?php $this->part('registration-field-types/fields/cpf') ?></span>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    
</div>