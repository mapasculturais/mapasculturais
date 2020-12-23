<?php use MapasCulturais\i; ?>
<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType == 'links'" id="field_{{::field.id}}" >
    <span class="label icon"> 
        {{::field.title}} 
        <span ng-if="requiredField(field) ">obrigatório</span>       

    </span>
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div><?php $this->part('registration-field-types/fields/links') ?></div>
</div>