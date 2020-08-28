<div ng-if="::field.fieldType === 'url'" id="field_{{::field.id}}">
    <span class="label"> {{::field.title}} {{::field.required ? '*' : ''}}</span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
    
    <div><?php $this->part('registration-field-types/fields/url') ?></div>
</div>