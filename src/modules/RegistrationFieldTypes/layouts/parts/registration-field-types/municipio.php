<?php

use MapasCulturais\i; ?>

<div ng-if="field.fieldType == 'municipio'">
    <span class="label">
        {{::field.title}}
        <div ng-if="requiredField(field)" class="field-required"><span class="description"><?php i::_e('obrigatório') ?></span><span class="icon-required">*</span></div>
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
    
</div>