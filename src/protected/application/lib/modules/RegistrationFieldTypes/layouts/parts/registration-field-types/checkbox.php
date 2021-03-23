<?php

use MapasCulturais\i; ?>

<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'checkbox'" id="field_{{::field.id}}">
    <span>
        <?php $this->part('registration-field-types/fields/checkbox') ?>
        <div ng-if="requiredField(field)" class="field-required"><span class="description"><?php i::_e('obrigatório') ?></span><span class="icon-required">*</span></div>
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
</div>