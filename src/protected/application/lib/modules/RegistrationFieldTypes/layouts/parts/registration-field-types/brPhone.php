<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'brPhone'" id="field_{{::field.id}}">
    <div class="label"> 
        {{field.title}}
        <span ng-if="requiredField(field)" class="required_form">(<?php \MapasCulturais\i::_e('Obrigatório'); ?>)</span>
    </div>
    
    <div ng-if="::field.description" class="attachment-description">{{::fieldfield.description}}</div>
    <div><?php $this->part('registration-field-types/fields/brPhone') ?></div>
    
</div>