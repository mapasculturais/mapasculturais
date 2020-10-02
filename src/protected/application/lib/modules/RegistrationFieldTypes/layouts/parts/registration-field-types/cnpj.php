<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'cnpj'" id="field_{{::field.id}}">
    <div class="label"> 
        {{::field.title}} 
        <span ng-if="requiredField(field) ">obrigatório</span>   
    </div>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div><?php $this->part('registration-field-types/fields/cnpj') ?></div>
</div>