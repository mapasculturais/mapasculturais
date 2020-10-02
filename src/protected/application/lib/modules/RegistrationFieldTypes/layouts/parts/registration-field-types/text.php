<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'text'" id="field_{{::field.id}}">
    <span class="label"> 
        {{::field.title}} 
        <span ng-if="requiredField(field) ">obrigatório</span>   
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
    
    <div><?php $this->part('registration-field-types/fields/text') ?></div>
</div>