<div ng-if="::field.fieldType === 'brPhone'" id="field_{{::field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
    
    <div ng-if="::field.description" class="attachment-description">{{::fieldfield.description}}</div>

    <div><?php $this->part('registration-field-types/fields/brPhone') ?></div>
    
</div>