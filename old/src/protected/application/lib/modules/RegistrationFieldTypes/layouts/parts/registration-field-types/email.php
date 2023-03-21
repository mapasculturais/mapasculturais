<?php

use MapasCulturais\i; ?>

<div ng-class="field.error ? 'invalidField': '' " ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'email'" id="field_{{::field.id}}">
    <span class="label icon">
        {{::field.title}}
        <div ng-if="requiredField(field)" class="field-required"><span class="description"><?php i::_e('obrigatório') ?></span><span class="icon-required">*</span></div>
    </span>
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
    <div><?php $this->part('registration-field-types/fields/email') ?></div>
</div>