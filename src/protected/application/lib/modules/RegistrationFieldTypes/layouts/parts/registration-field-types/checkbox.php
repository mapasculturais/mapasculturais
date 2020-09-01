<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'checkbox'" id="field_{{::field.id}}">
    <span><?php $this->part('registration-field-types/fields/checkbox') ?></span>
    
    <span ng-if="::field.required ">obrigatório</span>   
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

</div>